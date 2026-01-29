<?php
$dbFile = __DIR__ . '/../database/database.sqlite';
if (! file_exists($dbFile)) {
    echo "No database file\n";
    exit(1);
}
$pdo = new PDO('sqlite:' . $dbFile);
$stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['name'] . "\n";
}
