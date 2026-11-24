<?php

return [
    'name' => '011_create_notifikasi_table',
    'up' => function (PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `notifikasi` (
                `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_pengguna`     BIGINT UNSIGNED NULL,
                `kategori`        ENUM('stok','kasbon','keuangan','aktivitas') NOT NULL,
                `judul`           VARCHAR(100) NOT NULL,
                `pesan`           TEXT NOT NULL,
                `target_url`      VARCHAR(255) NULL,
                `tipe_referensi`  VARCHAR(50) NULL,
                `id_referensi`    BIGINT UNSIGNED NULL,
                `sudah_dibaca`    TINYINT(1) NOT NULL DEFAULT 0,
                `dibuat_pada`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_notif_pengguna_dibaca` (`id_pengguna`, `sudah_dibaca`),
                INDEX `idx_notif_ref` (`tipe_referensi`, `id_referensi`),
                INDEX `idx_notif_dibuat` (`dibuat_pada`),
                CONSTRAINT `fk_notif_pengguna`
                    FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna`(`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function (PDO $pdo) {
        $pdo->exec("DROP TABLE IF EXISTS `notifikasi`");
    },
];
