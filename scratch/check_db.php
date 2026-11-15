<?php
require __DIR__ . '/../config/database.php';
try {
    $pdo = db();
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "TABLES IN DATABASE:\n";
    foreach ($tables as $t) {
        echo "- $t\n";
    }
} catch (Exception $e) {
    echo "Connection error: " . $e->getMessage() . "\n";
}
