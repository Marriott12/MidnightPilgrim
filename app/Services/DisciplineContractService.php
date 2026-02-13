<?php

namespace App\Services;

use App\Models\UserProfile;
use App\Models\DisciplineContract;
use App\Models\Poem;
use App\Models\PoemRevision;
use App\Models\ConstraintCycle;
use App\Models\ComplianceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * DisciplineContractService - POETRY ENFORCEMENT ENGINE (COMPREHENSIVE)
 * 
 * Enforces the binding discipline contract with full compliance tracking,
 * archive structure enforcement, and constraint validation.
 */
class DisciplineContractService
{
    public function __construct(
        private ArchiveEnforcementService $archiveService,
        private ConstraintValidationService $constraintValidator
    ) {}

    /**
     * Initialize discipline contract for user
     */
    public function initializeContract(UserProfile $profile, ?string $timezone = null, ?Carbon $startDate = null): DisciplineContract
    {
        $startDate = $startDate ?? Carbon::parse('2026-02-20');
        $endDate = $startDate->copy()->addWeeks(10);
        $totalWeeks = 10;
        $userTimezone = $timezone ?? $profile->timezone ?? 'UTC';

        $contract = DisciplineContract::create([
            'user_profile_id' => $profile->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'total_weeks' => $totalWeeks,
            'user_timezone' => $userTimezone,
        ]);

        // Generate constraint cycles for all weeks
        for ($week = 1; $week <= $totalWeeks; $week++) {
            $constraint = ConstraintCycle::getConstraintForWeek($week);
            
            ConstraintCycle::create([
                'user_profile_id' => $profile->id,
                'discipline_contract_id' => $contract->id,
                'week_number' => $week,
                'constraint_type' => $constraint['type'],
                'constraint_description' => $constraint['description'],
            ]);
        }

        // Generate compliance logs for all weeks
        $this->generateComplianceLogs($contract);

        // Initialize archive structure
        $this->archiveService->initializeContractArchive($contract);

        return $contract;
    }

    /**
     * Generate compliance logs for all weeks
     */
    private function generateComplianceLogs(DisciplineContract $contract): void
    {
        for ($week = 1; $week <= $contract->total_weeks; $week++) {
            $weekStart = $contract->start_date->copy()->addWeeks($week - 1);
            $deadline = $weekStart->copy()->addDays(6)->setTime(20, 0);

            ComplianceLog::create([
                'discipline_contract_id' => $contract->id,
                'user_profile_id' => $contract->user_profile_id,
                'week_number' => $week,
                'status' => 'pending',
                'deadline_at' => $deadline,
            ]);
        }
    }

    /**
     * Get current contract status
     */
    public function getContractStatus(UserProfile $profile): array
    {
        $contract = $profile->activeDisciplineContract();

        if (!$contract) {
            return [
                'active' => false,
                'message' => 'No active discipline contract.',
            ];
        }

        $currentWeek = $contract->getCurrentWeekNumber();
        $constraint = ConstraintCycle::where('discipline_contract_id', $contract->id)
            ->where('week_number', $currentWeek)
            ->first();

        $complianceLog = $contract->complianceLogs()
            ->where('week_number', $currentWeek)
            ->first();

        $deadline = $complianceLog ? $complianceLog->deadline_at : null;
        $hoursUntilDeadline = $deadline ? now()->diffInHours($deadline, false) : null;

        return [
            'active' => true,
            'week' => $currentWeek,
            'total_weeks' => $contract->total_weeks,
            'constraint' => $constraint ? [
                'type' => $constraint->constraint_type,
                'description' => $constraint->constraint_description,
            ] : null,
            'deadline' => $deadline ? $deadline->toIso8601String() : null,
            'hours_until_deadline' => $hoursUntilDeadline,
            'minimum_lines' => $contract->getMinimumLines(),
            'poems_submitted' => $contract->poems_submitted,
            'poems_missed' => $contract->poems_missed,
            'monthly_releases' => $contract->monthly_releases,
            'monthly_release_due' => $contract->isMonthlyReleaseDue(),
            'in_recovery_window' => $complianceLog ? $complianceLog->isInRecoveryWindow() : false,
        ];
    }

