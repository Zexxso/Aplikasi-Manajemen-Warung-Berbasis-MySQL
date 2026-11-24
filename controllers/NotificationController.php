<?php

require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../helpers/notifications.php';

class NotificationController
{
    public function index(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        $user     = auth_user();
        $userId   = (int) $user['id'];
        $filter   = $_GET['filter'] ?? 'semua';

        $validFilters = ['semua', 'stok', 'kasbon_keuangan', 'aktivitas'];
        if (!in_array($filter, $validFilters, true)) {
            $filter = 'semua';
        }

        $notifications = ambil_daftar_notifikasi($filter, $userId);
        $unreadCount   = hitung_notif_belum_dibaca($userId);

        view('notifications.index', [
            'title'         => 'Notifikasi - Warungku',
            'notifications' => $notifications,
            'filter'        => $filter,
            'unreadCount'   => $unreadCount,
        ]);
    }

    public function markRead(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            redirect('/notifications');
        }

        $user = auth_user();
        $uri  = current_uri();

        // Parse ID from URI: /notifications/{id}/read
        if (preg_match('#^/notifications/(\d+)/read$#', $uri, $m)) {
            $id = (int) $m[1];
            tandai_notif_dibaca($id);

            // Redirect ke target_url notifikasi jika ada
            $stmt = db()->prepare("SELECT target_url FROM notifikasi WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $notif = $stmt->fetch(PDO::FETCH_ASSOC);
            $targetUrl = !empty($notif['target_url']) ? $notif['target_url'] : '/notifications';
            redirect($targetUrl);
        }

        redirect('/notifications');
    }

    public function markAllRead(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            redirect('/notifications');
        }

        $user   = auth_user();
        $userId = (int) $user['id'];
        tandai_semua_notif_dibaca($userId);
        set_flash('status', 'Semua notifikasi telah ditandai sebagai sudah dibaca.');
        redirect('/notifications');
    }

    public function badge(): void
    {
        if (!auth_check()) {
            json_response(['count' => 0], 200);
        }

        $user    = auth_user();
        $userId  = (int) $user['id'];
        $count   = hitung_notif_belum_dibaca($userId);
        json_response(['count' => $count]);
    }
}
