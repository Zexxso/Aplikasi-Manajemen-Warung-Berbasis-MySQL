<?php
require __DIR__ . '/../config/database.php';

try {
    $pdo = db();
    echo "=== BASELINE DATABASE 'warungku' ===\n";
    
    // Row count per table
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "Tabel $t: $count baris\n";
    }
    
    echo "\n--- KEUANGAN & STOK ---\n";
    $income = $pdo->query("SELECT SUM(nominal) FROM catatan_kas WHERE jenis = 'income' AND dihapus_pada IS NULL")->fetchColumn() ?: 0;
    $expense = $pdo->query("SELECT SUM(nominal) FROM catatan_kas WHERE jenis = 'expense' AND dihapus_pada IS NULL")->fetchColumn() ?: 0;
    echo "Total Pemasukan (catatan_kas aktif): Rp " . number_format($income, 0, ',', '.') . "\n";
    echo "Total Pengeluaran (catatan_kas aktif): Rp " . number_format($expense, 0, ',', '.') . "\n";
    
    $totalStock = $pdo->query("SELECT SUM(stok) FROM produk WHERE dihapus_pada IS NULL")->fetchColumn() ?: 0;
    echo "Total Stok Seluruh Produk Aktif: $totalStock\n";
    
    $totalMutasi = $pdo->query("SELECT COUNT(*) FROM mutasi_stok WHERE dihapus_pada IS NULL")->fetchColumn() ?: 0;
    $totalMutasiSale = $pdo->query("SELECT COUNT(*) FROM mutasi_stok WHERE jenis = 'out' AND alasan = 'sale' AND dihapus_pada IS NULL")->fetchColumn() ?: 0;
    $totalMutasiPurchase = $pdo->query("SELECT COUNT(*) FROM mutasi_stok WHERE jenis = 'in' AND alasan = 'purchase' AND dihapus_pada IS NULL")->fetchColumn() ?: 0;
    echo "Total Mutasi Stok Aktif: $totalMutasi (Penjualan: $totalMutasiSale, Kulakan/Purchase: $totalMutasiPurchase)\n";
    
    // Distinct invoice numbers
    $invoices = $pdo->query("SELECT COUNT(DISTINCT no_faktur) FROM mutasi_stok WHERE no_faktur IS NOT NULL AND dihapus_pada IS NULL")->fetchColumn() ?: 0;
    echo "Jumlah Faktur Penjualan Unik (no_faktur): $invoices\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
