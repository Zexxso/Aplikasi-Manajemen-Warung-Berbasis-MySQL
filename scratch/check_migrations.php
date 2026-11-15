<?php
require __DIR__ . '/../config/database.php';
try {
    $pdo = db();
    $rows = $pdo->query('SELECT * FROM migrations')->fetchAll(PDO::FETCH_ASSOC);
    echo "MIGRATIONS TABLE IN DB:\n";
    foreach ($rows as $r) {
        echo "- " . $r['migration'] . " (batch " . $r['batch'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
