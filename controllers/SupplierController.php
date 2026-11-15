<?php

require_once __DIR__ . '/../helpers/helpers.php';

class SupplierController
{
    private function ensureAuth(): array
    {
        if (!auth_check()) {
            redirect('/login');
        }
        return auth_user();
    }

    public function index(): void
    {
        $user = $this->ensureAuth();
        $q = trim($_GET['q'] ?? '');

        $sql = "
            SELECT 
                p.id_pemasok,
                p.nama_pemasok,
                p.alamat_pemasok,
                p.no_hp_pemasok,
                p.email_pemasok,
                p.dibuat_pada,
                COUNT(s.id_suplai) AS total_suplai,
                COALESCE(SUM(s.total_harga), 0) AS total_nominal_suplai
            FROM pemasok p
            LEFT JOIN suplai s ON s.id_pemasok = p.id_pemasok AND s.dihapus_pada IS NULL
            WHERE p.dihapus_pada IS NULL
        ";
        $params = [];

        if ($q !== '') {
            $sql .= " AND (p.nama_pemasok LIKE :q OR p.no_hp_pemasok LIKE :q OR p.alamat_pemasok LIKE :q)";
            $params['q'] = "%{$q}%";
        }

        $sql .= " GROUP BY p.id_pemasok ORDER BY p.nama_pemasok ASC";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $suppliers = $stmt->fetchAll();

        view('suppliers.index', [
            'suppliers' => $suppliers,
            'q' => $q,
            'user' => $user,
            'title' => 'Pemasok & Kulakan - Warungku',
        ]);
    }

    public function store(): void
    {
        $user = $this->ensureAuth();

        if (!csrf_verify()) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                json_response(['success' => false, 'message' => 'Token keamanan tidak valid.'], 400);
            }
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $nama = trim($_POST['nama_pemasok'] ?? '');
        $noHp = trim($_POST['no_hp_pemasok'] ?? '');
        $email = trim($_POST['email_pemasok'] ?? '');
        $alamat = trim($_POST['alamat_pemasok'] ?? '');

        if ($nama === '') {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                json_response(['success' => false, 'message' => 'Nama pemasok wajib diisi.'], 422);
            }
            set_flash('error', 'Nama pemasok wajib diisi.');
            back();
        }

        $stmt = db()->prepare("
            INSERT INTO pemasok (nama_pemasok, no_hp_pemasok, email_pemasok, alamat_pemasok, dibuat_pada, diperbarui_pada)
            VALUES (:nama, :nohp, :email, :alamat, NOW(), NOW())
        ");
        $stmt->execute([
            'nama' => $nama,
            'nohp' => $noHp ?: null,
            'email' => $email ?: null,
            'alamat' => $alamat ?: null,
        ]);
        $id = (int) db()->lastInsertId();

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            json_response([
                'success' => true,
                'message' => "Pemasok {$nama} berhasil ditambahkan.",
                'pemasok' => [
                    'id_pemasok' => $id,
                    'nama_pemasok' => $nama,
                    'no_hp_pemasok' => $noHp,
                    'alamat_pemasok' => $alamat,
                ]
            ]);
        }

        set_flash('status', "Pemasok {$nama} berhasil ditambahkan.");
        redirect('/suppliers');
    }

    public function update(int $id): void
    {
        $user = $this->ensureAuth();

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $nama = trim($_POST['nama_pemasok'] ?? '');
        $noHp = trim($_POST['no_hp_pemasok'] ?? '');
        $email = trim($_POST['email_pemasok'] ?? '');
        $alamat = trim($_POST['alamat_pemasok'] ?? '');

        if ($nama === '') {
            set_flash('error', 'Nama pemasok wajib diisi.');
            back();
        }

        $stmt = db()->prepare("
            UPDATE pemasok 
            SET nama_pemasok = :nama, no_hp_pemasok = :nohp, email_pemasok = :email, alamat_pemasok = :alamat, diperbarui_pada = NOW()
            WHERE id_pemasok = :id AND dihapus_pada IS NULL
        ");
        $stmt->execute([
            'nama' => $nama,
            'nohp' => $noHp ?: null,
            'email' => $email ?: null,
            'alamat' => $alamat ?: null,
            'id' => $id,
        ]);

        set_flash('status', "Data pemasok {$nama} berhasil diperbarui.");
        redirect('/suppliers');
    }

    public function destroy(int $id): void
    {
        $user = $this->ensureAuth();

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $stmt = db()->prepare("UPDATE pemasok SET dihapus_pada = NOW() WHERE id_pemasok = :id");
        $stmt->execute(['id' => $id]);

        set_flash('status', 'Pemasok berhasil dihapus.');
        redirect('/suppliers');
    }

    public function show(int $id): void
    {
        $user = $this->ensureAuth();

        $stmt = db()->prepare("
            SELECT * FROM pemasok WHERE id_pemasok = :id AND dihapus_pada IS NULL LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $supplier = $stmt->fetch();

        if (!$supplier) {
            set_flash('error', 'Data pemasok tidak ditemukan.');
            redirect('/suppliers');
        }

        // Ambil riwayat suplai dari pemasok ini
        $suppliesStmt = db()->prepare("
            SELECT 
                s.*,
                p.nama AS nama_produk,
                p.satuan AS satuan_produk,
                u.nama AS nama_pegawai
            FROM suplai s
            JOIN produk p ON p.id = s.id_produk
            LEFT JOIN pengguna u ON u.id = s.id_pengguna
            WHERE s.id_pemasok = :id AND s.dihapus_pada IS NULL
            ORDER BY s.tanggal_suplai DESC, s.id_suplai DESC
        ");
        $suppliesStmt->execute(['id' => $id]);
        $supplies = $suppliesStmt->fetchAll();

        view('suppliers.show', [
            'supplier' => $supplier,
            'supplies' => $supplies,
            'user' => $user,
            'title' => 'Riwayat Pasokan: ' . $supplier['nama_pemasok'],
        ]);
    }

    public function apiList(): void
    {
        $this->ensureAuth();
        $stmt = db()->query("
            SELECT id_pemasok, nama_pemasok, no_hp_pemasok, alamat_pemasok 
            FROM pemasok 
            WHERE dihapus_pada IS NULL 
            ORDER BY nama_pemasok ASC
        ");
        json_response($stmt->fetchAll());
    }
}
