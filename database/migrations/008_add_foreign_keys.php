<?php

return [
    'name' => '008_add_foreign_keys',
    'up' => function (PDO $pdo) {
        // Pastikan tipe data foreign key selaras (BIGINT UNSIGNED) dengan tabel induk
        $pdo->exec("ALTER TABLE pesanan MODIFY COLUMN id_pesanan BIGINT UNSIGNED AUTO_INCREMENT");
        $pdo->exec("ALTER TABLE pesanan MODIFY COLUMN id_pelanggan BIGINT UNSIGNED NULL");
        $pdo->exec("ALTER TABLE pesanan MODIFY COLUMN id_pengguna BIGINT UNSIGNED NOT NULL");

        $pdo->exec("ALTER TABLE pemasok MODIFY COLUMN id_pemasok BIGINT UNSIGNED AUTO_INCREMENT");

        $pdo->exec("ALTER TABLE suplai MODIFY COLUMN id_suplai BIGINT UNSIGNED AUTO_INCREMENT");
        $pdo->exec("ALTER TABLE suplai MODIFY COLUMN id_pemasok BIGINT UNSIGNED NOT NULL");
        $pdo->exec("ALTER TABLE suplai MODIFY COLUMN id_produk BIGINT UNSIGNED NOT NULL");
        $pdo->exec("ALTER TABLE suplai MODIFY COLUMN id_pengguna BIGINT UNSIGNED NOT NULL");
        $pdo->exec("ALTER TABLE suplai MODIFY COLUMN id_mutasi_stok BIGINT UNSIGNED NULL");

        $pdo->exec("ALTER TABLE detail_pesanan MODIFY COLUMN id BIGINT UNSIGNED AUTO_INCREMENT");
        $pdo->exec("ALTER TABLE detail_pesanan MODIFY COLUMN id_pesanan BIGINT UNSIGNED NOT NULL");
        $pdo->exec("ALTER TABLE detail_pesanan MODIFY COLUMN id_produk BIGINT UNSIGNED NOT NULL");

        $pdo->exec("ALTER TABLE mutasi_stok MODIFY COLUMN id_pesanan BIGINT UNSIGNED NULL");
        $pdo->exec("ALTER TABLE catatan_kas MODIFY COLUMN id_pesanan BIGINT UNSIGNED NULL");

        // Pembersihan orphan record (bila ada)
        $pdo->exec("UPDATE pesanan SET id_pelanggan = NULL WHERE id_pelanggan IS NOT NULL AND id_pelanggan NOT IN (SELECT id FROM pelanggan)");
        $pdo->exec("UPDATE mutasi_stok SET id_pelanggan = NULL WHERE id_pelanggan IS NOT NULL AND id_pelanggan NOT IN (SELECT id FROM pelanggan)");
        $pdo->exec("UPDATE catatan_kas SET id_pelanggan = NULL WHERE id_pelanggan IS NOT NULL AND id_pelanggan NOT IN (SELECT id FROM pelanggan)");

        // Helper untuk cek foreign key sebelum ADD
        $hasFk = function ($table, $fkName) use ($pdo) {
            $stmt = $pdo->prepare("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
            ");
            $stmt->execute([$table, $fkName]);
            return (bool)$stmt->fetch();
        };

        // FK pesanan -> pelanggan
        if (!$hasFk('pesanan', 'fk_pesanan_pelanggan')) {
            $pdo->exec("ALTER TABLE pesanan ADD CONSTRAINT fk_pesanan_pelanggan FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) ON DELETE SET NULL ON UPDATE CASCADE;");
        }
        // FK pesanan -> pengguna
        if (!$hasFk('pesanan', 'fk_pesanan_pengguna')) {
            $pdo->exec("ALTER TABLE pesanan ADD CONSTRAINT fk_pesanan_pengguna FOREIGN KEY (id_pengguna) REFERENCES pengguna(id) ON UPDATE CASCADE;");
        }
        // FK detail_pesanan -> pesanan
        if (!$hasFk('detail_pesanan', 'fk_dp_pesanan')) {
            $pdo->exec("ALTER TABLE detail_pesanan ADD CONSTRAINT fk_dp_pesanan FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE ON UPDATE CASCADE;");
        }
        // FK detail_pesanan -> produk
        if (!$hasFk('detail_pesanan', 'fk_dp_produk')) {
            $pdo->exec("ALTER TABLE detail_pesanan ADD CONSTRAINT fk_dp_produk FOREIGN KEY (id_produk) REFERENCES produk(id) ON UPDATE CASCADE;");
        }
        // FK suplai -> pemasok
        if (!$hasFk('suplai', 'fk_suplai_pemasok')) {
            $pdo->exec("ALTER TABLE suplai ADD CONSTRAINT fk_suplai_pemasok FOREIGN KEY (id_pemasok) REFERENCES pemasok(id_pemasok) ON UPDATE CASCADE;");
        }
        // FK suplai -> produk
        if (!$hasFk('suplai', 'fk_suplai_produk')) {
            $pdo->exec("ALTER TABLE suplai ADD CONSTRAINT fk_suplai_produk FOREIGN KEY (id_produk) REFERENCES produk(id) ON UPDATE CASCADE;");
        }
        // FK suplai -> pengguna
        if (!$hasFk('suplai', 'fk_suplai_pengguna')) {
            $pdo->exec("ALTER TABLE suplai ADD CONSTRAINT fk_suplai_pengguna FOREIGN KEY (id_pengguna) REFERENCES pengguna(id) ON UPDATE CASCADE;");
        }
        // FK mutasi_stok -> pesanan
        if (!$hasFk('mutasi_stok', 'fk_mutasi_pesanan')) {
            $pdo->exec("ALTER TABLE mutasi_stok ADD CONSTRAINT fk_mutasi_pesanan FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE SET NULL ON UPDATE CASCADE;");
        }
        // FK catatan_kas -> pesanan
        if (!$hasFk('catatan_kas', 'fk_kas_pesanan')) {
            $pdo->exec("ALTER TABLE catatan_kas ADD CONSTRAINT fk_kas_pesanan FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE SET NULL ON UPDATE CASCADE;");
        }
    },
    'down' => function (PDO $pdo) {
        $dropFk = function ($table, $fkName) use ($pdo) {
            $stmt = $pdo->prepare("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
            ");
            $stmt->execute([$table, $fkName]);
            if ($stmt->fetch()) {
                $pdo->exec("ALTER TABLE `$table` DROP FOREIGN KEY `$fkName`");
            }
        };

        $dropFk('pesanan', 'fk_pesanan_pelanggan');
        $dropFk('pesanan', 'fk_pesanan_pengguna');
        $dropFk('detail_pesanan', 'fk_dp_pesanan');
        $dropFk('detail_pesanan', 'fk_dp_produk');
        $dropFk('suplai', 'fk_suplai_pemasok');
        $dropFk('suplai', 'fk_suplai_produk');
        $dropFk('suplai', 'fk_suplai_pengguna');
        $dropFk('mutasi_stok', 'fk_mutasi_pesanan');
        $dropFk('catatan_kas', 'fk_kas_pesanan');
    }
];
