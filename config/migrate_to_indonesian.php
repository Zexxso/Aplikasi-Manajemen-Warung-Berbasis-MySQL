<?php

/**
 * Script Migrasi Skema Database Warungku: Bahasa Inggris -> Bahasa Indonesia
 * 
 * Jalankan file ini melalui CLI:
 * php config/migrate_to_indonesian.php
 */

require_once __DIR__ . '/database.php';

try {
    $pdo = db();
    echo "============================================================\n";
    echo "  MIGRASI SKEMA DATABASE KE BAHASA INDONESIA (WARUNGKU)  \n";
    echo "============================================================\n\n";

    // Helper untuk mengecek keberadaan tabel
    $tableExists = function(string $tableName) use ($pdo): bool {
        $stmt = $pdo->prepare("SHOW TABLES LIKE :table");
        $stmt->execute(['table' => $tableName]);
        return (bool) $stmt->fetch();
    };

    // Helper untuk mengecek keberadaan kolom
    $columnExists = function(string $tableName, string $columnName) use ($pdo): bool {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$tableName}` LIKE :column");
        $stmt->execute(['column' => $columnName]);
        return (bool) $stmt->fetch();
    };

    // Helper rename kolom dengan aman
    $renameColumn = function(string $tableName, string $oldCol, string $newCol, string $colDef) use ($pdo, $columnExists): void {
        if ($columnExists($tableName, $oldCol) && !$columnExists($tableName, $newCol)) {
            $pdo->exec("ALTER TABLE `{$tableName}` CHANGE COLUMN `{$oldCol}` `{$newCol}` {$colDef}");
            echo "  ✓ Kolom `{$oldCol}` diubah menjadi `{$newCol}` pada `{$tableName}`.\n";
        }
    };

    // 0. Nonaktifkan pengecekan foreign key sementara agar rename mulus
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 1. RENAME TABEL JIKA MASIH MENGGUNAKAN NAMA LAMA
    $tableRenames = [
        'users' => 'pengguna',
        'categories' => 'kategori',
        'products' => 'produk',
        'customers' => 'pelanggan',
        'stock_movements' => 'mutasi_stok',
        'cash_categories' => 'kategori_kas',
        'cash_entries' => 'catatan_kas',
        'password_reset_tokens' => 'token_reset_kata_sandi',
        'sessions' => 'sesi',
    ];

    foreach ($tableRenames as $oldTable => $newTable) {
        if ($tableExists($oldTable) && !$tableExists($newTable)) {
            $pdo->exec("RENAME TABLE `{$oldTable}` TO `{$newTable}`");
            echo "✓ Tabel `{$oldTable}` berhasil di-rename menjadi `{$newTable}`.\n";
        }
    }

    // 2. MIGRASI KOLOM TABEL: pengguna
    if ($tableExists('pengguna')) {
        echo "\nMemeriksa kolom tabel `pengguna`...\n";
        $renameColumn('pengguna', 'shop_name', 'nama_toko', 'VARCHAR(255) NULL');
        $renameColumn('pengguna', 'name', 'nama', 'VARCHAR(255) NOT NULL');
        $renameColumn('pengguna', 'phone', 'telepon', 'VARCHAR(255) NULL');
        $renameColumn('pengguna', 'email_verified_at', 'email_diverifikasi_pada', 'TIMESTAMP NULL DEFAULT NULL');
        $renameColumn('pengguna', 'password', 'kata_sandi', 'VARCHAR(255) NOT NULL');
        $renameColumn('pengguna', 'role', 'peran', "VARCHAR(50) NOT NULL DEFAULT 'owner'");
        $renameColumn('pengguna', 'owner_id', 'id_pemilik', 'BIGINT UNSIGNED NULL');
        $renameColumn('pengguna', 'is_active', 'status_aktif', 'TINYINT(1) NOT NULL DEFAULT 1');
        $renameColumn('pengguna', 'remember_token', 'token_ingat', 'VARCHAR(100) NULL');
        $renameColumn('pengguna', 'created_at', 'dibuat_pada', 'TIMESTAMP NULL DEFAULT NULL');
        $renameColumn('pengguna', 'updated_at', 'diperbarui_pada', 'TIMESTAMP NULL DEFAULT NULL');
    }

    // 3. MIGRASI KOLOM TABEL: kategori
    if ($tableExists('kategori')) {
        echo "\nMemeriksa kolom tabel `kategori`...\n";
        $renameColumn('kategori', 'user_id', 'id_pengguna', 'BIGINT UNSIGNED NOT NULL');
        $renameColumn('kategori', 'name', 'nama', 'VARCHAR(255) NOT NULL');
        $renameColumn('kategori', 'created_at', 'dibuat_pada', 'TIMESTAMP NULL DEFAULT NULL');
        $renameColumn('kategori', 'updated_at', 'diperbarui_pada', 'TIMESTAMP NULL DEFAULT NULL');
    }

    // 4. MIGRASI KOLOM TABEL: produk
    if ($tableExists('produk')) {
        echo "\nMemeriksa kolom tabel `produk`...\n";
        $renameColumn('produk', 'user_id', 'id_pengguna', 'BIGINT UNSIGNED NOT NULL');
        $renameColumn('produk', 'category_id', 'id_kategori', 'BIGINT UNSIGNED NULL');
        $renameColumn('produk', 'name', 'nama', 'VARCHAR(255) NOT NULL');
        $renameColumn('produk', 'unit', 'satuan', "VARCHAR(50) NOT NULL DEFAULT 'pcs'");
        $renameColumn('produk', 'image', 'gambar', 'VARCHAR(255) NULL');
        $renameColumn('produk', 'buy_price', 'harga_beli', 'DECIMAL(12,2) NOT NULL DEFAULT 0.00');
        $renameColumn('produk', 'sell_price', 'harga_jual', 'DECIMAL(12,2) NOT NULL DEFAULT 0.00');
        $renameColumn('produk', 'stock', 'stok', 'INT NOT NULL DEFAULT 0');
        $renameColumn('produk', 'min_stock', 'stok_minimum', 'INT NOT NULL DEFAULT 5');
        $renameColumn('produk', 'is_active', 'status_aktif', 'TINYINT(1) NOT NULL DEFAULT 1');
        $renameColumn('produk', 'deleted_at', 'dihapus_pada', 'TIMESTAMP NULL DEFAULT NULL');
        $renameColumn('produk', 'created_at', 'dibuat_pada', 'TIMESTAMP NULL DEFAULT NULL');
        $renameColumn('produk', 'updated_at', 'diperbarui_pada', 'TIMESTAMP NULL DEFAULT NULL');
    }

    // 5. MIGRASI KOLOM TABEL: pelanggan
    if ($tableExists('pelanggan')) {
        echo "\nMemeriksa kolom tabel `pelanggan`...\n";
        $renameColumn('pelanggan', 'user_id', 'id_pengguna', 'BIGINT UNSIGNED NOT NULL');
        $renameColumn('pelanggan', 'code', 'kode', 'VARCHAR(50) NOT NULL');
        $renameColumn('pelanggan', 'name', 'nama', 'VARCHAR(150) NOT NULL');
        $renameColumn('pelanggan', 'phone', 'telepon', 'VARCHAR(30) NULL');
        $renameColumn('pelanggan', 'address', 'alamat', 'TEXT NULL');
        $renameColumn('pelanggan', 'note', 'catatan', 'TEXT NULL');
        $renameColumn('pelanggan', 'created_at', 'dibuat_pada', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        $renameColumn('pelanggan', 'updated_at', 'diperbarui_pada', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        $renameColumn('pelanggan', 'deleted_at', 'dihapus_pada', 'TIMESTAMP NULL DEFAULT NULL');
    }

    // 6. MIGRASI KOLOM TABEL: mutasi_stok
    if ($tableExists('mutasi_stok')) {
        echo "\nMemeriksa kolom tabel `mutasi_stok`...\n";
        $renameColumn('mutasi_stok', 'user_id', 'id_pengguna', 'BIGINT UNSIGNED NOT NULL');
        $renameColumn('mutasi_stok', 'customer_id', 'id_pelanggan', 'BIGINT UNSIGNED NULL');
        $renameColumn('mutasi_stok', 'invoice_no', 'no_faktur', 'VARCHAR(50) NULL');
        $renameColumn('mutasi_stok', 'product_id', 'id_produk', 'BIGINT UNSIGNED NOT NULL');
        $renameColumn('mutasi_stok', 'type', 'jenis', "ENUM('in','out','adjust') NOT NULL");
        $renameColumn('mutasi_stok', 'reason', 'alasan', "ENUM('purchase','sale','personal','damaged','adjustment') NOT NULL");
        $renameColumn('mutasi_stok', 'payment_status', 'status_pembayaran', "VARCHAR(20) NOT NULL DEFAULT 'paid'");
        $renameColumn('mutasi_stok', 'quantity', 'jumlah', 'INT NOT NULL');
        $renameColumn('mutasi_stok', 'unit_price', 'harga_satuan', 'DECIMAL(12,2) NOT NULL DEFAULT 0.00');
        $renameColumn('mutasi_stok', 'cost_price', 'harga_modal', 'DECIMAL(12,2) NOT NULL DEFAULT 0.00');
        $renameColumn('mutasi_stok', 'movement_date', 'tanggal_mutasi', 'DATE NOT NULL');
        $renameColumn('mutasi_stok', 'paid_at', 'dibayar_pada', 'DATETIME NULL DEFAULT NULL');
        $renameColumn('mutasi_stok', 'note', 'catatan', 'VARCHAR(255) NULL');
        $renameColumn('mutasi_stok', 'deleted_at', 'dihapus_pada', 'TIMESTAMP NULL DEFAULT NULL');
        $renameColumn('mutasi_stok', 'created_at', 'dibuat_pada', 'TIMESTAMP NULL DEFAULT NULL');
        $renameColumn('mutasi_stok', 'updated_at', 'diperbarui_pada', 'TIMESTAMP NULL DEFAULT NULL');

        // Hapus kolom QRIS jika sebelumnya sempat dibuat
        if ($columnExists('mutasi_stok', 'qr_string')) {
            $pdo->exec("ALTER TABLE `mutasi_stok` DROP COLUMN `qr_string`");
            echo "  ✓ Kolom `qr_string` dihapus dari `mutasi_stok`.\n";
        }
        if ($columnExists('mutasi_stok', 'id_transaksi_gateway')) {
            $pdo->exec("ALTER TABLE `mutasi_stok` DROP COLUMN `id_transaksi_gateway`");
            echo "  ✓ Kolom `id_transaksi_gateway` dihapus dari `mutasi_stok`.\n";
        }
        if ($columnExists('mutasi_stok', 'metode_pembayaran')) {
            $pdo->exec("ALTER TABLE `mutasi_stok` DROP COLUMN `metode_pembayaran`");
            echo "  ✓ Kolom `metode_pembayaran` dihapus dari `mutasi_stok`.\n";
        }
    }

    // 7. MIGRASI KOLOM TABEL: kategori_kas
    if ($tableExists('kategori_kas')) {
        echo "\nMemeriksa kolom tabel `kategori_kas`...\n";
        $renameColumn('kategori_kas', 'user_id', 'id_pengguna', 'BIGINT UNSIGNED NULL');
        $renameColumn('kategori_kas', 'name', 'nama', 'VARCHAR(255) NOT NULL');
        $renameColumn('kategori_kas', 'type', 'jenis', "ENUM('income','expense') NOT NULL");
        $renameColumn('kategori_kas', 'created_at', 'dibuat_pada', 'TIMESTAMP NULL DEFAULT NULL');
        $renameColumn('kategori_kas', 'updated_at', 'diperbarui_pada', 'TIMESTAMP NULL DEFAULT NULL');
    }

    // 8. MIGRASI KOLOM TABEL: catatan_kas
    if ($tableExists('catatan_kas')) {
        echo "\nMemeriksa kolom tabel `catatan_kas`...\n";
        $renameColumn('catatan_kas', 'user_id', 'id_pengguna', 'BIGINT UNSIGNED NOT NULL');
        $renameColumn('catatan_kas', 'cash_category_id', 'id_kategori_kas', 'BIGINT UNSIGNED NULL');
        $renameColumn('catatan_kas', 'stock_movement_id', 'id_mutasi_stok', 'BIGINT UNSIGNED NULL');
        $renameColumn('catatan_kas', 'customer_id', 'id_pelanggan', 'BIGINT UNSIGNED NULL');
        $renameColumn('catatan_kas', 'type', 'jenis', "ENUM('income','expense') NOT NULL");
        $renameColumn('catatan_kas', 'amount', 'nominal', 'DECIMAL(12,2) NOT NULL DEFAULT 0.00');
        $renameColumn('catatan_kas', 'entry_date', 'tanggal_transaksi', 'DATE NOT NULL');
        $renameColumn('catatan_kas', 'note', 'catatan', 'VARCHAR(255) NULL');
        $renameColumn('catatan_kas', 'deleted_at', 'dihapus_pada', 'TIMESTAMP NULL DEFAULT NULL');
        $renameColumn('catatan_kas', 'created_at', 'dibuat_pada', 'TIMESTAMP NULL DEFAULT NULL');
        $renameColumn('catatan_kas', 'updated_at', 'diperbarui_pada', 'TIMESTAMP NULL DEFAULT NULL');
    }

    // 9. MIGRASI KOLOM TABEL: token_reset_kata_sandi
    if ($tableExists('token_reset_kata_sandi')) {
        echo "\nMemeriksa kolom tabel `token_reset_kata_sandi`...\n";
        $renameColumn('token_reset_kata_sandi', 'created_at', 'dibuat_pada', 'TIMESTAMP NULL DEFAULT NULL');
    }

    // 10. MIGRASI KOLOM TABEL: sesi
    if ($tableExists('sesi')) {
        echo "\nMemeriksa kolom tabel `sesi`...\n";
        $renameColumn('sesi', 'user_id', 'id_pengguna', 'BIGINT UNSIGNED NULL');
        $renameColumn('sesi', 'ip_address', 'alamat_ip', 'VARCHAR(45) NULL');
        $renameColumn('sesi', 'last_activity', 'aktivitas_terakhir', 'INT NOT NULL');
    }

    // 11. Perbarui View pembeli
    if ($tableExists('pelanggan')) {
        $pdo->exec("CREATE OR REPLACE VIEW `pembeli` AS SELECT * FROM `pelanggan`;");
        echo "\n✓ View `pembeli` berhasil diperbarui sebagai alias tabel `pelanggan`.\n";
    }

    // 12. Aktifkan kembali Foreign Key Checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n============================================================\n";
    echo "  MIGRASI SKEMA BERHASIL DISELESAIKAN DENGAN SEMPURNA!     \n";
    echo "============================================================\n";
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
    echo "\n[ERROR] Migrasi gagal: " . $e->getMessage() . "\n";
    exit(1);
}

