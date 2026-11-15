<?php

return [
    'name' => '001_create_pemasok_table',
    'up' => function (PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS pemasok (
                id_pemasok BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nama_pemasok VARCHAR(255) NOT NULL,
                alamat_pemasok TEXT NULL,
                no_hp_pemasok VARCHAR(20) NULL,
                email_pemasok VARCHAR(255) NULL,
                dihapus_pada DATETIME NULL,
                dibuat_pada DATETIME NULL,
                diperbarui_pada DATETIME NULL,
                INDEX idx_pemasok_nama (nama_pemasok),
                INDEX idx_pemasok_dihapus (dihapus_pada)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    },
    'down' => function (PDO $pdo) {
        $pdo->exec("DROP TABLE IF EXISTS pemasok;");
    }
];
