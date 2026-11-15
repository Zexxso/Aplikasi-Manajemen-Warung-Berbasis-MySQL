<?php

require_once __DIR__ . '/../helpers/helpers.php';

class StaffController
{
    private function ensureOwner(): array
    {
        if (!auth_check()) {
            redirect('/login');
        }

        $user = auth_user();
        if (($user['peran'] ?? $user['role'] ?? '') !== 'owner') {
            set_flash('error', 'Hanya Pemilik Warung yang dapat mengakses fitur ini.');
            redirect('/dashboard');
        }

        return $user;
    }

    public function index(): void
    {
        $owner = $this->ensureOwner();

        $stmt = db()->prepare("
            SELECT id, nama, email, telepon, status_aktif, peran, id_pemilik, nama_toko, dibuat_pada, diperbarui_pada,
                   nama AS name, telepon AS phone, status_aktif AS is_active, peran AS role, id_pemilik AS owner_id, nama_toko AS shop_name
            FROM pengguna 
            WHERE id_pemilik = :id_pemilik 
              AND peran = 'staff' 
            ORDER BY nama ASC
        ");
        $stmt->execute(['id_pemilik' => $owner['id']]);
        $staffMembers = $stmt->fetchAll();

        view('staff.index', [
            'staffMembers' => $staffMembers,
            'owner' => $owner,
            'title' => 'Kelola Karyawan - Warungku',
        ]);
    }

    public function store(): void
    {
        $owner = $this->ensureOwner();

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $defaultPassword = !empty($password) ? $password : '123456';

        if (empty($name) || empty($phone)) {
            set_flash('error', 'Nama dan Nomor HP karyawan wajib diisi.');
            back();
        }

        // Check unique phone
        $checkStmt = db()->prepare("SELECT id FROM pengguna WHERE telepon = :phone LIMIT 1");
        $checkStmt->execute(['phone' => $phone]);
        if ($checkStmt->fetch()) {
            set_flash('error', 'Nomor HP ini sudah digunakan oleh akun lain.');
            back();
        }

        $hashed = password_hash($defaultPassword, PASSWORD_BCRYPT);
        $insStmt = db()->prepare("
            INSERT INTO pengguna (nama, telepon, email, kata_sandi, peran, id_pemilik, nama_toko, status_aktif, dibuat_pada, diperbarui_pada)
            VALUES (:nama, :telepon, NULL, :kata_sandi, 'staff', :id_pemilik, :nama_toko, 1, NOW(), NOW())
        ");
        $insStmt->execute([
            'nama' => $name,
            'telepon' => $phone,
            'kata_sandi' => $hashed,
            'id_pemilik' => $owner['id'],
            'nama_toko' => $owner['nama_toko'] ?? $owner['shop_name'] ?? 'Warung',
        ]);

        set_flash('status', "Akun karyawan {$name} berhasil ditambahkan dengan kata sandi: {$defaultPassword}");
        redirect('/staff');
    }

    public function toggleStatus(int $id): void
    {
        $owner = $this->ensureOwner();

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $stmt = db()->prepare("SELECT * FROM pengguna WHERE id = :id AND id_pemilik = :id_pemilik AND peran = 'staff' LIMIT 1");
        $stmt->execute(['id' => $id, 'id_pemilik' => $owner['id']]);
        $staff = $stmt->fetch();

        if (!$staff) {
            set_flash('error', 'Akun karyawan tidak ditemukan.');
            redirect('/staff');
        }

        $newStatus = empty($staff['status_aktif'] ?? $staff['is_active']) ? 1 : 0;
        $upStmt = db()->prepare("UPDATE pengguna SET status_aktif = :status, diperbarui_pada = NOW() WHERE id = :id");
        $upStmt->execute(['status' => $newStatus, 'id' => $id]);

        $statusText = $newStatus ? 'diaktifkan kembali' : 'dinonaktifkan';
        $staffName = $staff['nama'] ?? $staff['name'] ?? 'Karyawan';
        set_flash('status', "Akun {$staffName} berhasil {$statusText}.");

        redirect('/staff');
    }

    public function resetPassword(int $id): void
    {
        $owner = $this->ensureOwner();

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $stmt = db()->prepare("SELECT * FROM pengguna WHERE id = :id AND id_pemilik = :id_pemilik AND peran = 'staff' LIMIT 1");
        $stmt->execute(['id' => $id, 'id_pemilik' => $owner['id']]);
        $staff = $stmt->fetch();

        if (!$staff) {
            set_flash('error', 'Akun karyawan tidak ditemukan.');
            redirect('/staff');
        }

        $defaultPassword = '123456';
        $hashed = password_hash($defaultPassword, PASSWORD_BCRYPT);

        $upStmt = db()->prepare("UPDATE pengguna SET kata_sandi = :password, diperbarui_pada = NOW() WHERE id = :id");
        $upStmt->execute(['password' => $hashed, 'id' => $id]);

        $staffName = $staff['nama'] ?? $staff['name'] ?? 'Karyawan';
        set_flash('status', "Kata sandi {$staffName} berhasil direset ke {$defaultPassword}.");
        redirect('/staff');
    }
}

