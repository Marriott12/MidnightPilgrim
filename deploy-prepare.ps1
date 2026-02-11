# Deployment Preparation Script for Shared Hosting
# This script prepares your Midnight Pilgrim installation for FTP upload

Write-Host "=== Midnight Pilgrim - Deployment Preparation ===" -ForegroundColor Cyan
Write-Host ""

# Check if we're in the right directory
if (-not (Test-Path "artisan")) {
    Write-Host "Error: This script must be run from the Midnight Pilgrim root directory!" -ForegroundColor Red
    exit 1
}

# Step 1: Install production dependencies
Write-Host "[1/6] Installing production dependencies..." -ForegroundColor Yellow
composer install --no-dev --optimize-autoloader

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Composer install failed!" -ForegroundColor Red
    exit 1
}

# Step 2: Generate production APP_KEY
Write-Host "`n[2/6] Generating production APP_KEY..." -ForegroundColor Yellow
$appKey = php artisan key:generate --show
Write-Host "Your production APP_KEY: $appKey" -ForegroundColor Green
Write-Host "Save this for your .env.production file!" -ForegroundColor Yellow

# Step 3: Create .env.production template
Write-Host "`n[3/6] Creating .env.production template..." -ForegroundColor Yellow
$envProduction = @"
APP_NAME=MidnightPilgrim
APP_ENV=production
APP_KEY=$appKey
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=/home/username/midnightpilgrim_db/database.sqlite

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=525600

CACHE_STORE=file

# Optional: OpenAI API for conversation features
# OPENAI_API_KEY=your_openai_key_here
"@

$envProduction | Out-File -FilePath ".env.production" -Encoding UTF8
Write-Host ".env.production created successfully!" -ForegroundColor Green

# Step 4: Clear all caches
Write-Host "`n[4/6] Clearing caches..." -ForegroundColor Yellow
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Step 5: Create deployment package directory
Write-Host "`n[5/6] Creating deployment package..." -ForegroundColor Yellow
$deployDir = "deployment_package"
if (Test-Path $deployDir) {
    Remove-Item $deployDir -Recurse -Force
}
New-Item -ItemType Directory -Path $deployDir | Out-Null

# Copy necessary files
Write-Host "Copying files..." -ForegroundColor Gray
$itemsToCopy = @(
    "app",
    "bootstrap",
    "config",
    "database\migrations",
    "public",
    "resources",
    "routes",
    "storage\framework\cache\.gitkeep",
    "storage\framework\sessions\.gitkeep",  
    "storage\framework\views\.gitkeep",
    "storage\logs\.gitkeep",
    "vendor",
    "artisan",
    "composer.json",
    "composer.lock"
)

foreach ($item in $itemsToCopy) {
    $source = $item -replace '\\', '/'
    if (Test-Path $source) {
        $destination = Join-Path $deployDir $item
        $destDir = Split-Path $destination -Parent
        
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        
        if (Test-Path $source -PathType Container) {
            Copy-Item $source $destination -Recurse -Force
        } else {
            Copy-Item $source $destination -Force
        }
        Write-Host "  Copied: $item" -ForegroundColor DarkGray
    }
}

# Copy .env.production as .env in deployment package
Copy-Item ".env.production" (Join-Path $deployDir ".env") -Force
Write-Host "  Copied: .env.production → .env" -ForegroundColor DarkGray

# Step 6: Create README for deployment
Write-Host "`n[6/6] Creating deployment instructions..." -ForegroundColor Yellow
$deployReadme = @"
# Midnight Pilgrim - Deployment Package

This package is ready for upload to your shared hosting server.

## Quick Start

1. Upload all contents to your server (e.g., ~/midnightpilgrim/)
2. Point your domain's document root to the 'public' folder
3. Edit .env file with your production settings
4. Set permissions on storage/ and bootstrap/cache/ to 755
5. Create database.sqlite outside public_html
6. Run migrations (if SSH available): php artisan migrate --force

## Full Instructions

See DEPLOYMENT.md in the repository for complete step-by-step guide.

## Security Notes

- .env contains your APP_KEY - keep it secure!
- Database path must be outside public_html
- Ensure APP_DEBUG=false before going live
- Enable HTTPS/SSL on your domain

## Support

Check storage/logs/laravel.log for errors.

Generated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
"@

$deployReadme | Out-File(Join-Path $deployDir "README_DEPLOYMENT.txt") -Encoding UTF8

Write-Host "`n=== Deployment Preparation Complete! ===" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Review and edit $deployDir\.env with your production settings" -ForegroundColor White
Write-Host "2. Update APP_URL and database path in .env" -ForegroundColor White
Write-Host "3. Upload contents of '$deployDir' folder to your server via FTP" -ForegroundColor White
Write-Host "4. Follow instructions in DEPLOYMENT.md" -ForegroundColor White
Write-Host ""
Write-Host "Deployment package created in: $deployDir\" -ForegroundColor Yellow

# Restore dev dependencies
Write-Host "`nRestoring development dependencies..." -ForegroundColor Gray
composer install > $null

Write-Host "Done!" -ForegroundColor Green
