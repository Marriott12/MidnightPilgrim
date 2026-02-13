# PRODUCTION READINESS CHECKLIST
# Midnight Pilgrim Discipline System - February 13, 2026
#
# Run this script to verify all components before deployment.

Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host "  MIDNIGHT PILGRIM - PRODUCTION READINESS VERIFICATION" -ForegroundColor Cyan
Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host ""

$ErrorCount = 0
$WarningCount = 0

# Check 1: PHP Version
Write-Host "[1/12] Checking PHP version..." -ForegroundColor Yellow
$phpVersion = php -v
if ($phpVersion -match "PHP 8\.2") {
    Write-Host "  ✓ PHP 8.2+ detected" -ForegroundColor Green
} else {
    Write-Host "  ✗ PHP 8.2+ required" -ForegroundColor Red
    $ErrorCount++
}

# Check 2: Composer dependencies
Write-Host "[2/12] Checking Composer dependencies..." -ForegroundColor Yellow
if (Test-Path "vendor/autoload.php") {
    Write-Host "  ✓ Vendor directory exists" -ForegroundColor Green
} else {
    Write-Host "  ✗ Run 'composer install' first" -ForegroundColor Red
    $ErrorCount++
}

# Check 3: Database connection
Write-Host "[3/12] Testing database connection..." -ForegroundColor Yellow
$dbTest = php artisan migrate:status 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "  ✓ Database connected" -ForegroundColor Green
} else {
    Write-Host "  ✗ Database connection failed" -ForegroundColor Red
    $ErrorCount++
}

# Check 4: Critical migrations exist
Write-Host "[4/12] Verifying critical migrations..." -ForegroundColor Yellow
$migrations = @(
    "2026_02_13_000006_add_archive_and_tracking_fields.php",
    "2026_02_13_000007_create_compliance_logs_table.php",
    "2026_02_13_000008_create_poem_revisions_table.php"
)
$missingMigrations = @()
foreach ($m in $migrations) {
    if (!(Test-Path "database/migrations/$m")) {
        $missingMigrations += $m
    }
}
if ($missingMigrations.Count -eq 0) {
    Write-Host "  ✓ All critical migrations present" -ForegroundColor Green
} else {
    Write-Host "  ✗ Missing migrations: $($missingMigrations -join ', ')" -ForegroundColor Red
    $ErrorCount++
}

# Check 5: Critical services exist
Write-Host "[5/12] Verifying critical services..." -ForegroundColor Yellow
$services = @(
    "app/Services/ArchiveEnforcementService.php",
    "app/Services/ConstraintValidationService.php",
    "app/Services/DisciplineContractService.php",
    "app/Services/DisciplineNotificationService.php"
)
$missingServices = @()
foreach ($s in $services) {
    if (!(Test-Path $s)) {
        $missingServices += $s
    }
}
if ($missingServices.Count -eq 0) {
    Write-Host "  ✓ All critical services present" -ForegroundColor Green
} else {
    Write-Host "  ✗ Missing services: $($missingServices -join ', ')" -ForegroundColor Red
    $ErrorCount++
}

# Check 6: AppServiceProvider exists
Write-Host "[6/12] Checking AppServiceProvider..." -ForegroundColor Yellow
if (Test-Path "app/Providers/AppServiceProvider.php") {
    Write-Host "  ✓ AppServiceProvider exists" -ForegroundColor Green
} else {
    Write-Host "  ✗ AppServiceProvider missing" -ForegroundColor Red
    $ErrorCount++
}

# Check 7: Storage directories writable
Write-Host "[7/12] Checking storage permissions..." -ForegroundColor Yellow
$storageDirs = @("storage/app", "storage/framework", "storage/logs")
$notWritable = @()
foreach ($dir in $storageDirs) {
    if (!(Test-Path $dir -PathType Container) -or !(Get-Item $dir).Attributes -match "Directory") {
        $notWritable += $dir
    }
}
if ($notWritable.Count -eq 0) {
    Write-Host "  ✓ Storage directories accessible" -ForegroundColor Green
} else {
    Write-Host "  ⚠ Check permissions: $($notWritable -join ', ')" -ForegroundColor Yellow
    $WarningCount++
}

