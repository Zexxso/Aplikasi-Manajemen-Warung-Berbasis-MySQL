<?php

/**
 * Notification Helper - Warungku
 * Semua fungsi notifikasi untuk 4 kategori: stok, kasbon, keuangan, aktivitas
 */

/**
 * Buat notifikasi baru dengan anti-spam:
 * Tidak akan membuat duplikat jika sudah ada notif dengan tipe_referensi+id_referensi
 * yang belum dibaca di hari yang sama.
 *
 * @param string   $kategori       stok | kasbon | keuangan | aktivitas
 * @param string   $judul          Judul singkat (maks 100 karakter)
 * @param string   $pesan          Pesan detail
 * @param string   $target_url     URL/route tujuan saat diklik
 * @param string|null $tipe_referensi   Tipe entitas referensi (mis. 'produk', 'catatan_kas')
 * @param int|null $id_referensi   ID entitas referensi
 * @param int|null $id_pengguna    NULL = broadcast ke semua
 * @return bool    true = dibuat, false = dilewati (duplikat)
 */
function buat_notifikasi(
    string $kategori,
    string $judul,
    string $pesan,
    string $target_url,
    ?string $tipe_referensi = null,
    ?int $id_referensi = null,
    ?int $id_pengguna = null
): bool {
    try {
        $pdo = db();

        // Anti-spam: cek duplikat hari ini dengan referensi yang sama dan belum dibaca
        if ($tipe_referensi !== null && $id_referensi !== null) {
            $checkStmt = $pdo->prepare("
                SELECT COUNT(*) FROM notifikasi
                WHERE tipe_referensi = :tipe
                  AND id_referensi = :id_ref
                  AND sudah_dibaca = 0
                  AND DATE(dibuat_pada) = CURDATE()
            ");
            $checkStmt->execute([
                'tipe'   => $tipe_referensi,
                'id_ref' => $id_referensi,
            ]);
            if ((int) $checkStmt->fetchColumn() > 0) {
                return false; // Sudah ada notif duplikat hari ini
            }
        }

        $validKategori = ['stok', 'kasbon', 'keuangan', 'aktivitas'];
        if (!in_array($kategori, $validKategori, true)) {
            $kategori = 'aktivitas';
        }

        $insStmt = $pdo->prepare("
            INSERT INTO notifikasi (id_pengguna, kategori, judul, pesan, target_url, tipe_referensi, id_referensi, sudah_dibaca, dibuat_pada)
            VALUES (:id_pengguna, :kategori, :judul, :pesan, :target_url, :tipe_referensi, :id_referensi, 0, NOW())
        ");
        $insStmt->execute([
            'id_pengguna'    => $id_pengguna,
            'kategori'       => $kategori,
            'judul'          => mb_substr($judul, 0, 100),
            'pesan'          => $pesan,
            'target_url'     => $target_url,
            'tipe_referensi' => $tipe_referensi,
            'id_referensi'   => $id_referensi,
        ]);

        return true;
    } catch (Throwable $e) {
        // Jangan crash aplikasi karena gagal notifikasi
        error_log('[Notifikasi] Gagal buat notifikasi: ' . $e->getMessage());
        return false;
    }
}

/**
 * Hitung jumlah notifikasi yang belum dibaca.
 * Jika $id_pengguna null, hitung semua (broadcast + milik siapa pun).
 */
function hitung_notif_belum_dibaca(?int $id_pengguna = null): int
{
    try {
        $pdo = db();
        if ($id_pengguna !== null) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM notifikasi
                WHERE sudah_dibaca = 0
                  AND (id_pengguna = :uid OR id_pengguna IS NULL)
            ");
            $stmt->execute(['uid' => $id_pengguna]);
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) FROM notifikasi WHERE sudah_dibaca = 0");
        }
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * Ambil daftar notifikasi dengan filter kategori.
 * $filter: 'semua' | 'stok' | 'kasbon_keuangan' | 'aktivitas'
 */
function ambil_daftar_notifikasi(string $filter = 'semua', ?int $id_pengguna = null): array
{
    try {
        $pdo = db();

        $where = ['1=1'];
        $params = [];

        if ($id_pengguna !== null) {
            $where[] = '(id_pengguna = :uid OR id_pengguna IS NULL)';
            $params['uid'] = $id_pengguna;
        }

        switch ($filter) {
            case 'stok':
                $where[] = "kategori = 'stok'";
                break;
            case 'kasbon_keuangan':
                $where[] = "kategori IN ('kasbon', 'keuangan')";
                break;
            case 'aktivitas':
                $where[] = "kategori = 'aktivitas'";
                break;
            // 'semua': no additional filter
        }

        $sql = "SELECT * FROM notifikasi WHERE " . implode(' AND ', $where) . " ORDER BY dibuat_pada DESC LIMIT 200";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Tandai satu notifikasi sebagai sudah dibaca.
 */
function tandai_notif_dibaca(int $id): void
{
    try {
        $stmt = db()->prepare("UPDATE notifikasi SET sudah_dibaca = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    } catch (Throwable $e) {
        error_log('[Notifikasi] Gagal tandai dibaca: ' . $e->getMessage());
    }
}

/**
 * Tandai semua notifikasi sebagai sudah dibaca.
 */
function tandai_semua_notif_dibaca(?int $id_pengguna = null): void
{
    try {
        $pdo = db();
        if ($id_pengguna !== null) {
            $stmt = $pdo->prepare("UPDATE notifikasi SET sudah_dibaca = 1 WHERE (id_pengguna = :uid OR id_pengguna IS NULL)");
            $stmt->execute(['uid' => $id_pengguna]);
        } else {
            $pdo->exec("UPDATE notifikasi SET sudah_dibaca = 1");
        }
    } catch (Throwable $e) {
        error_log('[Notifikasi] Gagal tandai semua dibaca: ' . $e->getMessage());
    }
}

/**
 * Format waktu relatif untuk tampilan notifikasi.
 * Contoh: "5 menit lalu", "2 jam lalu", "kemarin", "3 Jan 2025"
 */
function format_waktu_relatif(string $dateTimeStr): string
{
    $now = time();
    $ts  = strtotime($dateTimeStr);
    if (!$ts) return $dateTimeStr;

    $diff = $now - $ts;

    if ($diff < 60) {
        return 'Baru saja';
    } elseif ($diff < 3600) {
        $mins = (int) ($diff / 60);
        return "{$mins} menit lalu";
    } elseif ($diff < 86400) {
        $hours = (int) ($diff / 3600);
        return "{$hours} jam lalu";
    } elseif ($diff < 172800) {
        return 'Kemarin';
    } else {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
        $day   = date('j', $ts);
        $month = $months[(int) date('n', $ts)] ?? date('M', $ts);
        $year  = date('Y', $ts);
        $yearSuffix = ($year !== date('Y')) ? " {$year}" : '';
        return "{$day} {$month}{$yearSuffix}";
    }
}

// ─────────────────────────────────────────────
// AUTO-CHECK FUNCTIONS (dipanggil di Dashboard)
// ─────────────────────────────────────────────

/**
 * Cek semua produk dengan stok <= stok_minimum dan buat notifikasi.
 */
function cek_notif_stok_menipis(): void
{
    try {
        $stmt = db()->query("
            SELECT id, nama, stok, stok_minimum, satuan
            FROM produk
            WHERE dihapus_pada IS NULL
              AND status_aktif = 1
              AND stok <= stok_minimum
        ");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $prod) {
            $nama     = $prod['nama'];
            $stok     = (int) $prod['stok'];
            $min      = (int) $prod['stok_minimum'];
            $satuan   = $prod['satuan'] ?: 'item';
            $id       = (int) $prod['id'];

            if ($stok === 0) {
                $judul = "Stok {$nama} Habis!";
                $pesan = "Stok {$nama} sudah habis (0 {$satuan}). Segera lakukan kulakan agar penjualan tidak terhenti.";
            } else {
                $judul = "Stok {$nama} Menipis";
                $pesan = "Stok {$nama} tinggal {$stok} {$satuan}, sudah di bawah batas minimum ({$min} {$satuan}). Pertimbangkan untuk kulakan.";
            }

            buat_notifikasi(
                'stok',
                $judul,
                $pesan,
                '/products?filter=low_stock',
                'produk',
                $id
            );
        }
    } catch (Throwable $e) {
        error_log('[Notifikasi] Gagal cek stok menipis: ' . $e->getMessage());
    }
}

/**
 * Cek kasbon yang belum lunas (payment_status=debt) di pesanan.
 * Ambil semua pesanan kasbon aktif dan buat notifikasi jika ada.
 */
function cek_notif_kasbon_aktif(): void
{
    try {
        $stmt = db()->query("
            SELECT p.id_pesanan, p.total_harga, p.tanggal_pesanan, pl.nama AS nama_pelanggan, pl.id AS id_pelanggan
            FROM pesanan p
            JOIN pelanggan pl ON pl.id = p.id_pelanggan
            WHERE p.dihapus_pada IS NULL
              AND p.status_pembayaran = 'debt'
        ");
        $kasbons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($kasbons as $kb) {
            $nama   = $kb['nama_pelanggan'];
            $total  = format_rupiah((float) $kb['total_harga']);
            $tgl    = !empty($kb['tanggal_pesanan']) ? date('d/m/Y', strtotime($kb['tanggal_pesanan'])) : '-';
            $custId = (int) $kb['id_pelanggan'];

            buat_notifikasi(
                'kasbon',
                "Kasbon {$nama} Belum Lunas",
                "Kasbon {$nama} senilai {$total} (tgl. {$tgl}) belum dilunasi. Ketuk untuk lihat detail.",
                '/customers/' . $custId,
                'kasbon_pelanggan',
                $custId
            );
        }
    } catch (Throwable $e) {
        error_log('[Notifikasi] Gagal cek kasbon aktif: ' . $e->getMessage());
    }
}

/**
 * Rekap harian otomatis: dipicu setelah pukul 20:00.
 * Hanya membuat 1 notifikasi per hari (cek tipe_referensi = 'rekap_harian').
 */
function cek_notif_rekap_harian(): void
{
    try {
        date_default_timezone_set('Asia/Jakarta');
        $hour = (int) date('G');

        // Hanya setelah pukul 20:00
        if ($hour < 20) {
            return;
        }

        // Anti-spam: cek apakah sudah ada rekap hari ini
        $checkStmt = db()->prepare("
            SELECT COUNT(*) FROM notifikasi
            WHERE tipe_referensi = 'rekap_harian'
              AND DATE(dibuat_pada) = CURDATE()
        ");
        $checkStmt->execute();
        if ((int) $checkStmt->fetchColumn() > 0) {
            return;
        }

        // Hitung pemasukan dan pengeluaran hari ini
        $sumStmt = db()->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN jenis = 'income' THEN nominal ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN jenis = 'expense' THEN nominal ELSE 0 END), 0) AS expense
            FROM catatan_kas
            WHERE dihapus_pada IS NULL
              AND DATE(tanggal_transaksi) = CURDATE()
        ");
        $sumStmt->execute();
        $metrics = $sumStmt->fetch(PDO::FETCH_ASSOC);

        $income  = (float) $metrics['income'];
        $expense = (float) $metrics['expense'];
        $laba    = $income - $expense;

        $labaStr     = format_rupiah(abs($laba));
        $labaSuffix  = $laba >= 0 ? "Laba: +{$labaStr}" : "Rugi: -{$labaStr}";

        $pesan = sprintf(
            "Pemasukan hari ini: %s · Pengeluaran: %s · %s. Ketuk untuk lihat laporan lengkap.",
            format_rupiah($income),
            format_rupiah($expense),
            $labaSuffix
        );

        // Gunakan id_referensi = tanggal hari ini sebagai bilangan bulat (YYYYMMDD)
        $todayInt = (int) date('Ymd');

        buat_notifikasi(
            'keuangan',
            'Rekap Kas Hari Ini',
            $pesan,
            '/reports',
            'rekap_harian',
            $todayInt
        );
    } catch (Throwable $e) {
        error_log('[Notifikasi] Gagal cek rekap harian: ' . $e->getMessage());
    }
}

/**
 * Picu notifikasi stok menipis untuk satu produk tertentu (dipanggil setelah barang keluar).
 */
function notif_stok_setelah_keluar(int $productId): void
{
    try {
        $stmt = db()->prepare("
            SELECT id, nama, stok, stok_minimum, satuan
            FROM produk
            WHERE id = :id AND dihapus_pada IS NULL LIMIT 1
        ");
        $stmt->execute(['id' => $productId]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prod) return;
        if ((int) $prod['stok'] > (int) $prod['stok_minimum']) return;

        $nama   = $prod['nama'];
        $stok   = (int) $prod['stok'];
        $min    = (int) $prod['stok_minimum'];
        $satuan = $prod['satuan'] ?: 'item';

        if ($stok === 0) {
            $judul = "Stok {$nama} Habis!";
            $pesan = "Stok {$nama} sudah habis setelah transaksi terakhir. Segera lakukan kulakan.";
        } else {
            $judul = "Stok {$nama} Menipis";
            $pesan = "Stok {$nama} tinggal {$stok} {$satuan} (min: {$min}). Pertimbangkan untuk kulakan segera.";
        }

        buat_notifikasi('stok', $judul, $pesan, '/products?filter=low_stock', 'produk', (int) $prod['id']);
    } catch (Throwable $e) {
        error_log('[Notifikasi] Gagal cek stok produk: ' . $e->getMessage());
    }
}

/**
 * Picu notifikasi pengeluaran besar.
 * Batas default: Rp 500.000
 */
const BATAS_PENGELUARAN_BESAR = 500000;

function notif_pengeluaran_besar(float $nominal, int $catatanKasId, string $catatan = ''): void
{
    if ($nominal < BATAS_PENGELUARAN_BESAR) return;

    $nominalStr = format_rupiah($nominal);
    $keterangan = $catatan ? " ({$catatan})" : '';
    buat_notifikasi(
        'keuangan',
        'Pengeluaran Besar Dicatat',
        "Pengeluaran sebesar {$nominalStr}{$keterangan} telah dicatat. Pastikan ini sesuai.",
        '/history',
        'catatan_kas',
        $catatanKasId
    );
}

/**
 * Picu notifikasi perubahan harga jual barang.
 */
function notif_perubahan_harga(int $productId, string $nama, float $hargaLama, float $hargaBaru): void
{
    $lamaStr = format_rupiah($hargaLama);
    $baruStr = format_rupiah($hargaBaru);
    buat_notifikasi(
        'aktivitas',
        'Harga Jual Diubah',
        "Harga jual {$nama} diubah dari {$lamaStr} menjadi {$baruStr}.",
        '/products',
        'produk_harga',
        $productId
    );
}

/**
 * Picu notifikasi transaksi dihapus.
 */
function notif_transaksi_dihapus(string $namaBarang, int $mutasiId): void
{
    buat_notifikasi(
        'aktivitas',
        'Transaksi Dihapus',
        "Transaksi {$namaBarang} telah dibatalkan dan stok/kas dikembalikan.",
        '/history',
        'mutasi_stok',
        $mutasiId
    );
}
