<?php
require __DIR__ . '/../config/database.php';
try {
    $pdo = db();
    $rows = $pdo->query("SELECT id, id_pengguna, no_faktur, id_produk, id_pelanggan, jenis, alasan, status_pembayaran, jumlah, harga_satuan, harga_modal, tanggal_mutasi, dibayar_pada FROM mutasi_stok")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
