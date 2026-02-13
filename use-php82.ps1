# Use PHP 8.2 for Midnight Pilgrim
# Run this script in your terminal: . .\use-php82.ps1

$env:Path = "C:\wamp64\bin\php\php8.2.26;$env:Path"
Write-Host "PHP 8.2.26 activated for this session" -ForegroundColor Green
php -v
