<?php

return [
    'name' => '002_create_pesanan_table',
    'up' => function (PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS pesanan (
                id_pesanan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                no_faktur VARCHAR(50) NOT NULL UNIQUE,
                id_pelanggan BIGINT UNSIGNED NULL,
                id_pengguna BIGINT UNSIGNED NOT NULL,
                tanggal_pesanan DATETIME NOT NULL,
                total_harga DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                jumlah_bayar DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                kembalian DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                metode_pembayaran VARCHAR(20) NOT NULL DEFAULT 'cash',
                status_pembayaran VARCHAR(20) NOT NULL DEFAULT 'paid',
                catatan TEXT NULL,
                dihapus_pada DATETIME NULL,
                dibuat_pada DATETIME NULL,
                diperbarui_pada DATETIME NULL,
                INDEX idx_pesanan_faktur (no_faktur),
                INDEX idx_pesanan_pelanggan (id_pelanggan),
                INDEX idx_pesanan_pengguna (id_pengguna),
                INDEX idx_pesanan_tanggal (tanggal_pesanan),
                INDEX idx_pesanan_status (status_pembayaran),
                INDEX idx_pesanan_dihapus (dihapus_pada)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    },
    'down' => function (PDO $pdo) {
        $pdo->exec("DROP TABLE IF EXISTS pesanan;");
    }
];
