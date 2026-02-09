<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Database: " . config('database.default') . "\n";
echo "Database path: " . database_path('database.sqlite') . "\n";
echo "File exists: " . (file_exists(database_path('database.sqlite')) ? 'YES' : 'NO') . "\n\n";

try {
    // Check if quotes table exists
    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='quotes'");
    if (count($tables) > 0) {
        echo "✓ Quotes table EXISTS\n";
        
        // Count quotes
        $count = DB::table('quotes')->count();
        echo "✓ Total quotes: {$count}\n";
        
        // Show sample
        $quotes = DB::table('quotes')->limit(3)->get();
        if ($quotes->count() > 0) {
            echo "\nSample quotes:\n";
            foreach ($quotes as $quote) {
                echo "  - " . substr($quote->body, 0, 60) . "...\n";
            }
        } else {
            echo "\n! No quotes found in database\n";
        }
    } else {
        echo "✗ Quotes table DOES NOT EXIST\n";
        echo "Run: php artisan migrate\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
