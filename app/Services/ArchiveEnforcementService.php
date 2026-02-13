<?php

namespace App\Services;

use App\Models\Poem;
use App\Models\DisciplineContract;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

/**
 * ArchiveEnforcementService - FILE SYSTEM DISCIPLINE
 * 
 * Enforces structured archive folders for all poems.
 * Prevents overwrites. Prevents deletions.
 * Requires reflections before continuation.
 */
class ArchiveEnforcementService
{
    private string $basePath = 'discipline_archives';

    /**
     * Get contract archive path
     */
    public function getContractPath(DisciplineContract $contract): string
    {
        $start = $contract->start_date->format('M_d');
        $end = $contract->end_date->format('M_d_Y');
        
        return "{$this->basePath}/Midnight_Pilgrim_Contract_{$start}_{$end}";
    }

    /**
     * Get week folder path
     */
    public function getWeekPath(DisciplineContract $contract, int $weekNumber): string
    {
        $contractPath = $this->getContractPath($contract);
        return "{$contractPath}/Week_" . str_pad($weekNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Initialize contract archive structure
     */
    public function initializeContractArchive(DisciplineContract $contract): void
    {
        $contractPath = $this->getContractPath($contract);
        
        // Create base contract folder
        $this->ensureDirectoryExists($contractPath);

        // Create all weekly folders
        for ($week = 1; $week <= $contract->total_weeks; $week++) {
            $weekPath = $this->getWeekPath($contract, $week);
            $this->ensureDirectoryExists($weekPath);
            
            // Create subfolders
            $this->ensureDirectoryExists("{$weekPath}/drafts");
            $this->ensureDirectoryExists("{$weekPath}/revisions");
            $this->ensureDirectoryExists("{$weekPath}/final");
            $this->ensureDirectoryExists("{$weekPath}/reflection");
        }

        // Create README
        $this->createArchiveReadme($contractPath, $contract);
    }

    /**
     * Check if previous week has reflection
     */
    public function hasPreviousWeekReflection(DisciplineContract $contract, int $currentWeek): bool
    {
        if ($currentWeek <= 1) {
            return true; // No previous week for week 1
        }

        $previousWeekPath = $this->getWeekPath($contract, $currentWeek - 1);
        $reflectionPath = "{$previousWeekPath}/reflection/Reflection.md";
        
        return $this->fileExists($reflectionPath);
    }

    /**
     * Save poem draft to archive
     */
    public function saveDraft(Poem $poem, string $content, int $draftNumber = 1): string
    {
        $contract = $poem->userProfile->activeDisciplineContract();
        $weekPath = $this->getWeekPath($contract, $poem->week_number);
        
        $filename = "Draft_v{$draftNumber}.md";
        $filepath = "{$weekPath}/drafts/{$filename}";
        
        // Prevent overwrite
        if ($this->fileExists($filepath)) {
            throw new \Exception("Draft v{$draftNumber} already exists. Cannot overwrite.");
        }

        $this->writeFile($filepath, $this->formatPoemContent($content, $poem));
        
        return $filepath;
    }

    /**
     * Save revision to archive
     */
    public function saveRevision(Poem $poem, string $content, int $revisionNumber, string $changesMade): string
    {
        $contract = $poem->userProfile->activeDisciplineContract();
        $weekPath = $this->getWeekPath($contract, $poem->week_number);
        
        $filename = "Draft_v{$revisionNumber}_revision.md";
        $filepath = "{$weekPath}/revisions/{$filename}";
        
        // Prevent overwrite
        if ($this->fileExists($filepath)) {
            throw new \Exception("Revision v{$revisionNumber} already exists. Cannot overwrite.");
        }

        $fullContent = $this->formatPoemContent($content, $poem);
        $fullContent .= "\n\n---\n\n## REVISION NOTES\n\n{$changesMade}";

        $this->writeFile($filepath, $fullContent);
        
        return $filepath;
    }

    /**
     * Save final version to archive
     */
    public function saveFinal(Poem $poem, string $content): string
    {
        $contract = $poem->userProfile->activeDisciplineContract();
        $weekPath = $this->getWeekPath($contract, $poem->week_number);
        
        $filename = "Final.md";
        $filepath = "{$weekPath}/final/{$filename}";
        
        // Prevent overwrite
        if ($this->fileExists($filepath)) {
            throw new \Exception("Final version already exists. Cannot overwrite.");
        }

        $this->writeFile($filepath, $this->formatPoemContent($content, $poem));
        
        return $filepath;
    }

    /**
     * Create reflection file
     */
    public function createReflection(DisciplineContract $contract, int $weekNumber, ?string $content = null): string
    {
        $weekPath = $this->getWeekPath($contract, $weekNumber);
        $filepath = "{$weekPath}/reflection/Reflection.md";

        if ($content) {
            // User-provided reflection content
            $formatted = "# Reflection - Week {$weekNumber}\n\n";
            $formatted .= "**Completed:** " . now()->toDateTimeString() . "\n\n";
            $formatted .= "---\n\n";
            $formatted .= $content;
            
            $this->writeFile($filepath, $formatted);
        } else {
            // Template only (don't overwrite if exists)
            $template = $this->getReflectionTemplate($weekNumber);
            if (!$this->fileExists($filepath)) {
                $this->writeFile($filepath, $template);
            }
        }

        return $filepath;
    }

    /**
     * Format poem content for archive
     */
    private function formatPoemContent(string $content, Poem $poem): string
    {
        $formatted = "# Poem - Week {$poem->week_number}\n\n";
        $formatted .= "**Constraint:** {$poem->constraint_type}\n";
        $formatted .= "**Submitted:** " . ($poem->submitted_at ? $poem->submitted_at->toDateTimeString() : 'Not submitted') . "\n";
        $formatted .= "**Lines:** {$poem->line_count}\n\n";
        $formatted .= "---\n\n";
        $formatted .= $content;
        
        return $formatted;
    }

    /**
     * Get reflection template
     */
    private function getReflectionTemplate(int $weekNumber): string
    {
        return <<<MD
# Reflection - Week {$weekNumber}

## What worked this week?


## What didn't work?


## Where was I lazy?


## Where did I hide behind abstraction?


## What line is weakest and why?


## What risk did I avoid?


## What will I do differently next week?


MD;
    }

    /**
     * Create archive README
     */
    private function createArchiveReadme(string $contractPath, DisciplineContract $contract): void
    {
        $readme = <<<MD
# Midnight Pilgrim Discipline Contract Archive

**Start Date:** {$contract->start_date->toDateString()}
**End Date:** {$contract->end_date->toDateString()}
**Total Weeks:** {$contract->total_weeks}

## Archive Structure

Each week contains:
- `/drafts` - Initial drafts (Draft_v1.md, Draft_v2.md, etc.)
- `/revisions` - Revision passes (Draft_v2_revision.md, etc.)
- `/final` - Final submitted version (Final.md)
- `/reflection` - Weekly reflection (Reflection.md)

## Rules

- **No overwrites allowed**
- **No deletions allowed**
- **Reflection required before continuing to next week**

## Contract Requirements

**Weekly:**
- 1 completed poem (minimum 14 lines)
- 1 structured revision pass
- Due Sunday 20:00
- Recovery window: 24 hours

**Monthly:**
- 1 recorded and publicly released poem
- Deadline: Last day of month, 18:00

**Penalties:**
- 2 misses in one month → 28 line minimum next poem
- Missed monthly release → 2 releases required following month

MD;

        $this->writeFile("{$contractPath}/README.md", $readme);
    }

    /**
     * Ensure directory exists
     */
    private function ensureDirectoryExists(string $path): void
    {
        $fullPath = storage_path("app/{$path}");
        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }
    }

    /**
     * Write file to storage
     */
    private function writeFile(string $path, string $content): void
    {
        Storage::put($path, $content);
    }

    /**
     * Check if file exists
     */
    private function fileExists(string $path): bool
    {
        return Storage::exists($path);
    }

    /**
     * Get file content
     */
    public function getFileContent(string $path): ?string
    {
        return Storage::exists($path) ? Storage::get($path) : null;
    }

    /**
     * List all files in week folder
     */
    public function getWeekFiles(DisciplineContract $contract, int $weekNumber): array
    {
        $weekPath = $this->getWeekPath($contract, $weekNumber);
        
        return [
            'drafts' => Storage::files("{$weekPath}/drafts"),
            'revisions' => Storage::files("{$weekPath}/revisions"),
            'final' => Storage::files("{$weekPath}/final"),
            'reflection' => Storage::files("{$weekPath}/reflection"),
        ];
    }

    /**
     * Store final contract report
     */
    public function storeFinalReport(DisciplineContract $contract, array $report): void
    {
        $archiveRoot = $this->getArchiveRoot($contract);
        $reportPath = "{$archiveRoot}/FINAL_REPORT.md";

        // Format report as markdown
        $markdown = "# DISCIPLINE CONTRACT - FINAL REPORT\n\n";
        $markdown .= "**Contract ID:** {$report['contract_id']}\n";
        $markdown .= "**Period:** {$report['start_date']} to {$report['end_date']}\n";
        $markdown .= "**Platform:** {$report['platform']}\n";
        $markdown .= "**Finalized:** {$report['finalized_at']}\n\n";
        
        $markdown .= "---\n\n";
        $markdown .= "## PERFORMANCE SUMMARY\n\n";
        $markdown .= "**Total Weeks:** {$report['total_weeks']}\n";
        $markdown .= "**Poems Submitted:** {$report['poems_submitted']}\n";
        $markdown .= "**Poems Missed:** {$report['poems_missed']}\n";
        $markdown .= "**Completion Rate:** {$report['completion_rate']}\n\n";
        
        $markdown .= "**On-Time Submissions:** {$report['on_time_count']}\n";
        $markdown .= "**Late Submissions:** {$report['late_count']}\n\n";
        
        $markdown .= "**Monthly Releases:** {$report['monthly_releases']}\n";
        $markdown .= "**Missed Monthly Releases:** {$report['monthly_releases_missed']}\n\n";
        
        $markdown .= "**Total Constraint Violations:** {$report['constraint_violations_total']}\n";
        $markdown .= "**Final Penalty Status:** {$report['final_penalty_status']}\n\n";
        
        $markdown .= "---\n\n";
        $markdown .= "*This contract has been completed and archived. All submissions are immutable.*\n";

        Storage::put($reportPath, $markdown);
    }
}
