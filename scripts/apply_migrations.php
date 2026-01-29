<?php

$dbFile = __DIR__ . '/../database/database.sqlite';
if (! file_exists($dbFile)) {
    // ensure directory and file
    if (! is_dir(dirname($dbFile))) {
        mkdir(dirname($dbFile), 0777, true);
    }
    touch($dbFile);
}

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// create cache table
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS cache (
        key TEXT PRIMARY KEY,
        value TEXT,
        expiration INTEGER
    );"
);

// create sessions table
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS sessions (
        id TEXT PRIMARY KEY,
        payload TEXT,
        last_activity INTEGER
    );"
);

function hasColumn(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->query("PRAGMA table_info('" . $table . "')");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        if (isset($c['name']) && $c['name'] === $column) {
            return true;
        }
    }
    return false;
}

$tables = [
    'notes' => "ALTER TABLE notes ADD COLUMN visibility TEXT DEFAULT 'private';",
    'quotes' => "ALTER TABLE quotes ADD COLUMN visibility TEXT DEFAULT 'private';",
    'daily_thoughts' => "ALTER TABLE daily_thoughts ADD COLUMN visibility TEXT DEFAULT 'private';",
];

foreach ($tables as $table => $sql) {
    // check table exists
    $res = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='" . $table . "'");
    $exists = (bool) $res->fetchColumn();
    if ($exists) {
        if (! hasColumn($pdo, $table, 'visibility')) {
            $pdo->exec($sql);
            echo "Added visibility to {$table}\n";
        } else {
            echo "{$table} already has visibility\n";
        }
    } else {
        echo "Table {$table} does not exist, skipping\n";
    }
}

echo "Done.\n";
