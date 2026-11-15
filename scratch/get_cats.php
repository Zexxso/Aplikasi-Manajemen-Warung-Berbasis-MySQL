<?php
require __DIR__ . '/../config/database.php';
try {
    $pdo = db();
    echo "=== KATEGORI ===\n";
    print_r($pdo->query("SELECT * FROM kategori")->fetchAll(PDO::FETCH_ASSOC));
    echo "=== KATEGORI KAS ===\n";
    print_r($pdo->query("SELECT * FROM kategori_kas")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
