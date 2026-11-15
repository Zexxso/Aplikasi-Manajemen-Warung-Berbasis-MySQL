<?php
require __DIR__ . '/../config/database.php';
try {
    $pdo = db();
    $targetTables = ['pengguna', 'produk', 'mutasi_stok', 'catatan_kas', 'pelanggan', 'kategori', 'kategori_kas', 'pembeli'];
    foreach ($targetTables as $tbl) {
        echo "=== TABLE: $tbl ===\n";
        $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "  - {$c['Field']} ({$c['Type']})\n";
        }
        $count = $pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
        echo "  TOTAL ROWS: $count\n\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
