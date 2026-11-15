<?php

require_once __DIR__ . '/../helpers/helpers.php';

class AuthController
{
    public function showLogin(): void
    {
        if (auth_check()) {
            redirect('/dashboard');
        }

        view('auth.login', [], 'guest');
    }

    public function login(): void
    {
        if (!csrf_verify()) {
            set_flash('errors', ['Token keamanan tidak valid. Silakan coba lagi.']);
            back();
        }

        $loginInput = trim($_POST['login'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        set_old(['login' => $loginInput]);

        if (empty($loginInput) || empty($password)) {
            set_flash('errors', ['Email atau Nomor HP dan Kata Sandi wajib diisi.']);
            back();
        }

        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'telepon';

        $stmt = db()->prepare("SELECT * FROM pengguna WHERE {$field} = :login LIMIT 1");
        $stmt->execute(['login' => $loginInput]);
        $user = $stmt->fetch();

        if ($user && empty($user['status_aktif'] ?? $user['is_active'])) {
            set_flash('errors', ['Akun ini telah dinonaktifkan oleh pemilik warung. Silakan hubungi pemilik warung.']);
            back();
        }

        $userPassword = $user['kata_sandi'] ?? $user['password'] ?? '';
        if ($user && password_verify($password, $userPassword)) {
            auth_login($user);
            redirect('/dashboard');
        }

        set_flash('errors', ['Email/No. HP atau kata sandi yang Anda masukkan salah.']);
        back();
    }

    public function logout(): void
    {
        auth_logout();
        set_flash('status', 'Anda telah berhasil keluar.');
        redirect('/login');
    }

    public function showForgotPassword(): void
    {
        if (auth_check()) {
            redirect('/dashboard');
        }

        view('auth.forgot-password', [], 'guest');
    }

    public function sendResetLink(): void
    {
        if (!csrf_verify()) {
            set_flash('errors', ['Token keamanan tidak valid.']);
            back();
        }

        $email = trim($_POST['email'] ?? '');
        set_old(['email' => $email]);

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('errors', ['Alamat email tidak valid.']);
            back();
        }

        $stmt = db()->prepare("SELECT * FROM pengguna WHERE email = :email AND peran = 'owner' LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            set_flash('errors', ['Email pemilik warung tidak ditemukan atau akun ini bukan pemilik warung.']);
            back();
        }

        // Generate token and store in token_reset_kata_sandi table if exists
        $token = bin2hex(random_bytes(20));
        try {
            $delStmt = db()->prepare("DELETE FROM token_reset_kata_sandi WHERE email = :email");
            $delStmt->execute(['email' => $email]);

            $insStmt = db()->prepare("INSERT INTO token_reset_kata_sandi (email, token, dibuat_pada) VALUES (:email, :token, NOW())");
            $insStmt->execute(['email' => $email, 'token' => password_hash($token, PASSWORD_BCRYPT)]);
        } catch (Exception $e) {
            // Table might not exist or exception, can proceed gracefully
        }

        // In local/native environment, provide instant reset link message
        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($email));
        set_flash('status', "Tautan reset telah dibuat: Silakan buka <a href='{$resetUrl}' class='underline font-bold'>Klik di sini untuk Reset Password</a>");
        back();
    }

    public function showResetPassword(string $token): void
    {
        $email = $_GET['email'] ?? '';
        view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ], 'guest');
    }

    public function updatePassword(): void
    {
        if (!csrf_verify()) {
            set_flash('errors', ['Token keamanan tidak valid.']);
            back();
        }

        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        if (strlen($password) < 6) {
            set_flash('errors', ['Kata sandi minimal 6 karakter.']);
            back();
        }

        if ($password !== $passwordConfirmation) {
            set_flash('errors', ['Konfirmasi kata sandi tidak cocok.']);
            back();
        }

        $stmt = db()->prepare("SELECT * FROM pengguna WHERE email = :email AND peran = 'owner' LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            set_flash('errors', ['Akun pemilik warung tidak ditemukan.']);
            back();
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $upStmt = db()->prepare("UPDATE pengguna SET kata_sandi = :password, diperbarui_pada = NOW() WHERE id = :id");
        $upStmt->execute(['password' => $hashed, 'id' => $user['id']]);

        try {
            $delStmt = db()->prepare("DELETE FROM token_reset_kata_sandi WHERE email = :email");
            $delStmt->execute(['email' => $email]);
        } catch (Exception $e) {}

        set_flash('status', 'Kata sandi Anda berhasil diperbarui. Silakan masuk.');
        redirect('/login');
    }
}

