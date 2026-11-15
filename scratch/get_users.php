<?php
require __DIR__ . '/../config/database.php';

try {
    $pdo = db();
    $users = $pdo->query("SELECT id, nama_toko, nama, email, telepon, peran, id_pemilik, status_aktif FROM pengguna")->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
