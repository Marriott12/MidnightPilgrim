<#
.SYNOPSIS
Production Readiness Verification for Midnight Pilgrim Discipline System

.DESCRIPTION
Validates all critical components before deployment

.NOTES
Version: 1.0
Date: February 13, 2026
#>

param(
    [switch]$Verbose
)

Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host "  MIDNIGHT PILGRIM - PRODUCTION READINESS VERIFICATION" -ForegroundColor Cyan
Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host ""

[int]$script:ErrorCount = 0
[int]$script:WarningCount = 0

function Test-PhpVersion {
    Write-Host "[1/10] Checking PHP version..." -ForegroundColor Yellow
    $version = php -v 2>&1 | Select-String "PHP 8\.[2-9]"
    if ($version) {
        Write-Host "  ✓ PHP 8.2+ detected" -ForegroundColor Green
        return $true
    }
    Write-Host "  ✗ PHP 8.2+ required" -ForegroundColor Red
    $script:ErrorCount++
    return $false
}

function Test-ComposerDependencies {
    Write-Host "[2/10] Checking Composer dependencies..." -ForegroundColor Yellow
    if (Test-Path "vendor/autoload.php") {
        Write-Host "  ✓ Vendor directory exists" -ForegroundColor Green
        return $true
    }
    Write-Host "  ✗ Run 'composer install' first" -ForegroundColor Red
    $script:ErrorCount++
    return $false
}

function Test-DatabaseConnection {
    Write-Host "[3/10] Testing database connection..." -ForegroundColor Yellow
    $result = php artisan migrate:status 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✓ Database connected" -ForegroundColor Green
        return $true
    }
    Write-Host "  ✗ Database connection failed" -ForegroundColor Red
    $script:ErrorCount++
    return $false
}

function Test-CriticalMigrations {
    Write-Host "[4/10] Verifying critical migrations..." -ForegroundColor Yellow
    $required = @(
        "2026_02_13_000006_add_archive_and_tracking_fields.php",
        "2026_02_13_000007_create_compliance_logs_table.php",
        "2026_02_13_000008_create_poem_revisions_table.php"
    )
    
   $missing = $required | Where-Object { !(Test-Path "database/migrations/$_") }
    
    if ($missing.Count -eq 0) {
        Write-Host "  ✓ All critical migrations present" -ForegroundColor Green
        return $true
    }
    Write-Host "  ✗ Missing: $($missing -join ', ')" -ForegroundColor Red
    $script:ErrorCount++
    return $false
}

function Test-CriticalServices {
    Write-Host "[5/10] Verifying critical services..." -ForegroundColor Yellow
    $required = @(
        "app/Services/ArchiveEnforcementService.php",
        "app/Services/ConstraintValidationService.php",
        "app/Services/DisciplineContractService.php",
        "app/Services/DisciplineNotificationService.php"
    )
    
    $missing = $required | Where-Object { !(Test-Path $_) }
    
    if ($missing.Count -eq 0) {
        Write-Host "  ✓ All critical services present" -ForegroundColor Green
        return $true
    }
    Write-Host "  ✗ Missing: $($missing -join ', ')" -ForegroundColor Red
    $script:ErrorCount++
    return $false
}

function Test-AppServiceProvider {
    Write-Host "[6/10] Checking AppServiceProvider..." -ForegroundColor Yellow
    if (Test-Path "app/Providers/AppServiceProvider.php") {
        Write-Host "  ✓ AppServiceProvider exists" -ForegroundColor Green
        return $true
    }
    Write-Host "  ✗ AppServiceProvider missing" -ForegroundColor Red
    $script:ErrorCount++
    return $false
}

function Test-StoragePermissions {
    Write-Host "[7/10] Checking storage directories..." -ForegroundColor Yellow
    $dirs = @("storage/app", "storage/framework", "storage/logs")
    $issues = $dirs | Where-Object { !(Test-Path $_) }
    
    if ($issues.Count -eq 0) {
        Write-Host "  ✓ Storage directories exist" -ForegroundColor Green
        return $true
    }
    Write-Host "  ⚠ Missing dirs: $($issues -join ', ')" -ForegroundColor Yellow
    $script:WarningCount++
    return $false
}

