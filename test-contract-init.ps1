# Test Contract Initialization
Write-Host "Testing Discipline Contract Initialization..." -ForegroundColor Cyan

# Test service resolution
Write-Host "`nStep 1: Testing service container..." -ForegroundColor Yellow
php artisan tinker --execute="echo app(App\Services\DisciplineContractService::class) ? 'OK' : 'FAIL';"

# Test contract init endpoint
Write-Host "`nStep 2: Calling contract init endpoint..." -ForegroundColor Yellow
$response = Invoke-WebRequest -Uri "http://localhost:8000/api/discipline/init" `
    -Method POST `
    -ContentType "application/json" `
    -Body '{}' `
    -UseBasicParsing

Write-Host "Status: $($response.StatusCode)" -ForegroundColor $(if ($response.StatusCode -eq 200) { 'Green' } else { 'Red' })
Write-Host "Response:" -ForegroundColor Cyan
$response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 5

# Verify database
Write-Host "`nStep 3: Verifying database records..." -ForegroundColor Yellow
php artisan tinker --execute="echo 'Contracts: ' . App\Models\DisciplineContract::count(); echo ' | Logs: ' . App\Models\ComplianceLog::count();"

# Check archive folder
Write-Host "`nStep 4: Checking archive structure..." -ForegroundColor Yellow
$archivePath = "storage\app\discipline_archives"
if (Test-Path $archivePath) {
    $folders = Get-ChildItem $archivePath -Directory
    Write-Host "Archive folders found: $($folders.Count)" -ForegroundColor Green
    $folders | ForEach-Object { Write-Host "  - $($_.Name)" -ForegroundColor Gray }
} else {
    Write-Host "No archive folder created yet" -ForegroundColor Yellow
}

Write-Host "`nProduction Test Complete!" -ForegroundColor Green
