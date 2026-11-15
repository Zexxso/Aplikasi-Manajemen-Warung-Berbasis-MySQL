<?php

require_once __DIR__ . '/database.php';

try {
    $pdo = db();

    echo "Memulai migrasi tabel pelanggan & pembeli...\n";

    // 1. Create pelanggan table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `pelanggan` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_pengguna` BIGINT UNSIGNED NOT NULL,
            `kode` VARCHAR(50) NOT NULL,
            `nama` VARCHAR(150) NOT NULL,
            `telepon` VARCHAR(30) NULL,
            `alamat` TEXT NULL,
            `catatan` TEXT NULL,
            `dibuat_pada` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `diperbarui_pada` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `dihapus_pada` TIMESTAMP NULL,
            INDEX `idx_pelanggan_id_pengguna` (`id_pengguna`),
            INDEX `idx_pelanggan_kode` (`kode`),
            INDEX `idx_pelanggan_nama` (`nama`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabel 'pelanggan' berhasil dibuat / diverifikasi.\n";

    // 2. Create pembeli view (or alias table)
    $pdo->exec("CREATE OR REPLACE VIEW `pembeli` AS SELECT * FROM `pelanggan`;");
    echo "✓ View 'pembeli' berhasil dibuat sebagai alias tabel 'pelanggan'.\n";

    // 3. Add id_pelanggan to mutasi_stok if not exists
    $smCols = $pdo->query("SHOW COLUMNS FROM `mutasi_stok` LIKE 'id_pelanggan'")->fetchAll();
    if (empty($smCols)) {
        $pdo->exec("ALTER TABLE `mutasi_stok` ADD COLUMN `id_pelanggan` BIGINT UNSIGNED NULL AFTER `id_produk`");
        $pdo->exec("ALTER TABLE `mutasi_stok` ADD INDEX `idx_ms_id_pelanggan` (`id_pelanggan`)");
        echo "✓ Kolom 'id_pelanggan' berhasil ditambahkan ke 'mutasi_stok'.\n";
    } else {
        echo "✓ Kolom 'id_pelanggan' sudah ada di 'mutasi_stok'.\n";
    }

    // 4. Add id_pelanggan to catatan_kas if not exists
    $ceCols = $pdo->query("SHOW COLUMNS FROM `catatan_kas` LIKE 'id_pelanggan'")->fetchAll();
    if (empty($ceCols)) {
        $pdo->exec("ALTER TABLE `catatan_kas` ADD COLUMN `id_pelanggan` BIGINT UNSIGNED NULL AFTER `id_mutasi_stok`");
        $pdo->exec("ALTER TABLE `catatan_kas` ADD INDEX `idx_ck_id_pelanggan` (`id_pelanggan`)");
        echo "✓ Kolom 'id_pelanggan' berhasil ditambahkan ke 'catatan_kas'.\n";
    } else {
        echo "✓ Kolom 'id_pelanggan' sudah ada di 'catatan_kas'.\n";
    }

    echo "Migrasi berhasil selesai!\n";
} catch (Exception $e) {
    echo "Gagal migrasi: " . $e->getMessage() . "\n";
    exit(1);
}
