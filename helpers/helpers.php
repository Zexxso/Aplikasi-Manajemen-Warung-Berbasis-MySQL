<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (ob_get_level() === 0) {
    ob_start();
}

require_once __DIR__ . '/../config/database.php';

// --- Authentication Helpers ---

function auth_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $cachedUser = null;
    if ($cachedUser !== null && $cachedUser['id'] == $_SESSION['user_id']) {
        return $cachedUser;
    }

    $stmt = db()->prepare("SELECT * FROM pengguna WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user && empty($user['status_aktif'] ?? $user['is_active'])) {
        auth_logout();
        return null;
    }

    if ($user) {
        $user['name'] = $user['nama'] ?? $user['name'] ?? '';
        $user['shop_name'] = $user['nama_toko'] ?? $user['shop_name'] ?? '';
        $user['role'] = $user['peran'] ?? $user['role'] ?? 'owner';
        $user['phone'] = $user['telepon'] ?? $user['phone'] ?? '';
        $user['owner_id'] = $user['id_pemilik'] ?? $user['owner_id'] ?? null;
        $user['is_active'] = $user['status_aktif'] ?? $user['is_active'] ?? 1;
    }

    $cachedUser = $user ?: null;
    return $cachedUser;
}

function auth_check(): bool
{
    return auth_user() !== null;
}

function auth_login(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
}

function auth_logout(): void
{
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
}

function is_owner(): bool
{
    $user = auth_user();
    return $user && ($user['peran'] ?? $user['role'] ?? 'owner') === 'owner';
}

function is_staff(): bool
{
    $user = auth_user();
    return $user && ($user['peran'] ?? $user['role'] ?? '') === 'staff';
}

function warung_owner_id(): int
{
    $user = auth_user();
    if (!$user) return 0;
    $role = $user['peran'] ?? $user['role'] ?? 'owner';
    $ownerId = $user['id_pemilik'] ?? $user['owner_id'] ?? null;
    if ($role === 'staff' && !empty($ownerId)) {
        return (int) $ownerId;
    }
    return (int) $user['id'];
}

function get_warung_owner(): ?array
{
    $ownerId = warung_owner_id();
    if (!$ownerId) return null;
    $stmt = db()->prepare("SELECT * FROM pengguna WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $ownerId]);
    return $stmt->fetch() ?: null;
}

// --- CSRF Helpers ---

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    return $token && hash_equals(csrf_token(), $token);
}

// --- Flash Messages & Old Input ---

