<?php

// Serve static files directly if running under PHP built-in server
if (php_sapi_name() === 'cli-server') {
    $requestedFile = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($requestedFile) && pathinfo($requestedFile, PATHINFO_EXTENSION) !== 'php') {
        return false;
    }
}

require_once __DIR__ . '/helpers/helpers.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/TransactionController.php';
require_once __DIR__ . '/controllers/HistoryController.php';
require_once __DIR__ . '/controllers/ReportController.php';
require_once __DIR__ . '/controllers/StaffController.php';
require_once __DIR__ . '/controllers/CustomerController.php';

// Determine HTTP Method (Support Method Spoofing for PUT, PATCH, DELETE)
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

// Determine Current Path URI
$uri = current_uri();

// Route Dispatcher
switch (true) {
    // -------------------------------------------------------------
    // Root & Auth
    // -------------------------------------------------------------
    case $uri === '/' && $method === 'GET':
        if (auth_check()) {
            redirect('/dashboard');
        } else {
            redirect('/login');
        }
        break;

    case $uri === '/login' && $method === 'GET':
        (new AuthController())->showLogin();
        break;

    case $uri === '/login' && $method === 'POST':
        (new AuthController())->login();
        break;

    case $uri === '/logout' && $method === 'POST':
        (new AuthController())->logout();
        break;

    case $uri === '/forgot-password' && $method === 'GET':
        (new AuthController())->showForgotPassword();
        break;

    case $uri === '/forgot-password' && $method === 'POST':
        (new AuthController())->sendResetLink();
        break;

    case preg_match('#^/reset-password/([^/]+)$#', $uri, $matches) && $method === 'GET':
        (new AuthController())->showResetPassword($matches[1]);
        break;

    case $uri === '/reset-password' && $method === 'POST':
        (new AuthController())->updatePassword();
        break;

    // -------------------------------------------------------------
    // Dashboard
    // -------------------------------------------------------------
    case $uri === '/dashboard' && $method === 'GET':
        (new DashboardController())->index();
        break;

    // -------------------------------------------------------------
    // Products Management CRUD
    // -------------------------------------------------------------
    case $uri === '/products' && $method === 'GET':
        (new ProductController())->index();
        break;

    case $uri === '/products' && $method === 'POST':
        (new ProductController())->store();
        break;

    case preg_match('#^/products/(\d+)$#', $uri, $matches) && $method === 'PUT':
        (new ProductController())->update((int) $matches[1]);
        break;

    case preg_match('#^/products/(\d+)$#', $uri, $matches) && $method === 'DELETE':
        (new ProductController())->destroy((int) $matches[1]);
        break;

    case $uri === '/products/quick-store' && $method === 'POST':
        (new TransactionController())->quickStoreProduct();
        break;

    // -------------------------------------------------------------
    // Transactions (+ Catat)
    // -------------------------------------------------------------
    case $uri === '/stock/in' && $method === 'POST':
        (new TransactionController())->storeStockIn();
        break;

    case $uri === '/stock/out' && $method === 'POST':
        (new TransactionController())->storeStockOut();
        break;

    case $uri === '/cash' && $method === 'POST':
        (new TransactionController())->storeCash();
        break;

    // -------------------------------------------------------------
    // History (/history & Soft-Delete Reversal)
    // -------------------------------------------------------------
    case $uri === '/history' && $method === 'GET':
        (new HistoryController())->index();
        break;

    case preg_match('#^/history/([a-zA-Z0-9_-]+)/(\d+)$#', $uri, $matches) && $method === 'DELETE':
        (new HistoryController())->destroy($matches[1], (int) $matches[2]);
        break;

    // -------------------------------------------------------------
    // Detail Transaksi, Struk Penjualan & Pelunasan Kasbon
    // -------------------------------------------------------------
    case preg_match('#^/transactions/(\d+)$#', $uri, $matches) && $method === 'GET':
        (new TransactionController())->showDetail((int) $matches[1]);
        break;

    case preg_match('#^/receipt/(\d+)$#', $uri, $matches) && $method === 'GET':
        (new TransactionController())->showReceipt((int) $matches[1]);
        break;

    case preg_match('#^/kasbon/(\d+)/settle$#', $uri, $matches) && $method === 'POST':
        (new TransactionController())->settleKasbon((int) $matches[1]);
        break;

    // -------------------------------------------------------------
    // Reports (/reports - Owner Full Analytics, Staff Restricted)
    // -------------------------------------------------------------
    case $uri === '/reports' && $method === 'GET':
        (new ReportController())->index();
        break;

    // -------------------------------------------------------------
    // Customers Management (/customers & /customers/{id})
    // -------------------------------------------------------------
    case $uri === '/customers' && $method === 'GET':
        (new CustomerController())->index();
        break;

    case $uri === '/customers' && $method === 'POST':
        (new CustomerController())->store();
        break;

    case preg_match('#^/customers/(\d+)$#', $uri, $matches) && $method === 'GET':
        (new CustomerController())->show((int) $matches[1]);
        break;

    case preg_match('#^/customers/(\d+)$#', $uri, $matches) && $method === 'PUT':
        (new CustomerController())->update((int) $matches[1]);
        break;

    case preg_match('#^/customers/(\d+)$#', $uri, $matches) && $method === 'DELETE':
        (new CustomerController())->destroy((int) $matches[1]);
        break;

    // -------------------------------------------------------------
    // Staff Management (/staff - Owner Only)
    // -------------------------------------------------------------
    case $uri === '/staff' && $method === 'GET':
        (new StaffController())->index();
        break;

    case $uri === '/staff' && $method === 'POST':
        (new StaffController())->store();
        break;

    case preg_match('#^/staff/(\d+)/toggle-status$#', $uri, $matches) && in_array($method, ['PATCH', 'POST']):
        (new StaffController())->toggleStatus((int) $matches[1]);
        break;

    case preg_match('#^/staff/(\d+)/reset-password$#', $uri, $matches) && $method === 'POST':
        (new StaffController())->resetPassword((int) $matches[1]);
        break;

    // -------------------------------------------------------------
    // 404 Not Found Fallback
    // -------------------------------------------------------------
    default:
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>404 Halaman Tidak Ditemukan - Warungku</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-[#0B0E17] text-white flex items-center justify-center min-h-screen px-4">
            <div class="text-center max-w-[340px]">
                <div class="text-6xl font-extrabold text-[#4B4EF0] mb-2">404</div>
                <h1 class="text-lg font-bold mb-1">Halaman Tidak Ditemukan</h1>
                <p class="text-xs text-slate-400 mb-6">Alamat halaman yang Anda tuju (<?= htmlspecialchars($uri) ?>) tidak terdaftar.</p>
                <a href="<?= url('/dashboard') ?>" class="inline-block px-5 py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] text-white text-xs font-bold rounded-xl shadow-lg transition">
                    Kembali ke Beranda
                </a>
            </div>
        </body>
        </html>
        <?php
        break;
}

