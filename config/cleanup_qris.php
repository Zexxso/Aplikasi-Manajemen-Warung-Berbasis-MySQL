<?php

/**
 * Script Penghapusan Kolom & Skema QRIS dari Database
 * Warungku Versi Native
 */

require_once __DIR__ . '/database.php';

try {
    $pdo = db();
    echo "============================================================\n";
    echo "       PEMBERSIHAN SKEMA FITUR QRIS & MIDTRANS              \n";
    echo "============================================================\n\n";

    $columnExists = function (string $tableName, string $columnName) use ($pdo): bool {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$tableName}` LIKE :column");
        $stmt->execute(['column' => $columnName]);
        return (bool) $stmt->fetch();
    };

    // 1. Drop index & kolom qr_string
    if ($columnExists('mutasi_stok', 'qr_string')) {
        $pdo->exec("ALTER TABLE `mutasi_stok` DROP COLUMN `qr_string`");
        echo "✓ Kolom `qr_string` berhasil dihapus dari `mutasi_stok`.\n";
    }

    // 2. Drop index & kolom id_transaksi_gateway
    if ($columnExists('mutasi_stok', 'id_transaksi_gateway')) {
        try {
            $pdo->exec("ALTER TABLE `mutasi_stok` DROP INDEX `idx_sm_gateway_tx`");
        } catch (\Throwable $e) {}
        try {
            $pdo->exec("ALTER TABLE `mutasi_stok` DROP INDEX `idx_ms_gateway_tx`");
        } catch (\Throwable $e) {}
        $pdo->exec("ALTER TABLE `mutasi_stok` DROP COLUMN `id_transaksi_gateway`");
        echo "✓ Kolom `id_transaksi_gateway` berhasil dihapus dari `mutasi_stok`.\n";
    }

    // 3. Drop index & kolom metode_pembayaran
    if ($columnExists('mutasi_stok', 'metode_pembayaran')) {
        try {
            $pdo->exec("ALTER TABLE `mutasi_stok` DROP INDEX `idx_sm_metode_pembayaran`");
        } catch (\Throwable $e) {}
        try {
            $pdo->exec("ALTER TABLE `mutasi_stok` DROP INDEX `idx_ms_metode_pembayaran`");
        } catch (\Throwable $e) {}
        $pdo->exec("ALTER TABLE `mutasi_stok` DROP COLUMN `metode_pembayaran`");
        echo "✓ Kolom `metode_pembayaran` berhasil dihapus dari `mutasi_stok`.\n";
    }

    echo "\nSemua kolom database yang berkaitan dengan QRIS berhasil dihapus!\n";
    echo "Transaksi penjualan kini murni menggunakan sistem Tunai (dan Kasbon jika diaktifkan).\n";
} catch (\Throwable $e) {
    echo "Error saat membersihkan database: " . $e->getMessage() . "\n";
}
