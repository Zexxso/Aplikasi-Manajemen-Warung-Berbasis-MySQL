<?php
require __DIR__ . '/../config/database.php';
try {
    $pdo = db();
    $rows = $pdo->query("SELECT * FROM pelanggan")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
