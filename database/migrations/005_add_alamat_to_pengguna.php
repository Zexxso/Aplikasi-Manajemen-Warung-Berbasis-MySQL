<?php

return [
    'name' => '005_add_alamat_to_pengguna',
    'up' => function (PDO $pdo) {
        $cols = $pdo->query("SHOW COLUMNS FROM pengguna LIKE 'alamat'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE pengguna ADD COLUMN alamat TEXT NULL AFTER telepon;");
        }
    },
    'down' => function (PDO $pdo) {
        $cols = $pdo->query("SHOW COLUMNS FROM pengguna LIKE 'alamat'")->fetchAll();
        if (!empty($cols)) {
            $pdo->exec("ALTER TABLE pengguna DROP COLUMN alamat;");
        }
    }
];