function set_flash(string $key, $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

function get_flash(string $key, $default = null)
{
    if (isset($_SESSION['_flash'][$key])) {
        $val = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
    return $default;
}

function flash(string $key, $message = null)
{
    if ($message !== null) {
        set_flash($key, $message);
        return null;
    }
    return get_flash($key);
}

function set_old(array $data): void
{
    $_SESSION['_old_input'] = $data;
}

function old(string $key, $default = '')
{
    if (isset($_SESSION['_old_input'][$key])) {
        $val = $_SESSION['_old_input'][$key];
        return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
    }
    return htmlspecialchars((string)$default, ENT_QUOTES, 'UTF-8');
}

function clear_old(): void
{
    unset($_SESSION['_old_input']);
}

// --- Formatting Helpers ---

function format_rupiah($number, bool $withPrefix = true): string
{
    $formatted = number_format((float)$number, 0, ',', '.');
    return $withPrefix ? 'Rp ' . $formatted : $formatted;
}

function format_date_id(string $dateTimeStr): string
{
    $timestamp = strtotime($dateTimeStr);
    if (!$timestamp) return $dateTimeStr;

    $days = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    $months = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Ags',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];

    $dayOfWeek = (int) date('w', $timestamp);
    $dayName = $days[$dayOfWeek] ?? '';
    $dayNum = date('j', $timestamp);
    $monthNum = (int) date('n', $timestamp);
    $monthName = $months[$monthNum] ?? '';
    $year = date('Y', $timestamp);

    return "{$dayName}, {$dayNum} {$monthName} {$year}";
}

function format_time(string $dateTimeStr): string
{
    $timestamp = strtotime($dateTimeStr);
    return $timestamp ? date('H.i', $timestamp) : '';
}

// --- Navigation & Routing Helpers ---

function url(string $path = ''): string
{
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    $cleanPath = '/' . ltrim($path, '/');
    return ($scriptDir ? $scriptDir : '') . $cleanPath;
}

function current_uri(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    if ($scriptDir && str_starts_with($uri, $scriptDir)) {
        $uri = substr($uri, strlen($scriptDir));
    }
    return '/' . ltrim($uri, '/');
}

function route_is(string $pattern): bool
{
    $current = current_uri();
    if ($pattern === $current) return true;
    if (str_ends_with($pattern, '*')) {
        $prefix = rtrim($pattern, '*');
        return str_starts_with($current, $prefix);
    }
    return false;
}

function redirect(string $path): void
{
    header("Location: " . url($path));
    exit;
}

function back(): void
{
    $referer = $_SERVER['HTTP_REFERER'] ?? url('/dashboard');
    header("Location: " . $referer);
    exit;
}

function json_response(array $data, int $statusCode = 200): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// --- View Renderer ---

function view(string $viewPath, array $data = [], string $layout = 'app'): void
{
    // Extract variables for view
    extract($data);

    // Global data when user is authenticated
    if (auth_check()) {
        $user = auth_user();
        $ownerId = warung_owner_id();

        if (!isset($globalProducts)) {
            $stmt = db()->prepare("
                SELECT id, nama, satuan, gambar, harga_beli, harga_jual, stok, stok_minimum, id_kategori,
                       nama AS name, satuan AS unit, gambar AS image, harga_beli AS buy_price, harga_jual AS sell_price, stok AS stock, stok_minimum AS min_stock, id_kategori AS category_id
                FROM produk 
                WHERE id_pengguna = :id_pengguna AND dihapus_pada IS NULL AND status_aktif = 1 
                ORDER BY nama ASC
            ");
            $stmt->execute(['id_pengguna' => $ownerId]);
            $globalProducts = $stmt->fetchAll();
        }

        if (!isset($globalCashCategories)) {
            $stmt = db()->prepare("
                SELECT id, nama, jenis, id_pengguna,
                       nama AS name, jenis AS type, id_pengguna AS user_id
                FROM kategori_kas 
                WHERE (id_pengguna = :id_pengguna OR id_pengguna IS NULL)
                ORDER BY nama ASC
            ");
            $stmt->execute(['id_pengguna' => $ownerId]);
            $globalCashCategories = $stmt->fetchAll();
        }

        if (!isset($globalProductCategories)) {
            $stmt = db()->prepare("
                SELECT id, nama, id_pengguna,
                       nama AS name, id_pengguna AS user_id
                FROM kategori 
                WHERE id_pengguna = :id_pengguna
                ORDER BY nama ASC
            ");
            $stmt->execute(['id_pengguna' => $ownerId]);
            $globalProductCategories = $stmt->fetchAll();
        }

        if (!isset($globalCustomers)) {
            $stmt = db()->prepare("
                SELECT id, kode, nama, telepon, catatan,
                       kode AS code, nama AS name, telepon AS phone, catatan AS note
                FROM pelanggan 
                WHERE id_pengguna = :id_pengguna AND dihapus_pada IS NULL
                ORDER BY nama ASC
            ");
            $stmt->execute(['id_pengguna' => $ownerId]);
            $globalCustomers = $stmt->fetchAll();
        }
    }

    // Capture child view content
    $childViewFile = __DIR__ . '/../views/' . str_replace('.', '/', $viewPath) . '.php';
    if (!file_exists($childViewFile)) {
        die("View file not found: " . htmlspecialchars($childViewFile));
    }

    ob_start();
    include $childViewFile;
    $slot = ob_get_clean();

    // Preserve page title
    $title = $data['title'] ?? 'Warungku';

    // Include surrounding layout
    if ($layout) {
        $layoutFile = __DIR__ . '/../views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $slot;
        }
    } else {
        echo $slot;
    }

    clear_old();
}

// --- Customer / Pembeli & Kasbon Helpers ---

function generate_invoice_no(int $movementId, ?string $date = null): string
{
    $d = $date ? date('ymd', strtotime($date)) : date('ymd');
    return sprintf('TRX-%s-%04d', $d, $movementId);
}

function generate_next_customer_code(int $ownerId): string
{
    $stmt = db()->prepare("
        SELECT kode 
        FROM pelanggan 
        WHERE id_pengguna = :id_pengguna AND kode LIKE 'CUST-%'
        ORDER BY id DESC
        LIMIT 100
    ");
    $stmt->execute(['id_pengguna' => $ownerId]);
    $codes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $maxNum = 0;
    foreach ($codes as $code) {
        if (preg_match('/^CUST-(\d+)$/i', $code, $matches)) {
            $num = (int) $matches[1];
            if ($num > $maxNum) {
                $maxNum = $num;
            }
        }
    }

    $next = $maxNum + 1;
    return sprintf('CUST-%03d', $next);
}

function get_or_create_kasbon_customer(int $ownerId, string $customerInput, ?string $phone = null): ?int
{
    $input = trim($customerInput);
    if ($input === '') {
        return null;
    }

    // 1. If numeric ID passed (selected from list)
    if (ctype_digit($input)) {
        $chk = db()->prepare("SELECT id FROM pelanggan WHERE id = :id AND id_pengguna = :id_pengguna AND dihapus_pada IS NULL LIMIT 1");
        $chk->execute(['id' => (int) $input, 'id_pengguna' => $ownerId]);
        $foundId = $chk->fetchColumn();
        if ($foundId) {
            return (int) $foundId;
        }
    }

    // 2. If matching existing code or name (case-insensitive)
    $findStmt = db()->prepare("
        SELECT id FROM pelanggan 
        WHERE id_pengguna = :id_pengguna AND dihapus_pada IS NULL AND (LOWER(nama) = LOWER(:nama_input) OR LOWER(kode) = LOWER(:kode_input))
        ORDER BY id DESC LIMIT 1
    ");
    $findStmt->execute([
        'id_pengguna' => $ownerId,
        'nama_input' => $input,
        'kode_input' => $input,
    ]);
    $existingId = $findStmt->fetchColumn();
    if ($existingId) {
        return (int) $existingId;
    }

    // 3. Otherwise, create a new kasbon customer record with the specified name
    $code = generate_next_customer_code($ownerId);
    $ins = db()->prepare("
        INSERT INTO pelanggan (id_pengguna, kode, nama, telepon, dibuat_pada, diperbarui_pada)
        VALUES (:id_pengguna, :kode, :nama, :telepon, NOW(), NOW())
    ");
    $ins->execute([
        'id_pengguna' => $ownerId,
        'kode' => $code,
        'nama' => $input,
        'telepon' => $phone ?: null,
    ]);
    return (int) db()->lastInsertId();
}

function get_or_create_customer(int $ownerId, ?string $customerInput, ?string $phone = null): ?int
{
    return get_or_create_kasbon_customer($ownerId, (string)$customerInput, $phone);
}

