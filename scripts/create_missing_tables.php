<?php
$dbFile = __DIR__ . '/../database/database.sqlite';
if (! file_exists($dbFile)) {
    echo "No database file\n";
    exit(1);
}
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE TABLE IF NOT EXISTS quotes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT UNIQUE,
    body TEXT,
    path TEXT,
    visibility TEXT DEFAULT 'private'
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS daily_thoughts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date TEXT,
    body TEXT,
    path TEXT,
    visibility TEXT DEFAULT 'private'
);");

echo "Ensured quotes and daily_thoughts tables exist\n";
