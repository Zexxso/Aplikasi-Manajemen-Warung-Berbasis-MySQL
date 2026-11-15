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
            SELECT id, nama, email, telepon, alamat, status_aktif, peran, dibuat_pada, diperbarui_pada,
                   nama AS name, telepon AS phone, status_aktif AS is_active, peran AS role
            FROM pengguna 
            WHERE peran = 'staff' 
            ORDER BY nama ASC
        ");
        $stmt->execute();
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

        $name = trim($_POST['name'] ?? $_POST['nama'] ?? '');
        $phone = trim($_POST['phone'] ?? $_POST['telepon'] ?? '');
        $address = trim($_POST['alamat'] ?? $_POST['address'] ?? '');
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
            INSERT INTO pengguna (nama, telepon, alamat, email, kata_sandi, peran, status_aktif, dibuat_pada, diperbarui_pada)
            VALUES (:nama, :telepon, :alamat, NULL, :kata_sandi, 'staff', 1, NOW(), NOW())
        ");
        $insStmt->execute([
            'nama' => $name,
            'telepon' => $phone,
            'alamat' => $address ?: null,
            'kata_sandi' => $hashed,
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

        $stmt = db()->prepare("SELECT * FROM pengguna WHERE id = :id AND peran = 'staff' LIMIT 1");
        $stmt->execute(['id' => $id]);
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

        $stmt = db()->prepare("SELECT * FROM pengguna WHERE id = :id AND peran = 'staff' LIMIT 1");
        $stmt->execute(['id' => $id]);
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