    /**
     * Submit a poem for the current week (with comprehensive validation)
     */
    public function submitPoem(
        UserProfile $profile,
        string $content,
        array $selfAssessment,
        ?string $revisionNotes = null,
        ?int $versionNumber = 1
    ): array {
        $contract = $profile->activeDisciplineContract();

        if (!$contract) {
            return [
                'success' => false,
                'message' => 'No active discipline contract found.',
            ];
        }

        $currentWeek = $contract->getCurrentWeekNumber();

        // CHECK 1: Previous week reflection
        if (!$this->archiveService->hasPreviousWeekReflection($contract, $currentWeek)) {
            return [
                'success' => false,
                'message' => "Week " . ($currentWeek - 1) . " reflection missing. Complete it before proceeding.",
                'blocked_by' => 'missing_reflection',
            ];
        }

        // CHECK 2: Unacknowledged patterns
        $unacknowledged = $profile->patternReports()
            ->where('acknowledged', false)
            ->count();

        if ($unacknowledged > 0) {
            return [
                'success' => false,
                'message' => 'You have unacknowledged pattern reports. Address them before submitting.',
                'blocked_by' => 'unacknowledged_patterns',
                'pattern_count' => $unacknowledged,
            ];
        }

        $lineCount = $this->countLines($content);
        $minimumLines = $contract->getMinimumLines();

        // CHECK 3: Line count
        if ($lineCount < $minimumLines) {
            return [
                'success' => false,
                'message' => "Poem must be at least {$minimumLines} lines. Current: {$lineCount} lines.",
            ];
        }

        // CHECK 4: Already submitted this week
        $existingSubmission = Poem::where('user_profile_id', $profile->id)
            ->where('week_number', $currentWeek)
            ->where('status', '!=', 'draft')
            ->first();

        if ($existingSubmission) {
            return [
                'success' => false,
                'message' => 'You have already submitted a poem for this week.',
            ];
        }

        // Get constraint for this week
        $constraint = ConstraintCycle::where('discipline_contract_id', $contract->id)
            ->where('week_number', $currentWeek)
            ->first();

        // CHECK 5: Constraint validation
        $violations = [];
        if ($constraint) {
            $violations = $this->constraintValidator->validate($content, $constraint->constraint_type);
            
            if ($this->constraintValidator->hasCriticalViolations($violations)) {
                return [
                    'success' => false,
                    'message' => 'Poem violates constraint. Submission rejected.',
                    'violations' => $violations,
                    'violation_summary' => $this->constraintValidator->formatViolations($violations),
                    'rejected' => true,
                ];
            }
        }

        // Create poem
        $poem = Poem::create([
            'user_profile_id' => $profile->id,
            'content' => $content,
            'line_count' => $lineCount,
            'constraint_type' => $constraint->constraint_type ?? null,
            'week_number' => $currentWeek,
            'status' => 'submitted',
            'submitted_at' => now(),
            'self_assessment' => $selfAssessment,
            'constraint_violations' => $violations,
            'revision_notes' => $revisionNotes,
        ]);

        // Save to archive
        try {
            if ($versionNumber == 1) {
                $archivePath = $this->archiveService->saveDraft($poem, $content, $versionNumber);
            } else {
                $archivePath = $this->archiveService->saveRevision($poem, $content, $versionNumber, $revisionNotes ?? '');
            }
            $poem->archive_path = $archivePath;
            $poem->save();

            // Save final version
            $finalPath = $this->archiveService->saveFinal($poem, $content);

            // Create revision record
            PoemRevision::create([
                'poem_id' => $poem->id,
                'version_number' => $versionNumber,
                'content' => $content,
                'changes_made' => $revisionNotes,
                'revision_type' => $versionNumber == 1 ? 'draft' : 'revision',
            ]);

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Archive error: ' . $e->getMessage(),
            ];
        }

        // Update compliance log
        $complianceLog = $contract->complianceLogs()
            ->where('week_number', $currentWeek)
            ->first();

        if ($complianceLog) {
            $complianceLog->on_time = $poem->isOnTime();
            $complianceLog->revision_done = $versionNumber > 1;
            $complianceLog->constraint_followed = empty($violations) || !$this->constraintValidator->hasCriticalViolations($violations);
            $complianceLog->submitted_at = $poem->submitted_at;
            $complianceLog->markCompleted();
        }

