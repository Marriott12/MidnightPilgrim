@echo off
echo ==================================
echo Midnight Pilgrim - Quote Fix
echo ==================================
echo.

echo Step 1: Setting PHP 8.2...
SET PATH=C:\wamp64\bin\php\php8.2.26;%PATH%
php -v
echo.

echo Step 2: Clearing Composer cache...
composer clear-cache
echo.

echo Step 3: Regenerating autoloader...
composer dump-autoload
echo.

echo Step 4: Running migrations...
php artisan migrate --force
echo.

echo Step 5: Testing quotes...
php test_quotes.php
echo.

echo Done!
pause
