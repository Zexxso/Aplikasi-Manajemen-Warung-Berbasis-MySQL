<?php

require_once __DIR__ . '/../helpers/helpers.php';

class CustomerController
{
    public function index(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        $search = trim($_GET['search'] ?? '');

        // Base query for kasbon customers with debt statistics (Single-store: Warung Bu Sari)
        $sql = "
            SELECT 
                c.id,
                c.kode,
                c.kode AS code,
                c.nama,
                c.nama AS name,
                c.telepon,
                c.telepon AS phone,
                c.alamat,
                c.alamat AS address,
                c.catatan,
                c.catatan AS note,
                c.dibuat_pada,
                c.dibuat_pada AS created_at,
                COUNT(DISTINCT sm.id) AS total_orders,
                COALESCE(SUM(CASE WHEN sm.status_pembayaran = 'debt' THEN (sm.jumlah * sm.harga_satuan) ELSE 0 END), 0) AS total_unpaid_debt,
                COALESCE(SUM(CASE WHEN sm.status_pembayaran = 'paid' THEN (sm.jumlah * sm.harga_satuan) ELSE 0 END), 0) AS total_paid_debt,
                COALESCE(SUM(sm.jumlah * sm.harga_satuan), 0) AS total_spent,
                MAX(sm.tanggal_mutasi) AS last_purchase_at
            FROM pelanggan c
            LEFT JOIN mutasi_stok sm ON sm.id_pelanggan = c.id AND sm.dihapus_pada IS NULL AND sm.alasan = 'sale'
            WHERE c.dihapus_pada IS NULL
        ";

        $params = [];

        if ($search !== '') {
            $sql .= " AND (c.nama LIKE :search OR c.kode LIKE :search OR c.telepon LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        $sql .= " GROUP BY c.id, c.kode, c.nama, c.telepon, c.alamat, c.catatan, c.dibuat_pada ORDER BY SUM(CASE WHEN sm.status_pembayaran = 'debt' THEN (sm.jumlah * sm.harga_satuan) ELSE 0 END) DESC, MAX(sm.tanggal_mutasi) DESC, c.id DESC";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll();

        // Overall kasbon summary metrics
        $summaryStmt = db()->prepare("
            SELECT 
                COUNT(DISTINCT c.id) AS total_customers,
                COUNT(DISTINCT sm.id) AS total_sales_count,
                COALESCE(SUM(CASE WHEN sm.status_pembayaran = 'debt' THEN (sm.jumlah * sm.harga_satuan) ELSE 0 END), 0) AS total_unpaid_all,
                COALESCE(SUM(CASE WHEN sm.status_pembayaran = 'paid' THEN (sm.jumlah * sm.harga_satuan) ELSE 0 END), 0) AS total_paid_all,
                COALESCE(SUM(sm.jumlah * sm.harga_satuan), 0) AS total_spent_all
            FROM pelanggan c
            LEFT JOIN mutasi_stok sm ON sm.id_pelanggan = c.id AND sm.dihapus_pada IS NULL AND sm.alasan = 'sale'
            WHERE c.dihapus_pada IS NULL
        ");
        $summaryStmt->execute();
        $summary = $summaryStmt->fetch() ?: [
            'total_customers' => 0,
            'total_sales_count' => 0,
            'total_unpaid_all' => 0,
            'total_paid_all' => 0,
            'total_spent_all' => 0,
        ];

        view('customers.index', [
            'customers' => $customers,
            'summary' => $summary,
            'search' => $search,
            'title' => 'Buku Kasbon & Pembeli - Warungku',
        ]);
    }

    public function show(int $id): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        // Fetch customer info
        $stmt = db()->prepare("
            SELECT *,
                   id AS id, kode AS code, nama AS name, telepon AS phone, alamat AS address, catatan AS note, dibuat_pada AS created_at
            FROM pelanggan 
            WHERE id = :id AND dihapus_pada IS NULL 
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $customer = $stmt->fetch();

        if (!$customer) {
            set_flash('error', 'Data pembeli tidak ditemukan.');
            redirect('/customers');
        }

        // Fetch all purchase / kasbon items for this customer
        $trxStmt = db()->prepare("
            SELECT 
                sm.id AS movement_id,
                sm.id AS id_mutasi_stok,
                sm.no_faktur AS invoice_no,
                sm.no_faktur,
                sm.id_produk AS product_id,
                sm.id_produk,
                p.nama AS product_name,
                p.nama,
                p.satuan AS product_unit,
                p.satuan,
                sm.jumlah AS quantity,
                sm.jumlah,
                sm.harga_satuan AS unit_price,
                sm.harga_satuan,
                (sm.jumlah * sm.harga_satuan) AS subtotal,
                sm.tanggal_mutasi AS movement_date,
                sm.tanggal_mutasi,
                sm.status_pembayaran AS payment_status,
                sm.status_pembayaran,
                sm.metode_pembayaran,
                sm.dibayar_pada AS paid_at,
                sm.dibayar_pada,
                sm.catatan AS note,
                sm.catatan,
                ce.id AS cash_id
            FROM mutasi_stok sm
            LEFT JOIN produk p ON p.id = sm.id_produk
            LEFT JOIN catatan_kas ce ON ce.id_mutasi_stok = sm.id AND ce.dihapus_pada IS NULL
            WHERE sm.id_pelanggan = :id_pelanggan 
              AND sm.dihapus_pada IS NULL
            ORDER BY (sm.status_pembayaran = 'debt') DESC, sm.tanggal_mutasi DESC, sm.id DESC
        ");
        $trxStmt->execute(['id_pelanggan' => $id]);
        $transactions = $trxStmt->fetchAll();

        // Calculate totals
        $totalItems = 0;
        $totalSpent = 0;
        $unpaidDebt = 0;
        $paidDebt = 0;
        foreach ($transactions as $tx) {
            $sub = (float) $tx['subtotal'];
            $qty = (int) ($tx['jumlah'] ?? $tx['quantity']);
            $status = $tx['status_pembayaran'] ?? $tx['payment_status'];
            $totalItems += $qty;
            $totalSpent += $sub;
            if ($status === 'debt') {
                $unpaidDebt += $sub;
            } else {
                $paidDebt += $sub;
            }
        }

        $custName = $customer['nama'] ?? $customer['name'];
        view('customers.show', [
            'customer' => $customer,
            'transactions' => $transactions,
            'totalOrders' => count($transactions),
            'totalItems' => $totalItems,
            'totalSpent' => $totalSpent,
            'unpaidDebt' => $unpaidDebt,
            'paidDebt' => $paidDebt,
            'title' => "Buku Kasbon - {$custName}",
        ]);
    }

    public function store(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $name = trim($_POST['nama'] ?? $_POST['name'] ?? '');
        $phone = trim($_POST['telepon'] ?? $_POST['phone'] ?? '');
        $address = trim($_POST['alamat'] ?? $_POST['address'] ?? '');
        $note = trim($_POST['catatan'] ?? $_POST['note'] ?? '');

        if ($name === '') {
            set_flash('error', 'Nama pembeli wajib diisi.');
            back();
        }

        $code = generate_next_customer_code();

        $stmt = db()->prepare("
            INSERT INTO pelanggan (kode, nama, telepon, alamat, catatan, dibuat_pada, diperbarui_pada)
            VALUES (:kode, :nama, :telepon, :alamat, :catatan, NOW(), NOW())
        ");
        $stmt->execute([
            'kode' => $code,
            'nama' => $name,
            'telepon' => $phone ?: null,
            'alamat' => $address ?: null,
            'catatan' => $note ?: null,
        ]);

        set_flash('status', "Pembeli {$name} ({$code}) berhasil ditambahkan!");
        redirect('/customers');
    }

    public function update(int $id): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $name = trim($_POST['nama'] ?? $_POST['name'] ?? '');
        $phone = trim($_POST['telepon'] ?? $_POST['phone'] ?? '');
        $address = trim($_POST['alamat'] ?? $_POST['address'] ?? '');
        $note = trim($_POST['catatan'] ?? $_POST['note'] ?? '');

        if ($name === '') {
            set_flash('error', 'Nama pembeli tidak boleh kosong.');
            back();
        }

        $stmt = db()->prepare("
            UPDATE pelanggan 
            SET nama = :nama, telepon = :telepon, alamat = :alamat, catatan = :catatan, diperbarui_pada = NOW()
            WHERE id = :id AND dihapus_pada IS NULL
        ");
        $stmt->execute([
            'nama' => $name,
            'telepon' => $phone ?: null,
            'alamat' => $address ?: null,
            'catatan' => $note ?: null,
            'id' => $id,
        ]);

        set_flash('status', "Data pembeli {$name} berhasil diperbarui!");
        back();
    }

    public function destroy(int $id): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $stmt = db()->prepare("
            UPDATE pelanggan 
            SET dihapus_pada = NOW(), diperbarui_pada = NOW() 
            WHERE id = :id AND dihapus_pada IS NULL
        ");
        $stmt->execute(['id' => $id]);

        set_flash('status', 'Data pembeli berhasil dihapus.');
        redirect('/customers');
    }
}

