<?php

return [
    'name' => '006_add_id_pesanan_to_mutasi_and_catatan_kas',
    'up' => function (PDO $pdo) {
        $cols = $pdo->query("SHOW COLUMNS FROM mutasi_stok LIKE 'id_pesanan'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE mutasi_stok ADD COLUMN id_pesanan BIGINT UNSIGNED NULL AFTER id_pelanggan;");
            $pdo->exec("ALTER TABLE mutasi_stok ADD INDEX idx_mutasi_id_pesanan (id_pesanan);");
        }
        $cols2 = $pdo->query("SHOW COLUMNS FROM catatan_kas LIKE 'id_pesanan'")->fetchAll();
        if (empty($cols2)) {
            $pdo->exec("ALTER TABLE catatan_kas ADD COLUMN id_pesanan BIGINT UNSIGNED NULL AFTER id_mutasi_stok;");
            $pdo->exec("ALTER TABLE catatan_kas ADD INDEX idx_kas_id_pesanan (id_pesanan);");
        }
    },
    'down' => function (PDO $pdo) {
        $cols = $pdo->query("SHOW COLUMNS FROM mutasi_stok LIKE 'id_pesanan'")->fetchAll();
        if (!empty($cols)) {
            $pdo->exec("ALTER TABLE mutasi_stok DROP COLUMN id_pesanan;");
        }
        $cols2 = $pdo->query("SHOW COLUMNS FROM catatan_kas LIKE 'id_pesanan'")->fetchAll();
        if (!empty($cols2)) {
            $pdo->exec("ALTER TABLE catatan_kas DROP COLUMN id_pesanan;");
        }
    }
];
