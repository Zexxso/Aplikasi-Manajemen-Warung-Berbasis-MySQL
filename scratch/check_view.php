<?php
require __DIR__ . '/../config/database.php';
try {
    $pdo = db();
    $row = $pdo->query("SHOW CREATE VIEW `pembeli`")->fetch(PDO::FETCH_ASSOC);
    print_r($row);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