function Test-CriticalModels {
    Write-Host "[8/10] Checking critical models..." -ForegroundColor Yellow
    $required = @(
        "app/Models/ComplianceLog.php",
        "app/Models/PoemRevision.php",
        "app/Models/Poem.php",
        "app/Models/DisciplineContract.php"
    )
    
    $missing = $required | Where-Object { !(Test-Path $_) }
    
    if ($missing.Count -eq 0) {
        Write-Host "  ✓ All critical models present" -ForegroundColor Green
        return $true
    }
    Write-Host "  ✗ Missing: $($missing -join ', ')" -ForegroundColor Red
    $script:ErrorCount++
    return $false
}

function Test-EnvironmentConfig {
    Write-Host "[9/10] Checking environment configuration..." -ForegroundColor Yellow
    if (!(Test-Path ".env")) {
        Write-Host "  ✗ .env file missing" -ForegroundColor Red
        $script:ErrorCount++
        return $false
    }
    
    $env = Get-Content ".env" -Raw
    $hasKey = $env -match "APP_KEY=base64:"
    $hasDB = $env -match "DB_CONNECTION="
    
    if ($hasKey -and $hasDB) {
        Write-Host "  ✓ Environment configured" -ForegroundColor Green
        return $true
    }
    
    if (!$hasKey) {
        Write-Host "  ⚠ APP_KEY not set - run 'php artisan key:generate'" -ForegroundColor Yellow
        $script:WarningCount++
    }
    if (!$hasDB) {
        Write-Host "  ⚠ Database not configured" -ForegroundColor Yellow
        $script:WarningCount++
    }
    return $false
}

function Test-SyntaxValidation {
    Write-Host "[10/10] Running syntax validation..." -ForegroundColor Yellow
    $files = @(
        "app/Services/DisciplineContractService.php",
        "app/Services/ArchiveEnforcementService.php",
        "app/Http/Controllers/AdaptiveConversationController.php"
    )
    
    $errors = @()
    foreach ($file in $files) {
        $result = php -l $file 2>&1
        if ($LASTEXITCODE -ne 0) {
            $errors += $file
        }
    }
    
    if ($errors.Count -eq 0) {
        Write-Host "  ✓ No syntax errors detected" -ForegroundColor Green
        return $true
    }
    Write-Host "  ✗ Syntax errors in: $($errors -join ', ')" -ForegroundColor Red
    $script:ErrorCount++
    return $false
}

# Run all tests
Test-PhpVersion
Test-ComposerDependencies
Test-DatabaseConnection
Test-CriticalMigrations
Test-CriticalServices
Test-AppServiceProvider
Test-StoragePermissions
Test-CriticalModels
Test-EnvironmentConfig
Test-SyntaxValidation

# Summary
Write-Host ""
Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host "  VERIFICATION COMPLETE" -ForegroundColor Cyan
Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host ""

if ($script:ErrorCount -eq 0 -and $script:WarningCount -eq 0) {
    Write-Host "  ✓ ALL CHECKS PASSED - SYSTEM READY FOR PRODUCTION" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "  1. Run: .\apply-discipline-fixes.ps1" -ForegroundColor White
    Write-Host "  2. Start scheduler: php artisan schedule:work" -ForegroundColor White
    Write-Host "  3. Test contract init: POST /api/discipline/init" -ForegroundColor White
    exit 0
} elseif ($script:ErrorCount -eq 0) {
    Write-Host "  ⚠ $script:WarningCount WARNINGS - System functional but review recommended" -ForegroundColor Yellow
    exit 0
} else {
    Write-Host "  ✗ $script:ErrorCount ERRORS, $script:WarningCount WARNINGS" -ForegroundColor Red
    Write-Host "  Fix errors before deployment" -ForegroundColor Red
    exit 1
}
}
