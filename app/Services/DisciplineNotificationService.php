<?php

namespace App\Services;

use App\Models\UserProfile;
use App\Models\DisciplineContract;
use App\Models\ComplianceLog;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * DisciplineNotificationService - DEADLINE WARNING SYSTEM
 * 
 * Monitors contract deadlines and sends warnings at critical thresholds:
 * - 48 hours before deadline
 * - 24 hours before deadline  
 * - 6 hours before deadline
 * - Recovery window active
 * - Pattern detected
 * - Penalty triggered
 */
class DisciplineNotificationService
{
    /**
     * Check all active contracts and generate notifications
     */
    public function checkNotifications(): array
    {
        $notifications = [];
        $contracts = DisciplineContract::where('status', 'active')->get();

        foreach ($contracts as $contract) {
            $contractNotifications = $this->checkContractNotifications($contract);
            $notifications = array_merge($notifications, $contractNotifications);
        }

        return $notifications;
    }

    /**
     * Check notifications for a specific contract
     */
    public function checkContractNotifications(DisciplineContract $contract): array
    {
        $notifications = [];
        $currentWeek = $contract->getCurrentWeekNumber();

        // Only check current week
        $complianceLog = $contract->complianceLogs()
            ->where('week_number', $currentWeek)
            ->where('status', 'pending')
            ->first();

        if (!$complianceLog) {
            return [];
        }

        $deadline = $complianceLog->deadline_at;
        $now = now();
        $hoursUntilDeadline = $now->diffInHours($deadline, false);

        // Check if in recovery window
        if ($now->gt($deadline) && $now->lt($deadline->copy()->addHours(24))) {
            $notifications[] = [
                'type' => 'recovery_window',
                'severity' => 'critical',
                'title' => 'Recovery Window Active',
                'message' => "Week {$currentWeek} deadline passed. You have until " . 
                            $deadline->copy()->addHours(24)->format('Y-m-d H:i') . 
                            " to submit without penalty.",
                'contract_id' => $contract->id,
                'week_number' => $currentWeek,
            ];
        }
        // Deadline warnings
        elseif ($hoursUntilDeadline <= 6 && $hoursUntilDeadline > 0) {
            $notifications[] = [
                'type' => 'deadline_6h',
                'severity' => 'critical',
                'title' => '6 Hour Warning',
                'message' => "Week {$currentWeek} deadline in 6 hours: " . $deadline->format('Y-m-d H:i'),
                'contract_id' => $contract->id,
                'week_number' => $currentWeek,
            ];
        } elseif ($hoursUntilDeadline <= 24 && $hoursUntilDeadline > 6) {
            $notifications[] = [
                'type' => 'deadline_24h',
                'severity' => 'high',
                'title' => '24 Hour Warning',
                'message' => "Week {$currentWeek} deadline tomorrow: " . $deadline->format('Y-m-d H:i'),
                'contract_id' => $contract->id,
                'week_number' => $currentWeek,
            ];
        } elseif ($hoursUntilDeadline <= 48 && $hoursUntilDeadline > 24) {
            $notifications[] = [
                'type' => 'deadline_48h',
                'severity' => 'medium',
                'title' => '48 Hour Notice',
                'message' => "Week {$currentWeek} deadline in 2 days: " . $deadline->format('Y-m-d H:i'),
                'contract_id' => $contract->id,
                'week_number' => $currentWeek,
            ];
        }

        // Check for unacknowledged patterns
        $unacknowledgedPatterns = $contract->userProfile->patternReports()
            ->where('acknowledged', false)
            ->get();

        if ($unacknowledgedPatterns->isNotEmpty()) {
            $notifications[] = [
                'type' => 'pattern_detected',
                'severity' => 'high',
                'title' => 'Unacknowledged Patterns',
                'message' => "You have {$unacknowledgedPatterns->count()} unacknowledged pattern(s). Address them before submitting.",
                'contract_id' => $contract->id,
                'pattern_count' => $unacknowledgedPatterns->count(),
            ];
        }

        // Check if penalty is active
        if ($contract->getMinimumLines() > 14) {
            $notifications[] = [
                'type' => 'penalty_active',
                'severity' => 'warning',
                'title' => 'Penalty Active',
                'message' => "Minimum line count increased to {$contract->getMinimumLines()} lines due to missed deadlines.",
                'contract_id' => $contract->id,
                'minimum_lines' => $contract->getMinimumLines(),
            ];
        }

        // Check if monthly release is overdue
        if ($contract->isMonthlyReleaseDue()) {
            $notifications[] = [
                'type' => 'monthly_release_due',
                'severity' => 'critical',
                'title' => 'Monthly Release Overdue',
                'message' => "Monthly release is required. Record and publish a poem before month end.",
                'contract_id' => $contract->id,
            ];
        }

        // Check for missing reflections
        if ($currentWeek > 1) {
            $previousWeek = $currentWeek - 1;
            $reflection = $contract->complianceLogs()
                ->where('week_number', $previousWeek)
                ->where('reflection_done', false)
                ->exists();

            if ($reflection) {
                $notifications[] = [
                    'type' => 'reflection_missing',
                    'severity' => 'critical',
                    'title' => 'Reflection Missing',
                    'message' => "Week {$previousWeek} reflection incomplete. Complete it before proceeding.",
                    'contract_id' => $contract->id,
                    'week_number' => $previousWeek,
                ];
            }
        }

        return $notifications;
    }

    /**
     * Get notifications for a specific user profile
     */
    public function getProfileNotifications(UserProfile $profile): array
    {
        $contract = $profile->activeDisciplineContract();

        if (!$contract) {
            return [];
        }

        return $this->checkContractNotifications($contract);
    }

    /**
     * Format notification for display (severity colors)
     */
    public function formatNotificationDisplay(array $notification): array
    {
        $colors = [
            'critical' => '#dc2626', // red
            'high' => '#ea580c',      // orange
            'warning' => '#ca8a04',   // yellow
            'medium' => '#0891b2',    // cyan
            'info' => '#4b5563',      // gray
        ];

        return [
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'severity' => $notification['severity'],
            'color' => $colors[$notification['severity']] ?? $colors['info'],
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Check if notification should be sent (rate limiting)
     * 
     * Prevents spam - only send each notification type once per time window:
     * - 48h warning: once
     * - 24h warning: once
     * - 6h warning: every 2 hours
     * - Recovery: every hour
     */
    public function shouldSendNotification(string $type, int $contractId, int $weekNumber): bool
    {
        // This would check against a notifications log table
        // For now, always return true (implement rate limiting later)
        return true;
    }

    /**
     * Send notification via configured channel
     * (placeholder for email/SMS/push implementation)
     */
    public function sendNotification(UserProfile $profile, array $notification): void
    {
        // TODO: Integrate with notification channels:
        // - Email (Laravel Mail)
        // - SMS (Twilio)
        // - Push (FCM)
        // - In-app toast
        
        // For now, just log
        \Log::info("Notification for profile {$profile->id}: {$notification['type']} - {$notification['message']}");
    }

    /**
     * Get notification summary for dashboard
     */
    public function getNotificationSummary(UserProfile $profile): array
    {
        $notifications = $this->getProfileNotifications($profile);
        
        $critical = collect($notifications)->where('severity', 'critical')->count();
        $high = collect($notifications)->where('severity', 'high')->count();
        $total = count($notifications);

        return [
            'total' => $total,
            'critical' => $critical,
            'high' => $high,
            'has_urgent' => $critical > 0 || $high > 0,
            'notifications' => array_map(
                fn($n) => $this->formatNotificationDisplay($n),
                $notifications
            ),
        ];
    }
}
