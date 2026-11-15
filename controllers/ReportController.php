<?php

require_once __DIR__ . '/../helpers/helpers.php';

class ReportController
{
    public function index(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        $user = auth_user();

        // Access Restriction for Staff
        if (is_staff()) {
            view('reports.restricted', [
                'user' => $user,
                'title' => 'Akses Dibatasi - Warungku',
            ]);
            return;
        }

        $ownerId = warung_owner_id();

        date_default_timezone_set('Asia/Jakarta');
        $now = new DateTime('now');

        $period = $_GET['period'] ?? 'week';
        if (!in_array($period, ['today', 'week', 'month'])) {
            $period = 'week';
        }

        if ($period === 'today') {
            $startDate = new DateTime($now->format('Y-m-d 00:00:00'));
            $endDate = new DateTime($now->format('Y-m-d 23:59:59'));
            $periodLabel = 'Hari Ini';
        } elseif ($period === 'month') {
            $startDate = new DateTime($now->format('Y-m-01 00:00:00'));
            $endDate = new DateTime($now->format('Y-m-t 23:59:59'));
            $periodLabel = 'Bulan Ini';
        } else {
            // week (Starts Sunday, ends Saturday to match PRD)
            $dayOfWeek = (int) $now->format('w'); // 0 (Sun) to 6 (Sat)
            $startDate = (clone $now)->modify("-{$dayOfWeek} days")->setTime(0, 0, 0);
            $endDate = (clone $startDate)->modify('+6 days')->setTime(23, 59, 59);
            $periodLabel = 'Minggu Ini';
        }

        $startStr = $startDate->format('Y-m-d H:i:s');
        $endStr = $endDate->format('Y-m-d H:i:s');

        // 1. Total Income & Total Expense
        $incStmt = db()->prepare("
            SELECT COALESCE(SUM(nominal), 0) 
            FROM catatan_kas 
            WHERE id_pengguna = :id_pengguna 
              AND jenis = 'income' 
              AND dihapus_pada IS NULL 
              AND tanggal_transaksi BETWEEN :start AND :end
        ");
        $incStmt->execute(['id_pengguna' => $ownerId, 'start' => $startStr, 'end' => $endStr]);
        $totalIncome = (float) $incStmt->fetchColumn();

        $expStmt = db()->prepare("
            SELECT COALESCE(SUM(nominal), 0) 
            FROM catatan_kas 
            WHERE id_pengguna = :id_pengguna 
              AND jenis = 'expense' 
              AND dihapus_pada IS NULL 
              AND tanggal_transaksi BETWEEN :start AND :end
        ");
        $expStmt->execute(['id_pengguna' => $ownerId, 'start' => $startStr, 'end' => $endStr]);
        $totalExpense = (float) $expStmt->fetchColumn();

        // 2. HPP (Harga Pokok Penjualan)
        $hppStmt = db()->prepare("
            SELECT COALESCE(SUM(jumlah * harga_modal), 0)
            FROM mutasi_stok
            WHERE id_pengguna = :id_pengguna
              AND jenis = 'out'
              AND alasan = 'sale'
              AND dihapus_pada IS NULL
              AND tanggal_mutasi BETWEEN :start AND :end
        ");
        $hppStmt->execute(['id_pengguna' => $ownerId, 'start' => $startStr, 'end' => $endStr]);
        $hpp = (float) $hppStmt->fetchColumn();

        // 3. Pengeluaran Operasional (Non-Kulakan Expenses)
        $opStmt = db()->prepare("
            SELECT COALESCE(SUM(ce.nominal), 0)
            FROM catatan_kas ce
            LEFT JOIN kategori_kas cc ON cc.id = ce.id_kategori_kas
            WHERE ce.id_pengguna = :id_pengguna
              AND ce.jenis = 'expense'
              AND ce.id_mutasi_stok IS NULL
              AND (cc.nama IS NULL OR cc.nama != 'Kulakan')
              AND ce.dihapus_pada IS NULL
              AND ce.tanggal_transaksi BETWEEN :start AND :end
        ");
        $opStmt->execute(['id_pengguna' => $ownerId, 'start' => $startStr, 'end' => $endStr]);
        $operationalExpense = (float) $opStmt->fetchColumn();

        // 4. Laba Kotor & Laba Bersih
        $grossProfit = $totalIncome - $hpp;
        $netProfit = $totalIncome - $hpp - $operationalExpense;

        // 5. Top 5 Best Selling Products
        $topStmt = db()->prepare("
            SELECT 
                p.id,
                p.nama,
                p.nama AS name,
                p.satuan,
                p.satuan AS unit,
                SUM(sm.jumlah) AS total_quantity,
                SUM(sm.jumlah * sm.harga_satuan) AS total_revenue
            FROM mutasi_stok sm
            JOIN produk p ON p.id = sm.id_produk
            WHERE sm.id_pengguna = :id_pengguna
              AND sm.jenis = 'out'
              AND sm.alasan = 'sale'
              AND sm.dihapus_pada IS NULL
              AND sm.tanggal_mutasi BETWEEN :start AND :end
            GROUP BY p.id, p.nama, p.satuan
            ORDER BY total_quantity DESC
            LIMIT 5
        ");
        $topStmt->execute(['id_pengguna' => $ownerId, 'start' => $startStr, 'end' => $endStr]);
        $rawTop = $topStmt->fetchAll();

        $maxQty = 1;
        foreach ($rawTop as $row) {
            if ((int) $row['total_quantity'] > $maxQty) {
                $maxQty = (int) $row['total_quantity'];
            }
        }

        $topProducts = [];
        foreach ($rawTop as $index => $item) {
            $qty = (int) $item['total_quantity'];
            $revenue = (float) $item['total_revenue'];
            $pct = $maxQty > 0 ? round(($qty / $maxQty) * 100) : 0;

            $topProducts[] = [
                'rank' => $index + 1,
                'product_id' => $item['id'],
                'name' => $item['name'],
                'unit' => $item['unit'],
                'quantity' => $qty,
                'quantity_formatted' => "{$qty} {$item['unit']} terjual",
                'revenue' => $revenue,
                'revenue_formatted' => format_rupiah($revenue),
                'percentage' => max(15, min(100, $pct)),
            ];
        }

        // 6. Chart Bars Data
        $chartData = $this->buildChartData($ownerId, $period, $now, $startDate, $endDate);

        $report = [
            'period' => $period,
            'period_label' => $periodLabel,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'hpp' => $hpp,
            'operational_expense' => $operationalExpense,
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'top_products' => $topProducts,
            'chart_data' => $chartData,
        ];

        view('reports.index', [
            'period' => $period,
            'report' => $report,
            'title' => 'Laporan - Warungku',
        ]);
    }

    private function buildChartData(int $ownerId, string $period, DateTime $now, DateTime $startDate, DateTime $endDate): array
    {
        $bars = [];

        if ($period === 'week') {
            $dayLabels = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            $currentDay = clone $startDate;

            for ($i = 0; $i < 7; $i++) {
                $dayStart = (clone $currentDay)->setTime(0, 0, 0)->format('Y-m-d H:i:s');
                $dayEnd = (clone $currentDay)->setTime(23, 59, 59)->format('Y-m-d H:i:s');

                $stmt = db()->prepare("
                    SELECT 
                        COALESCE(SUM(CASE WHEN jenis = 'income' THEN nominal ELSE 0 END), 0) AS income,
                        COALESCE(SUM(CASE WHEN jenis = 'expense' THEN nominal ELSE 0 END), 0) AS expense
                    FROM catatan_kas
                    WHERE id_pengguna = :id_pengguna 
                      AND dihapus_pada IS NULL 
                      AND tanggal_transaksi BETWEEN :start AND :end
                ");
                $stmt->execute(['id_pengguna' => $ownerId, 'start' => $dayStart, 'end' => $dayEnd]);
                $vals = $stmt->fetch();

                $dayIndex = (int) $currentDay->format('w');
                $isCurrent = $currentDay->format('Y-m-d') === $now->format('Y-m-d');

                $bars[] = [
                    'label' => $dayLabels[$dayIndex],
                    'date' => $currentDay->format('d/m'),
                    'income' => (float) $vals['income'],
                    'expense' => (float) $vals['expense'],
                    'is_current' => $isCurrent,
                ];

                $currentDay->modify('+1 day');
            }
        } elseif ($period === 'today') {
            $blocks = [
                ['label' => '00-06', 'start' => 0, 'end' => 6],
                ['label' => '06-09', 'start' => 6, 'end' => 9],
                ['label' => '09-12', 'start' => 9, 'end' => 12],
                ['label' => '12-15', 'start' => 12, 'end' => 15],
                ['label' => '15-18', 'start' => 15, 'end' => 18],
                ['label' => '18-24', 'start' => 18, 'end' => 24],
            ];

            $currentHour = (int) $now->format('H');

            foreach ($blocks as $b) {
                $bStart = (clone $now)->setTime($b['start'], 0, 0)->format('Y-m-d H:i:s');
                $bEnd = $b['end'] === 24
                    ? (clone $now)->setTime(23, 59, 59)->format('Y-m-d H:i:s')
                    : (clone $now)->setTime($b['end'] - 1, 59, 59)->format('Y-m-d H:i:s');

                $stmt = db()->prepare("
                    SELECT 
                        COALESCE(SUM(CASE WHEN jenis = 'income' THEN nominal ELSE 0 END), 0) AS income,
                        COALESCE(SUM(CASE WHEN jenis = 'expense' THEN nominal ELSE 0 END), 0) AS expense
                    FROM catatan_kas
                    WHERE id_pengguna = :id_pengguna 
                      AND dihapus_pada IS NULL 
                      AND tanggal_transaksi BETWEEN :start AND :end
                ");
                $stmt->execute(['id_pengguna' => $ownerId, 'start' => $bStart, 'end' => $bEnd]);
                $vals = $stmt->fetch();

                $isCurrent = ($currentHour >= $b['start'] && $currentHour < $b['end']);

                $bars[] = [
                    'label' => $b['label'],
                    'date' => $b['label'],
                    'income' => (float) $vals['income'],
                    'expense' => (float) $vals['expense'],
                    'is_current' => $isCurrent,
                ];
            }
        } else {
            // Month
            $cursor = clone $startDate;
            $weekNum = 1;

            while ($cursor <= $endDate) {
                $wStart = (clone $cursor)->setTime(0, 0, 0);
                $wEnd = (clone $cursor)->modify('+6 days')->setTime(23, 59, 59);
                if ($wEnd > $endDate) {
                    $wEnd = clone $endDate;
                }

                $wStartStr = $wStart->format('Y-m-d H:i:s');
                $wEndStr = $wEnd->format('Y-m-d H:i:s');

                $stmt = db()->prepare("
                    SELECT 
                        COALESCE(SUM(CASE WHEN jenis = 'income' THEN nominal ELSE 0 END), 0) AS income,
                        COALESCE(SUM(CASE WHEN jenis = 'expense' THEN nominal ELSE 0 END), 0) AS expense
                    FROM catatan_kas
                    WHERE id_pengguna = :id_pengguna 
                      AND dihapus_pada IS NULL 
                      AND tanggal_transaksi BETWEEN :start AND :end
                ");
                $stmt->execute(['id_pengguna' => $ownerId, 'start' => $wStartStr, 'end' => $wEndStr]);
                $vals = $stmt->fetch();

                $isCurrent = ($now >= $wStart && $now <= $wEnd);

                $bars[] = [
                    'label' => "M{$weekNum}",
                    'date' => $wStart->format('d') . '-' . $wEnd->format('d M'),
                    'income' => (float) $vals['income'],
                    'expense' => (float) $vals['expense'],
                    'is_current' => $isCurrent,
                ];

                $weekNum++;
                $cursor->modify('+7 days');
            }
        }

        $maxVal = 0;
        foreach ($bars as $bar) {
            if ($bar['income'] > $maxVal) $maxVal = $bar['income'];
            if ($bar['expense'] > $maxVal) $maxVal = $bar['expense'];
        }

        foreach ($bars as &$bar) {
            $bar['income_height'] = $maxVal > 0 && $bar['income'] > 0
                ? max(6, round(($bar['income'] / $maxVal) * 100))
                : 0;
            $bar['expense_height'] = $maxVal > 0 && $bar['expense'] > 0
                ? max(6, round(($bar['expense'] / $maxVal) * 100))
                : 0;
        }
        unset($bar);

        return [
            'bars' => $bars,
            'max_value' => $maxVal,
        ];
    }
}

