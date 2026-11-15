<?php

return [
    'name' => '003_create_detail_pesanan_table',
    'up' => function (PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS detail_pesanan (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                id_pesanan BIGINT UNSIGNED NOT NULL,
                id_produk BIGINT UNSIGNED NOT NULL,
                jumlah INT NOT NULL,
                harga_satuan DECIMAL(15,2) NOT NULL,
                harga_modal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                subtotal DECIMAL(15,2) NOT NULL,
                dihapus_pada DATETIME NULL,
                dibuat_pada DATETIME NULL,
                diperbarui_pada DATETIME NULL,
                INDEX idx_dp_pesanan (id_pesanan),
                INDEX idx_dp_produk (id_produk),
                INDEX idx_dp_dihapus (dihapus_pada)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $pdo->exec("CREATE OR REPLACE VIEW muat AS SELECT * FROM detail_pesanan;");
    },
    'down' => function (PDO $pdo) {
        $pdo->exec("DROP VIEW IF EXISTS muat;");
        $pdo->exec("DROP TABLE IF EXISTS detail_pesanan;");
    }
];