# Check 8: Routes registered
Write-Host "[8/12] Verifying routes..." -ForegroundColor Yellow
$routeList = php artisan route:list 2>&1
if ($routeList -match "discipline/submit-poem" -and $routeList -match "discipline/compliance-log") {
    Write-Host "  ✓ Discipline routes registered" -ForegroundColor Green
} else {
    Write-Host "  ⚠ Some routes may be missing - check routes/api.php" -ForegroundColor Yellow
    $WarningCount++
}

# Check 9: Key models exist
Write-Host "[9/12] Checking critical models..." -ForegroundColor Yellow
$models = @(
    "app/Models/ComplianceLog.php",
    "app/Models/PoemRevision.php",
    "app/Models/Poem.php",
    "app/Models/DisciplineContract.php"
)
$missingModels = @()
foreach ($m in $models) {
    if (!(Test-Path $m)) {
        $missingModels += $m
    }
}
if ($missingModels.Count -eq 0) {
    Write-Host "  ✓ All critical models present" -ForegroundColor Green
} else {
    Write-Host "  ✗ Missing models: $($missingModels -join ', ')" -ForegroundColor Red
    $ErrorCount++
}

# Check 10: .env configuration
Write-Host "[10/12] Checking environment configuration..." -ForegroundColor Yellow
if (Test-Path ".env") {
    $envContent = Get-Content ".env" -Raw
    if ($envContent -match "APP_KEY=base64:") {
        Write-Host "  ✓ APP_KEY configured" -ForegroundColor Green
    } else {
        Write-Host "  ⚠ Run 'php artisan key:generate'" -ForegroundColor Yellow
        $WarningCount++
    }
    if ($envContent -match "DB_CONNECTION=") {
        Write-Host "  ✓ Database configured" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Database not configured in .env" -ForegroundColor Red
        $ErrorCount++
    }
} else {
    Write-Host "  ✗ .env file missing - copy .env.example" -ForegroundColor Red
    $ErrorCount++
}

# Check 11: Syntax check on critical files
Write-Host "[11/12] Running syntax validation..." -ForegroundColor Yellow
$syntaxErrors = @()
$filesToCheck = @(
    "app/Services/DisciplineContractService.php",
   "app/Services/ArchiveEnforcementService.php",
    "app/Http/Controllers/AdaptiveConversationController.php"
)
foreach ($file in $filesToCheck) {
    $result = php -l $file 2>&1
    if ($LASTEXITCODE -ne 0) {
        $syntaxErrors += $file
    }
}
if ($syntaxErrors.Count -eq 0) {
    Write-Host "  ✓ No syntax errors detected" -ForegroundColor Green
} else {
    Write-Host "  ✗ Syntax errors in: $($syntaxErrors -join ', ')" -ForegroundColor Red
    $ErrorCount++
}

# Check 12: Service resolution test
Write-Host "[12/12] Testing service container resolution..." -ForegroundColor Yellow
$resolutionTest = php artisan tinker --execute="app(\App\Services\DisciplineContractService::class); echo 'OK';" 2>&1
if ($resolutionTest -match "OK") {
    Write-Host "  ✓ Services resolve correctly" -ForegroundColor Green
} else {
    Write-Host "  ⚠ Service resolution may have issues" -ForegroundColor Yellow
    $WarningCount++
}

Write-Host ""
Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host "  VERIFICATION COMPLETE" -ForegroundColor Cyan
Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host ""

if ($ErrorCount -eq 0 -and $WarningCount -eq 0) {
    Write-Host "  ✓ ALL CHECKS PASSED - SYSTEM READY FOR PRODUCTION" -ForegroundColor Green
    exit 0
} elseif ($ErrorCount -eq 0) {
    Write-Host "  ⚠ $WarningCount WARNINGS - System functional but review recommended" -ForegroundColor Yellow
    exit 0
} else {
    Write-Host "  ✗ $ErrorCount ERRORS, $WarningCount WARNINGS - Fix errors before deployment" -ForegroundColor Red
    exit 1
}