        // Record submission
        $contract->recordSubmission();

        // Mark constraint as completed
        if ($constraint) {
            $constraint->complete();
        }

        // Create reflection file
        $this->archiveService->createReflection($contract, $currentWeek);

        // Generate critique
        $critique = $this->generateCritique($poem, $selfAssessment, $violations);
        $poem->storeCritique($critique);

        return [
            'success' => true,
            'message' => 'Poem submitted successfully.',
            'poem_id' => $poem->id,
            'critique' => $critique,
            'on_time' => $poem->isOnTime(),
            'violations' => $violations,
            'archive_path' => $archivePath,
        ];
    }

    /**
     * Generate technical critique for submitted poem
     */
    private function generateCritique(Poem $poem, array $selfAssessment, array $violations): array
    {
        return [
            'line_strength' => $this->analyzeLineStrength($poem->content),
            'rhythm' => $this->analyzeRhythm($poem->content),
            'image_density' => $this->analyzeImageDensity($poem->content),
            'conceptual_coherence' => $this->analyzeConceptualCoherence($poem->content),
            'emotional_honesty' => $this->analyzeEmotionalHonesty($poem->content, $selfAssessment),
            'constraint_adherence' => !empty($violations) ? 
                count($violations) . ' violations detected. See details.' : 
                'Constraint followed.',
            'self_assessment_quality' => $this->assessSelfAssessmentQuality($selfAssessment),
            'weaknesses_identified' => $this->identifyWeaknesses($poem->content),
        ];
    }

    /**
     * Check if deadline was missed and apply consequences (AUTOMATED)
     */
    public function checkDeadlines(DisciplineContract $contract): array
    {
        $results = [];
        $currentWeek = $contract->getCurrentWeekNumber();

        // Check all weeks up to current
        for ($week = 1; $week <= min($currentWeek, $contract->total_weeks); $week++) {
            $complianceLog = $contract->complianceLogs()
                ->where('week_number', $week)
                ->first();

            if (!$complianceLog || $complianceLog->status !== 'pending') {
                continue;
            }

            $deadline = $complianceLog->deadline_at;
            $recoveryWindow = $deadline->copy()->addHours(24);

            // Check if we're past recovery window
            if (now()->gt($recoveryWindow)) {
                // Check if poem was submitted
                $submitted = Poem::where('user_profile_id', $contract->user_profile_id)
                    ->where('week_number', $week)
                    ->where('status', '!=', 'draft')
                    ->exists();

                if (!$submitted) {
                    $complianceLog->markMissed();
                    $contract->recordMiss($week);

                    $results[] = [
                        'week' => $week,
                        'missed' => true,
                        'message' => "Week {$week} deadline missed. Recovery window closed.",
                        'minimum_lines_next_week' => $contract->getMinimumLines(),
                    ];
                }
            } elseif (now()->gt($deadline)) {
                // In recovery window
                $complianceLog->status = 'in_recovery';
                $complianceLog->save();

                $results[] = [
                    'week' => $week,
                    'in_recovery' => true,
                    'message' => "Week {$week} in 24-hour recovery window.",
                ];
            }
        }

        return $results;
    }

    /**
     * Publish poem for monthly release
     */
    public function publishPoem(
        Poem $poem, 
        string $platform, 
        ?string $publicUrl = null,
        ?string $recordingPath = null
    ): array {
        $profile = $poem->userProfile;
        $contract = $profile->activeDisciplineContract();

        if (!$contract) {
            return [
                'success' => false,
                'message' => 'No active discipline contract.',
            ];
        }

        // Verify poem was written within contract window
        if ($poem->created_at->lt($contract->start_date)) {
            return [
                'success' => false,
                'message' => 'Poem was created before contract start date.',
            ];
        }

        // Check platform declaration
        if (!$profile->declared_platform) {
            $profile->declared_platform = $platform;
            $profile->save();
        } elseif ($profile->declared_platform !== $platform) {
            return [
                'success' => false,
                'message' => "Platform must be {$profile->declared_platform}. Cannot change after first release.",
            ];
        }

        // Require recording and URL
        if (empty($recordingPath)) {
            return [
                'success' => false,
                'message' => 'Recording file required for monthly release.',
                'missing' => 'recording',
            ];
        }

        if (empty($publicUrl)) {
            return [
                'success' => false,
                'message' => 'Public release URL required.',
                'missing' => 'url',
            ];
        }

        // Verify public URL
        $urlVerification = $this->verifyPublicUrl($publicUrl, $profile->declared_platform);
        
        if (!$urlVerification['valid']) {
            return [
                'success' => false,
                'message' => 'Public URL verification failed.',
                'reason' => $urlVerification['reason'],
                'url' => $publicUrl,
            ];
        }

        $poem->publish($platform);
        $poem->is_monthly_release = true;
        $poem->recording_file_path = $recordingPath;
        $poem->public_release_url = $publicUrl;
        $poem->save();

        $contract->recordMonthlyRelease();

        return [
            'success' => true,
            'message' => "Poem published on {$platform}.",
            'platform' => $platform,
            'url' => $publicUrl,
            'url_verified' => true,
            'verified_at' => $urlVerification['verified_at'] ?? null,
        ];
    }

    /**
     * Get full compliance log
     */
    public function getComplianceLog(DisciplineContract $contract): array
    {
        $logs = $contract->complianceLogs()
            ->orderBy('week_number')
            ->get();

        return $logs->map(function($log) {
            return [
                'week_number' => $log->week_number,
                'on_time' => $log->on_time ? 'Y' : 'N',
                'revision_done' => $log->revision_done ? 'Y' : 'N',
                'reflection_done' => $log->reflection_done ? 'Y' : 'N',
                'constraint_followed' => $log->constraint_followed ? 'Y' : 'N',
                'penalty_triggered' => $log->penalty_triggered ? 'Y' : 'N',
                'status' => $log->status,
                'status_color' => $log->getStatusColor(),
                'deadline_at' => $log->deadline_at?->toIso8601String(),
                'submitted_at' => $log->submitted_at?->toIso8601String(),
                'notes' => $log->notes,
            ];
        })->toArray();
    }

    /**
     * Count lines in poem
     */
    private function countLines(string $content): int
    {
        return count(array_filter(explode("\n", trim($content)), fn($line) => trim($line) !== ''));
    }

    /**
     * Assess self-assessment quality
     */
    private function assessSelfAssessmentQuality(array $assessment): string
    {
        $issues = [];

        foreach ($assessment as $key => $answer) {
            if (strlen($answer) < 20) {
                $issues[] = "{$key}: Too brief. Expand.";
            }
            if (preg_match('/\b(idk|dunno|maybe|not sure)\b/i', $answer)) {
                $issues[] = "{$key}: Vague language detected.";
            }
        }

        if (empty($issues)) {
            return "Self-assessment meets minimum standards.";
        }

        return "Self-assessment issues:\n" . implode("\n", $issues);
    }

    /**
     * Analyze line strength (improved from placeholder)
     */
    private function analyzeLineStrength(string $content): string
    {
        $lines = array_filter(explode("\n", $content), fn($l) => !empty(trim($l)));
        $weakLines = 0;

        foreach ($lines as $line) {
            $wordCount = str_word_count($line);
            // Lines that are too short or too long often lack strength
            if ($wordCount < 3 || $wordCount > 20) {
                $weakLines++;
            }
            // Lines ending with weak words
            if (preg_match('/\b(the|a|an|is|was|has|have)\s*$/i', $line)) {
                $weakLines++;
            }
        }

        $weakPercent = round(($weakLines / count($lines)) * 100);

        if ($weakPercent > 40) {
            return "Line strength: WEAK. {$weakPercent}% of lines lack impact. Cut filler.";
        } elseif ($weakPercent > 20) {
            return "Line strength: MODERATE. {$weakPercent}% of lines need strengthening.";
        }

        return "Line strength: ACCEPTABLE. Most lines carry weight.";
    }

    /**
     * Analyze rhythm
     */
    private function analyzeRhythm(string $content): string
    {
        $lines = array_filter(explode("\n", $content), fn($l) => !empty(trim($l)));
        $syllableCounts = array_map(fn($l) => $this->estimateSyllables($l), $lines);
        
        $variance = $this->calculateVariance($syllableCounts);

        if ($variance > 50) {
            return "Rhythm: UNSTABLE. High variance in line length. Inconsistent cadence.";
        } elseif ($variance > 25) {
            return "Rhythm: MODERATE. Some rhythm breaks detected.";
        }

        return "Rhythm: CONSISTENT. Cadence maintained throughout.";
    }

    /**
     * Analyze image density
     */
    private function analyzeImageDensity(string$content): string
    {
        $sensoryWords = ['see', 'saw', 'look', 'watch', 'hear', 'sound', 'taste', 'smell', 'touch', 'feel', 'cold', 'warm', 'hot', 'bright', 'dark', 'red', 'blue', 'green'];
        $wordCount = str_word_count($content);
        $imageCount = 0;

        foreach ($sensoryWords as $word) {
            $imageCount += substr_count(strtolower($content), $word);
        }

        $density = $wordCount > 0 ? ($imageCount / $wordCount) * 100 : 0;

        if ($density < 5) {
            return "Image density: LOW. More concrete imagery needed.";
        } elseif ($density < 10) {
            return "Image density: MODERATE. Acceptable but could strengthen.";
        }

        return "Image density: HIGH. Strong sensory detail.";
    }

    /**
     * Analyze conceptual coherence
     */
    private function analyzeConceptualCoherence(string $content): string
    {
        // Simplified - full implementation would use semantic analysis
        return "Conceptual coherence: Theme should thread throughout. Verify consistency.";
    }

    /**
   * Analyze emotional honesty
     */
    private function analyzeEmotionalHonesty(string $content, array $selfAssessment): string
    {
        $emotionWords = ['feel', 'felt', 'emotion', 'sad', 'happy', 'angry', 'afraid', 'love', 'hate'];
        $tellCount = 0;

        foreach ($emotionWords as $word) {
            $tellCount += substr_count(strtolower($content), $word);
        }

        if ($tellCount > 3) {
            return "Emotional honesty: TELLING not showing. Remove emotion words. Use imagery.";
        }

        return "Emotional honesty: Showing rather than telling. Good.";
    }

    /**
     * Identify weaknesses
     */
    private function identifyWeaknesses(string $content): array
    {
        return [
            'Identify the weakest line and explain why.',
            'Where did you hide behind abstraction?',
            'What risk did you avoid?',
        ];
    }

    /**
     * Estimate syllables (rough)
     */
    private function estimateSyllables(string $text): int
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z ]/', '', $text);
        $words = explode(' ', $text);
        
        $syllables = 0;
        foreach ($words as $word) {
            $syllables += max(1, preg_match_all('/[aeiouy]+/', $word));
        }
        
        return $syllables;
    }

    /**
     * Calculate variance
     */
    private function calculateVariance(array $numbers): float
    {
        if (empty($numbers)) return 0;
        
        $mean = array_sum($numbers) / count($numbers);
        $variance = 0;
        
        foreach ($numbers as $num) {
            $variance += pow($num - $mean, 2);
        }
        
        return $variance / count($numbers);
    }

    /**
     * Check for missed monthly releases (run at end of each month)
     */
    public function checkMonthlyReleaseDeadlines(): void
    {
        $contracts = DisciplineContract::where('status', 'active')->get();
        
        foreach ($contracts as $contract) {
            $currentMonth = now($contract->user_timezone)->month;
            $currentYear = now($contract->user_timezone)->year;
            $isLastDayOfMonth = now($contract->user_timezone)->isLastOfMonth();
            $isPastDeadline = now($contract->user_timezone)->hour >= 18;

            if (!$isLastDayOfMonth || !$isPastDeadline) {
                continue;
            }

            // Check if monthly release was completed this month
            $releasedThisMonth = $contract->poems()
                ->where('is_monthly_release', true)
                ->whereYear('published_at', $currentYear)
                ->whereMonth('published_at', $currentMonth)
                ->exists();

            if (!$releasedThisMonth) {
                // Record missed monthly release
                $contract->recordMissedMonthlyRelease();
                
                \Log::info("Monthly release missed for contract {$contract->id} in month {$currentMonth}/{$currentYear}");
            }
        }
    }

    /**
     * Finalize completed contracts (run daily)
     */
    public function finalizeCompletedContracts(): void
    {
        $contracts = DisciplineContract::where('status', 'active')
            ->where('end_date', '<', now())
            ->get();

        foreach ($contracts as $contract) {
            $this->finalizeContract($contract);
        }
    }

    /**
     * Finalize a specific contract
     */
    private function finalizeContract(DisciplineContract $contract): void
    {
        // Generate final report
        $report = $this->generateFinalReport($contract);

        // Store final report in archive
        try {
            $this->archiveService->storeFinalReport($contract, $report);
        } catch (\Exception $e) {
            \Log::error("Failed to store final report for contract {$contract->id}: " . $e->getMessage());
        }

        // Update contract status
        $contract->status = 'completed';
        $contract->save();

        \Log::info("Contract {$contract->id} finalized. Total submissions: {$contract->poems_submitted}, Misses: {$contract->poems_missed}");
    }

    /**
     * Generate final contract report
     */
    private function generateFinalReport(DisciplineContract $contract): array
    {
        $profile = $contract->userProfile;
        $logs = $contract->complianceLogs;

        $onTimeCount = $logs->where('on_time', true)->count();
        $missedCount = $contract->poems_missed;
        $completionRate = $contract->total_weeks > 0 
            ? round(($contract->poems_submitted / $contract->total_weeks) * 100, 2) 
            : 0;

        $constraintViolations = $contract->poems()
            ->whereNotNull('constraint_violations')
            ->get()
            ->sum(fn($poem) => is_array($poem->constraint_violations) ? count($poem->constraint_violations) : 0);

        return [
            'contract_id' => $contract->id,
            'user_profile_id' => $profile->id,
            'start_date' => $contract->start_date->toDateString(),
            'end_date' => $contract->end_date->toDateString(),
            'total_weeks' => $contract->total_weeks,
            'poems_submitted' => $contract->poems_submitted,
            'poems_missed' => $missedCount,
            'on_time_count' => $onTimeCount,
            'late_count' => $contract->poems_submitted - $onTimeCount,
            'completion_rate' => $completionRate . '%',
            'monthly_releases' => $contract->monthly_releases,
            'monthly_releases_missed' => $contract->monthly_releases_missed,
            'constraint_violations_total' => $constraintViolations,
            'final_penalty_status' => $contract->getMinimumLines() > 14 ? 'active' : 'none',
            'platform' => $profile->declared_platform,
            'finalized_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Verify public release URL
     */
    public function verifyPublicUrl(string $url, string $platform): array
    {
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'valid' => false,
                'reason' => 'Invalid URL format',
            ];
        }

        // Platform-specific URL validation
        $platformDomains = [
            'Medium' => 'medium.com',
            'Substack' => 'substack.com',
            'Twitter' => ['twitter.com', 'x.com'],
            'Personal Blog' => null, // Any domain allowed
        ];

        if (isset($platformDomains[$platform]) && $platformDomains[$platform] !== null) {
            $domains = is_array($platformDomains[$platform]) 
                ? $platformDomains[$platform] 
                : [$platformDomains[$platform]];

            $urlMatches = false;
            foreach ($domains as $domain) {
                if (str_contains(strtolower($url), $domain)) {
                    $urlMatches = true;
                    break;
                }
            }

            if (!$urlMatches) {
                return [
                    'valid' => false,
                    'reason' => "URL does not match declared platform: {$platform}",
                    'expected_domain' => implode(' or ', $domains),
                ];
            }
        }

        // Attempt HTTP verification
        try {
            $response = \Http::timeout(10)->get($url);

            if ($response->status() !== 200) {
                return [
                    'valid' => false,
                    'reason' => "URL returned HTTP {$response->status()}. Content may not be public.",
                ];
            }

            // Check for common "not found" indicators in HTML
            $body = strtolower($response->body());
            $notFoundIndicators = ['404', 'not found', 'page not found', 'does not exist'];
            
            foreach ($notFoundIndicators as $indicator) {
                if (str_contains($body, $indicator) && strlen($body) < 5000) {
                    return [
                        'valid' => false,
                        'reason' => 'URL appears to show a "not found" page',
                    ];
                }
            }

            return [
                'valid' => true,
                'verified_at' => now()->toIso8601String(),
                'status_code' => $response->status(),
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'reason' => 'URL is not publicly accessible: ' . $e->getMessage(),
            ];
        }
    }
}

