<?php

return [
    'name' => '004_create_suplai_table',
    'up' => function (PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS suplai (
                id_suplai BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                id_pemasok BIGINT UNSIGNED NOT NULL,
                id_produk BIGINT UNSIGNED NOT NULL,
                id_pengguna BIGINT UNSIGNED NOT NULL,
                id_mutasi_stok BIGINT UNSIGNED NULL,
                tanggal_suplai DATETIME NOT NULL,
                jumlah INT NOT NULL,
                harga_beli DECIMAL(15,2) NOT NULL,
                total_harga DECIMAL(15,2) NOT NULL,
                catatan TEXT NULL,
                dihapus_pada DATETIME NULL,
                dibuat_pada DATETIME NULL,
                diperbarui_pada DATETIME NULL,
                INDEX idx_suplai_pemasok (id_pemasok),
                INDEX idx_suplai_produk (id_produk),
                INDEX idx_suplai_pengguna (id_pengguna),
                INDEX idx_suplai_tanggal (tanggal_suplai),
                INDEX idx_suplai_dihapus (dihapus_pada)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    },
    'down' => function (PDO $pdo) {
        $pdo->exec("DROP TABLE IF EXISTS suplai;");
    }
];
