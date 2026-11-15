<?php

require_once __DIR__ . '/database.php';

try {
    $pdo = db();
    echo "Memulai migrasi status_pembayaran, no_faktur, dan dibayar_pada...\n";

    // 1. Add status_pembayaran to mutasi_stok
    $chk = $pdo->query("SHOW COLUMNS FROM mutasi_stok LIKE 'status_pembayaran'")->fetchAll();
    if (empty($chk)) {
        $pdo->exec("ALTER TABLE mutasi_stok ADD COLUMN status_pembayaran VARCHAR(20) NOT NULL DEFAULT 'paid' AFTER alasan");
        $pdo->exec("ALTER TABLE mutasi_stok ADD INDEX idx_sm_status_pembayaran (status_pembayaran)");
        echo "✓ Kolom status_pembayaran berhasil ditambahkan ke mutasi_stok.\n";
    }

    // 2. Add no_faktur to mutasi_stok
    $chk = $pdo->query("SHOW COLUMNS FROM mutasi_stok LIKE 'no_faktur'")->fetchAll();
    if (empty($chk)) {
        $pdo->exec("ALTER TABLE mutasi_stok ADD COLUMN no_faktur VARCHAR(50) NULL AFTER id_pengguna");
        $pdo->exec("ALTER TABLE mutasi_stok ADD INDEX idx_sm_no_faktur (no_faktur)");
        echo "✓ Kolom no_faktur berhasil ditambahkan ke mutasi_stok.\n";
    }

    // 3. Add dibayar_pada to mutasi_stok
    $chk = $pdo->query("SHOW COLUMNS FROM mutasi_stok LIKE 'dibayar_pada'")->fetchAll();
    if (empty($chk)) {
        $pdo->exec("ALTER TABLE mutasi_stok ADD COLUMN dibayar_pada DATETIME NULL AFTER tanggal_mutasi");
        echo "✓ Kolom dibayar_pada berhasil ditambahkan ke mutasi_stok.\n";
    }

    // 4. Populate no_faktur for existing mutasi_stok
    $pdo->exec("
        UPDATE mutasi_stok 
        SET no_faktur = CONCAT('TRX-', DATE_FORMAT(COALESCE(tanggal_mutasi, dibuat_pada), '%y%m%d'), '-', LPAD(id, 4, '0'))
        WHERE no_faktur IS NULL OR no_faktur = ''
    ");
    echo "✓ Nomor faktur untuk transaksi eksisting berhasil di-generate.\n";

    // 5. Clean up dummy anonymous customers ('Pelanggan CUST-%') from pelanggan table
    $dummyIds = $pdo->query("SELECT id FROM pelanggan WHERE nama LIKE 'Pelanggan CUST-%'")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($dummyIds)) {
        $placeholders = implode(',', array_map('intval', $dummyIds));
        // Disconnect dummy id_pelanggan from mutasi_stok and catatan_kas
        $pdo->exec("UPDATE mutasi_stok SET id_pelanggan = NULL WHERE id_pelanggan IN ({$placeholders})");
        $pdo->exec("UPDATE catatan_kas SET id_pelanggan = NULL WHERE id_pelanggan IN ({$placeholders})");
        $pdo->exec("DELETE FROM pelanggan WHERE id IN ({$placeholders})");
        echo "✓ Berhasil membersihkan " . count($dummyIds) . " data dummy pelanggan anonim. Pembeli anonim kini murni menggunakan ID Transaksi / No Struk.\n";
    }

    echo "Migrasi berhasil selesai!\n";
} catch (Exception $e) {
    echo "Gagal: " . $e->getMessage() . "\n";
    exit(1);
}
