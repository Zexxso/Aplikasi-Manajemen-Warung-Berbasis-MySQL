<?php
require __DIR__ . '/../config/database.php';
try {
    $pdo = db();
    $rows = $pdo->query("SELECT id, id_pengguna, id_kategori_kas, id_mutasi_stok, id_pelanggan, jenis, nominal, tanggal_transaksi, catatan FROM catatan_kas")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
