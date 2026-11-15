<?php

require_once __DIR__ . '/../helpers/helpers.php';

$pdo = db();

echo "Running migration: add gambar column to produk table...\n";

$cols = $pdo->query("SHOW COLUMNS FROM produk")->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('gambar', $cols)) {
    $pdo->exec("ALTER TABLE produk ADD COLUMN gambar VARCHAR(255) NULL AFTER satuan");
    echo "Kolom 'gambar' berhasil ditambahkan ke tabel produk.\n";
} else {
    echo "Kolom 'gambar' sudah ada di tabel produk.\n";
}

// Ensure upload directory exists
$uploadDir = __DIR__ . '/../uploads/products';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
    echo "Upload directory created at: {$uploadDir}\n";
} else {
    echo "Upload directory already exists.\n";
}

echo "Migration finished.\n";
