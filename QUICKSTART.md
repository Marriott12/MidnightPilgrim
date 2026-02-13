# Midnight Pilgrim - Quick Start Commands

# 1. Activate PHP 8.2 (run this in each new terminal session)
. .\use-php82.ps1

# 2. Start the development server (choose one method)

## Method A: Simple command line
php artisan serve

## Method B: Background process (server runs in separate window)
.\serve.bat

## Method C: Specific port
php artisan serve --port=8080

# 3. Test the API

## Test status endpoint
curl http://localhost:8000/api/status

## Initialize a conversation session
$body = @{
    mode = "quiet"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/conversation/init" -Method POST -Body $body -ContentType "application/json"

## Send a message
$body = @{
    session_uuid = "YOUR_SESSION_UUID_HERE"
    message = "I'm feeling overwhelmed today"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/conversation/message" -Method POST -Body $body -ContentType "application/json"

## Get random philosophical prompt
Invoke-RestMethod -Uri "http://localhost:8000/api/conversation/random-prompt" -Method GET

## Get adjacent theme
Invoke-RestMethod -Uri "http://localhost:8000/api/conversation/adjacent" -Method GET

# 4. Stop the server
# Press Ctrl+C in the terminal where the server is running
# Or if using serve.bat, close the command window

# 5. Database commands
php artisan migrate              # Run migrations
php artisan migrate:status       # Check migration status
php artisan migrate:rollback     # Rollback last migration batch
php artisan migrate:fresh        # Drop all tables and re-run migrations

# 6. Development commands
npm run dev                      # Start Vite development server
npm run build                    # Build for production
php artisan optimize:clear       # Clear all caches
php artisan route:list           # List all routes

# 7. Check logs
Get-Content storage\logs\laravel.log -Tail 50
