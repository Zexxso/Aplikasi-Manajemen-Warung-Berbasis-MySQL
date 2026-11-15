<?php

return [
    'name' => '010_add_fk_pelanggan_to_mutasi_and_catatan_kas',
    'up' => function (PDO $pdo) {
        // Bersihkan data orphan bila ada
        $pdo->exec("UPDATE mutasi_stok SET id_pelanggan = NULL WHERE id_pelanggan IS NOT NULL AND id_pelanggan NOT IN (SELECT id FROM pelanggan)");
        $pdo->exec("UPDATE catatan_kas SET id_pelanggan = NULL WHERE id_pelanggan IS NOT NULL AND id_pelanggan NOT IN (SELECT id FROM pelanggan)");

        // Helper cek foreign key
        $hasFk = function ($table, $fkName) use ($pdo) {
            $stmt = $pdo->prepare("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
            ");
            $stmt->execute([$table, $fkName]);
            return (bool)$stmt->fetch();
        };

        // Tambah FK mutasi_stok -> pelanggan
        if (!$hasFk('mutasi_stok', 'fk_mutasi_pelanggan')) {
            $pdo->exec("
                ALTER TABLE mutasi_stok 
                ADD CONSTRAINT fk_mutasi_pelanggan 
                FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) 
                ON DELETE SET NULL 
                ON UPDATE CASCADE;
            ");
        }

        // Tambah FK catatan_kas -> pelanggan
        if (!$hasFk('catatan_kas', 'fk_kas_pelanggan')) {
            $pdo->exec("
                ALTER TABLE catatan_kas 
                ADD CONSTRAINT fk_kas_pelanggan 
                FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) 
                ON DELETE SET NULL 
                ON UPDATE CASCADE;
            ");
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

        $dropFk('mutasi_stok', 'fk_mutasi_pelanggan');
        $dropFk('catatan_kas', 'fk_kas_pelanggan');
    }
];
