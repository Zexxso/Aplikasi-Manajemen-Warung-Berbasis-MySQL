<?php

return [
    'name' => '009_drop_unused_laravel_tables_and_columns',
    'up' => function (PDO $pdo) {
        // 1. Drop unused VIEW pembeli (legacy duplicate of pelanggan)
        $pdo->exec("DROP VIEW IF EXISTS `pembeli`");

        // 2. Drop unused Laravel tables (cache, queues, sessions, legacy migrations, password reset)
        $unusedTables = [
            'cache_locks',
            'cache',
            'failed_jobs',
            'job_batches',
            'jobs',
            'sesi',
            'token_reset_kata_sandi',
            'migrations',
        ];
        foreach ($unusedTables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        }

        // Helper to check if constraint exists
        $hasFk = function (string $table, string $fkName) use ($pdo): bool {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
                WHERE CONSTRAINT_SCHEMA = DATABASE() 
                  AND TABLE_NAME = ? 
                  AND CONSTRAINT_NAME = ? 
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");
            $stmt->execute([$table, $fkName]);
            return ((int) $stmt->fetchColumn()) > 0;
        };

        // Helper to check if index exists
        $hasIndex = function (string $table, string $indexName) use ($pdo): bool {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = ? 
                  AND INDEX_NAME = ?
            ");
            $stmt->execute([$table, $indexName]);
            return ((int) $stmt->fetchColumn()) > 0;
        };

        // Helper to check if column exists
        $hasCol = function (string $table, string $col) use ($pdo): bool {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = ? 
                  AND COLUMN_NAME = ?
            ");
            $stmt->execute([$table, $col]);
            return ((int) $stmt->fetchColumn()) > 0;
        };

        // 3. Drop tenant columns from kategori
        if ($hasFk('kategori', 'kategori_id_pengguna_foreign')) {
            $pdo->exec("ALTER TABLE `kategori` DROP FOREIGN KEY `kategori_id_pengguna_foreign`");
        }
        if ($hasIndex('kategori', 'kategori_id_pengguna_nama_index')) {
            $pdo->exec("ALTER TABLE `kategori` DROP INDEX `kategori_id_pengguna_nama_index`");
        }
        if (!$hasIndex('kategori', 'idx_kategori_nama')) {
            $pdo->exec("ALTER TABLE `kategori` ADD INDEX `idx_kategori_nama` (`nama`)");
        }
        if ($hasCol('kategori', 'id_pengguna')) {
            $pdo->exec("ALTER TABLE `kategori` DROP COLUMN `id_pengguna`");
        }

        // 4. Drop tenant columns from kategori_kas
        if ($hasFk('kategori_kas', 'kategori_kas_id_pengguna_foreign')) {
            $pdo->exec("ALTER TABLE `kategori_kas` DROP FOREIGN KEY `kategori_kas_id_pengguna_foreign`");
        }
        if ($hasIndex('kategori_kas', 'kategori_kas_id_pengguna_jenis_index')) {
            $pdo->exec("ALTER TABLE `kategori_kas` DROP INDEX `kategori_kas_id_pengguna_jenis_index`");
        }
        if (!$hasIndex('kategori_kas', 'idx_kategori_kas_jenis')) {
            $pdo->exec("ALTER TABLE `kategori_kas` ADD INDEX `idx_kategori_kas_jenis` (`jenis`)");
        }
        if ($hasCol('kategori_kas', 'id_pengguna')) {
            $pdo->exec("ALTER TABLE `kategori_kas` DROP COLUMN `id_pengguna`");
        }

        // 5. Drop tenant columns from produk
        if ($hasFk('produk', 'produk_id_pengguna_foreign')) {
            $pdo->exec("ALTER TABLE `produk` DROP FOREIGN KEY `produk_id_pengguna_foreign`");
        }
        if ($hasIndex('produk', 'produk_id_pengguna_nama_index')) {
            $pdo->exec("ALTER TABLE `produk` DROP INDEX `produk_id_pengguna_nama_index`");
        }
        if ($hasIndex('produk', 'produk_id_pengguna_stok_index')) {
            $pdo->exec("ALTER TABLE `produk` DROP INDEX `produk_id_pengguna_stok_index`");
        }
        if (!$hasIndex('produk', 'idx_produk_nama')) {
            $pdo->exec("ALTER TABLE `produk` ADD INDEX `idx_produk_nama` (`nama`)");
        }
        if (!$hasIndex('produk', 'idx_produk_stok')) {
            $pdo->exec("ALTER TABLE `produk` ADD INDEX `idx_produk_stok` (`stok`)");
        }
        if ($hasCol('produk', 'id_pengguna')) {
            $pdo->exec("ALTER TABLE `produk` DROP COLUMN `id_pengguna`");
        }

        // 6. Drop tenant columns from pelanggan
        if ($hasIndex('pelanggan', 'idx_pelanggan_id_pengguna')) {
            $pdo->exec("ALTER TABLE `pelanggan` DROP INDEX `idx_pelanggan_id_pengguna`");
        }
        if ($hasCol('pelanggan', 'id_pengguna')) {
            $pdo->exec("ALTER TABLE `pelanggan` DROP COLUMN `id_pengguna`");
        }

        // 7. Drop tenant & legacy columns from pengguna
        if ($hasFk('pengguna', 'pengguna_id_pemilik_foreign')) {
            $pdo->exec("ALTER TABLE `pengguna` DROP FOREIGN KEY `pengguna_id_pemilik_foreign`");
        }
        if ($hasIndex('pengguna', 'pengguna_id_pemilik_foreign')) {
            $pdo->exec("ALTER TABLE `pengguna` DROP INDEX `pengguna_id_pemilik_foreign`");
        }
        if ($hasCol('pengguna', 'id_pemilik')) {
            $pdo->exec("ALTER TABLE `pengguna` DROP COLUMN `id_pemilik`");
        }
        if ($hasCol('pengguna', 'nama_toko')) {
            $pdo->exec("ALTER TABLE `pengguna` DROP COLUMN `nama_toko`");
        }
        if ($hasCol('pengguna', 'token_ingat')) {
            $pdo->exec("ALTER TABLE `pengguna` DROP COLUMN `token_ingat`");
        }
        if ($hasCol('pengguna', 'email_diverifikasi_pada')) {
            $pdo->exec("ALTER TABLE `pengguna` DROP COLUMN `email_diverifikasi_pada`");
        }
    },
    'down' => function (PDO $pdo) {
        // Recreate columns in reverse
        $pdo->exec("ALTER TABLE `pengguna` ADD COLUMN `nama_toko` VARCHAR(120) NULL AFTER `id`");
        $pdo->exec("ALTER TABLE `pengguna` ADD COLUMN `email_diverifikasi_pada` TIMESTAMP NULL AFTER `alamat`");
        $pdo->exec("ALTER TABLE `pengguna` ADD COLUMN `id_pemilik` BIGINT UNSIGNED NULL AFTER `peran`");
        $pdo->exec("ALTER TABLE `pengguna` ADD COLUMN `token_ingat` VARCHAR(100) NULL AFTER `status_aktif`");
        $pdo->exec("ALTER TABLE `pengguna` ADD CONSTRAINT `pengguna_id_pemilik_foreign` FOREIGN KEY (`id_pemilik`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL");

        $pdo->exec("ALTER TABLE `pelanggan` ADD COLUMN `id_pengguna` BIGINT UNSIGNED NULL DEFAULT 1 AFTER `id`");
        $pdo->exec("ALTER TABLE `pelanggan` ADD INDEX `idx_pelanggan_id_pengguna` (`id_pengguna`)");

        $pdo->exec("ALTER TABLE `produk` ADD COLUMN `id_pengguna` BIGINT UNSIGNED NULL DEFAULT 1 AFTER `id`");
        $pdo->exec("ALTER TABLE `produk` ADD INDEX `produk_id_pengguna_nama_index` (`id_pengguna`, `nama`)");
        $pdo->exec("ALTER TABLE `produk` ADD INDEX `produk_id_pengguna_stok_index` (`id_pengguna`, `stok`)");
        $pdo->exec("ALTER TABLE `produk` ADD CONSTRAINT `produk_id_pengguna_foreign` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE");

        $pdo->exec("ALTER TABLE `kategori_kas` ADD COLUMN `id_pengguna` BIGINT UNSIGNED NULL AFTER `id`");
        $pdo->exec("ALTER TABLE `kategori_kas` ADD INDEX `kategori_kas_id_pengguna_jenis_index` (`id_pengguna`, `jenis`)");
        $pdo->exec("ALTER TABLE `kategori_kas` ADD CONSTRAINT `kategori_kas_id_pengguna_foreign` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE");

        $pdo->exec("ALTER TABLE `kategori` ADD COLUMN `id_pengguna` BIGINT UNSIGNED NULL DEFAULT 1 AFTER `id`");
        $pdo->exec("ALTER TABLE `kategori` ADD INDEX `kategori_id_pengguna_nama_index` (`id_pengguna`, `nama`)");
        $pdo->exec("ALTER TABLE `kategori` ADD CONSTRAINT `kategori_id_pengguna_foreign` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE");

        // Recreate VIEW pembeli
        $pdo->exec("CREATE OR REPLACE VIEW `pembeli` AS SELECT * FROM `pelanggan`");
    }
];
