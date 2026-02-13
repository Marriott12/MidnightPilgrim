# Apply New Discipline System Migrations
# Run this script to apply all architecture fixes

Write-Host "=== MIDNIGHT PILGRIM DISCIPLINE SYSTEM - MIGRATION RUNNER ===" -ForegroundColor Cyan
Write-Host ""

# Check if composer dependencies are installed
if (!(Test-Path "vendor/autoload.php")) {
    Write-Host "[ERROR] Vendor directory not found. Run 'composer install' first." -ForegroundColor Red
    exit 1
}

Write-Host "[1/4] Running new migrations..." -ForegroundColor Yellow
php artisan migrate --path=database/migrations/2026_02_13_000006_add_archive_and_tracking_fields.php
php artisan migrate --path=database/migrations/2026_02_13_000007_create_compliance_logs_table.php
php artisan migrate --path=database/migrations/2026_02_13_000008_create_poem_revisions_table.php

if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Migration failed. Check database connection." -ForegroundColor Red
    exit 1
}

Write-Host "[2/4] Clearing application cache..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

Write-Host "[3/4] Verifying service resolution..." -ForegroundColor Yellow
php artisan tinker --execute="app(\App\Services\ArchiveEnforcementService::class)"
php artisan tinker --execute="app(\App\Services\ConstraintValidationService::class)"
php artisan tinker --execute="app(\App\Services\DisciplineNotificationService::class)"

if ($LASTEXITCODE -ne 0) {
    Write-Host "[WARNING] Service resolution failed. Check service registrations." -ForegroundColor Yellow
}

Write-Host "[4/4] Registering scheduled tasks..." -ForegroundColor Yellow
Write-Host "    - Deadline check: Hourly at :00" -ForegroundColor Gray
Write-Host "    - Run 'php artisan schedule:work' to enable scheduled tasks" -ForegroundColor Gray

Write-Host ""
Write-Host "=== MIGRATION COMPLETE ===" -ForegroundColor Green
Write-Host ""
Write-Host "NEW FEATURES ENABLED:" -ForegroundColor Cyan
Write-Host "  ✓ Archive enforcement with structured folders" -ForegroundColor White
Write-Host "  ✓ Compliance log dashboard (single source of truth)" -ForegroundColor White
Write-Host "  ✓ Constraint validation (concrete imagery, no metaphors, sustained metaphor, second person)" -ForegroundColor White
Write-Host "  ✓ Revision tracking with diff calculation" -ForegroundColor White
Write-Host "  ✓ Timezone-aware deadlines" -ForegroundColor White
Write-Host "  ✓ Reflection requirement checking" -ForegroundColor White
Write-Host "  ✓ Pattern blocking (reject submissions with unacknowledged patterns)" -ForegroundColor White
Write-Host "  ✓ Session resume/new choice flow" -ForegroundColor White
Write-Host "  ✓ Notification system (48h, 24h, 6h warnings, recovery window, penalties)" -ForegroundColor White
Write-Host "  ✓ Recording and public URL tracking for monthly releases" -ForegroundColor White
Write-Host ""
Write-Host "NEW API ENDPOINTS:" -ForegroundColor Cyan
Write-Host "  POST /api/conversation/resume-decision" -ForegroundColor White
Write-Host "  POST /api/discipline/submit-revision" -ForegroundColor White
Write-Host "  GET  /api/discipline/compliance-log" -ForegroundColor White
Write-Host "  POST /api/discipline/upload-recording" -ForegroundColor White
Write-Host "  POST /api/discipline/complete-reflection" -ForegroundColor White
Write-Host "  GET  /api/discipline/notifications" -ForegroundColor White
Write-Host ""
Write-Host "CRITICAL CHANGES:" -ForegroundColor Yellow
Write-Host "  ⚠ Archive structure enforced - NO overwrites or deletions allowed" -ForegroundColor White
Write-Host "  ⚠ Reflections REQUIRED before week progression" -ForegroundColor White
Write-Host "  ⚠ Constraint violations REJECT submissions (3+ violations or any critical)" -ForegroundColor White
Write-Host "  ⚠ Self-assessment minimum 20 characters per question" -ForegroundColor White
Write-Host "  ⚠ Automated deadline checking runs hourly" -ForegroundColor White
Write-Host ""
Write-Host "NEXT STEPS:" -ForegroundColor Cyan
Write-Host "  1. Start scheduler: php artisan schedule:work" -ForegroundColor White
Write-Host "  2. Test contract init: POST /api/discipline/init" -ForegroundColor White
Write-Host "  3. Verify archive structure: storage/app/discipline_archives/" -ForegroundColor White
Write-Host "  4. Test constraint validation with sample poems" -ForegroundColor White
Write-Host ""
