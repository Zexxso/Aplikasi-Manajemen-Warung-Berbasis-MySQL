<?php

require_once __DIR__ . '/../helpers/helpers.php';

class DashboardController
{
    public function index(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        $user = auth_user();
        $ownerId = warung_owner_id();

        date_default_timezone_set('Asia/Jakarta');
        $now = new DateTime('now');

        $period = $_GET['period'] ?? 'today';
        if (!in_array($period, ['today', 'week', 'month'])) {
            $period = 'today';
        }

        // Calculate date boundaries
        if ($period === 'week') {
            // Monday as start of week, Sunday as end
            $start = (clone $now)->modify('this week monday 00:00:00');
            $end = (clone $now)->modify('this week sunday 23:59:59');

            $prevStart = (clone $start)->modify('-1 week');
            $prevEnd = (clone $end)->modify('-1 week');
            $prevLabel = 'dari minggu lalu';
            $periodLabel = 'Minggu Ini';
        } elseif ($period === 'month') {
            $start = new DateTime($now->format('Y-m-01 00:00:00'));
            $end = new DateTime($now->format('Y-m-t 23:59:59'));

            $prevStart = (clone $start)->modify('-1 month');
            $prevEnd = new DateTime($prevStart->format('Y-m-t 23:59:59'));
            $prevLabel = 'dari bulan lalu';
            $periodLabel = 'Bulan Ini';
        } else {
            // today
            $start = new DateTime($now->format('Y-m-d 00:00:00'));
            $end = new DateTime($now->format('Y-m-d 23:59:59'));

            $prevStart = (clone $start)->modify('-1 day');
            $prevEnd = (clone $end)->modify('-1 day');
            $prevLabel = 'dari kemarin';
            $periodLabel = 'Hari Ini';
        }

        $startStr = $start->format('Y-m-d H:i:s');
        $endStr = $end->format('Y-m-d H:i:s');
        $prevStartStr = $prevStart->format('Y-m-d H:i:s');
        $prevEndStr = $prevEnd->format('Y-m-d H:i:s');

        // Current period metrics (Single-store: Warung Bu Sari)
        $sumStmt = db()->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN jenis = 'income' THEN nominal ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN jenis = 'expense' THEN nominal ELSE 0 END), 0) AS expense
            FROM catatan_kas
            WHERE dihapus_pada IS NULL 
              AND tanggal_transaksi BETWEEN :start AND :end
        ");
        $sumStmt->execute([
            'start' => $startStr,
            'end' => $endStr,
        ]);
        $metrics = $sumStmt->fetch();
        $income = (float) $metrics['income'];
        $expense = (float) $metrics['expense'];
        $profit = $income - $expense;

        // Previous period metrics
        $prevSumStmt = db()->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN jenis = 'income' THEN nominal ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN jenis = 'expense' THEN nominal ELSE 0 END), 0) AS expense
            FROM catatan_kas
            WHERE dihapus_pada IS NULL 
              AND tanggal_transaksi BETWEEN :start AND :end
        ");
        $prevSumStmt->execute([
            'start' => $prevStartStr,
            'end' => $prevEndStr,
        ]);
        $prevMetrics = $prevSumStmt->fetch();
        $prevIncome = (float) $prevMetrics['income'];
        $prevExpense = (float) $prevMetrics['expense'];
        $prevProfit = $prevIncome - $prevExpense;

        $profitDiff = $profit - $prevProfit;
        if ($profitDiff >= 0) {
            $comparisonText = 'naik ' . format_rupiah($profitDiff) . ' ' . $prevLabel;
        } else {
            $comparisonText = 'turun ' . format_rupiah(abs($profitDiff)) . ' ' . $prevLabel;
        }

        $summary = [
            'period' => $period,
            'period_label' => $periodLabel,
            'income' => $income,
            'expense' => $expense,
            'profit' => $profit,
            'profit_diff' => $profitDiff,
            'comparison_text' => $comparisonText,
        ];

        // Low stock count
        $lowStockStmt = db()->prepare("
            SELECT COUNT(*) AS total
            FROM produk
            WHERE dihapus_pada IS NULL 
              AND status_aktif = 1 
              AND stok <= stok_minimum
        ");
        $lowStockStmt->execute();
        $lowStockCount = (int) $lowStockStmt->fetchColumn();

        // Auto-check notifications (stok menipis & rekap harian)
        cek_notif_stok_menipis();
        cek_notif_rekap_harian();

        // Unread notification count for bell badge
        $unreadNotifCount = hitung_notif_belum_dibaca((int) $user['id']);

        // Recent 5 transactions
        $recentStmt = db()->prepare("
            SELECT 
                ce.id,
                ce.jenis,
                ce.jenis AS type,
                ce.nominal,
                ce.nominal AS amount,
                ce.tanggal_transaksi,
                ce.tanggal_transaksi AS entry_date,
                ce.catatan,
                ce.catatan AS note,
                cc.nama AS category_name,
                cc.nama AS nama_kategori,
                sm.id AS stock_movement_id,
                sm.id AS id_mutasi_stok,
                sm.jenis AS stock_type,
                sm.jenis AS jenis_mutasi,
                sm.jumlah AS quantity,
                sm.jumlah,
                p.nama AS product_name,
                p.nama AS nama_produk,
                p.satuan AS unit,
                p.satuan,
                cust.id AS customer_id,
                cust.id AS id_pelanggan,
                cust.kode AS customer_code,
                cust.kode,
                cust.nama AS customer_name,
                cust.nama AS nama_pelanggan
            FROM catatan_kas ce
            LEFT JOIN kategori_kas cc ON cc.id = ce.id_kategori_kas
            LEFT JOIN mutasi_stok sm ON sm.id = ce.id_mutasi_stok
            LEFT JOIN produk p ON p.id = sm.id_produk
            LEFT JOIN pelanggan cust ON cust.id = COALESCE(sm.id_pelanggan, ce.id_pelanggan)
            WHERE ce.dihapus_pada IS NULL
            ORDER BY ce.tanggal_transaksi DESC, ce.id DESC
            LIMIT 5
        ");
        $recentStmt->execute();
        $recentTransactions = $recentStmt->fetchAll();

        // Greeting
        $hour = (int) $now->format('H');
        if ($hour >= 4 && $hour < 11) {
            $greeting = 'Selamat pagi,';
        } elseif ($hour >= 11 && $hour < 15) {
            $greeting = 'Selamat siang,';
        } elseif ($hour >= 15 && $hour < 18) {
            $greeting = 'Selamat sore,';
        } else {
            $greeting = 'Selamat malam,';
        }

        view('dashboard.index', [
            'user'               => $user,
            'greeting'           => $greeting,
            'period'             => $period,
            'summary'            => $summary,
            'lowStockCount'      => $lowStockCount,
            'recentTransactions' => $recentTransactions,
            'unreadNotifCount'   => $unreadNotifCount,
            'title'              => 'Beranda - Warungku',
        ]);
    }
}

