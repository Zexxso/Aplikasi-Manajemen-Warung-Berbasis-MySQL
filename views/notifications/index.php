<?php
/**
 * @var array  $notifications
 * @var string $filter
 * @var int    $unreadCount
 */
$currentUserId = (int) (auth_user()['id'] ?? 0);

// Icon map per category
$iconMap = [
    'stok'      => ['color' => '#F59E0B', 'bg' => 'rgba(245,158,11,0.15)', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />'],
    'kasbon'    => ['color' => '#EF4444', 'bg' => 'rgba(239,68,68,0.15)',  'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'],
    'keuangan'  => ['color' => '#10B981', 'bg' => 'rgba(16,185,129,0.15)', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />'],
    'aktivitas' => ['color' => '#6366F1', 'bg' => 'rgba(99,102,241,0.15)', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'],
];

$tabs = [
    'semua'          => 'Semua',
    'stok'           => 'Stok',
    'kasbon_keuangan'=> 'Kasbon & Keuangan',
    'aktivitas'      => 'Aktivitas',
];
?>
<div class="flex-1 flex flex-col min-h-screen">

    <!-- Header -->
    <header class="bg-[#161926] border-b border-[rgba(255,255,255,0.08)] px-4 pt-5 pb-3 sticky top-0 z-20 flex items-center justify-between gap-2">
        <div class="flex items-center gap-3 min-w-0">
            <a href="<?= url('/dashboard') ?>" class="w-9 h-9 rounded-xl bg-[#0B0D14] border border-[rgba(255,255,255,0.08)] flex items-center justify-center flex-shrink-0 active:scale-95 transition">
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="min-w-0">
                <h1 class="text-base font-extrabold text-white leading-tight truncate">Notifikasi</h1>
                <?php if ($unreadCount > 0): ?>
                    <p class="text-[11px] text-amber-400 font-semibold"><?= $unreadCount ?> belum dibaca</p>
                <?php else: ?>
                    <p class="text-[11px] text-slate-500">Semua sudah dibaca</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($unreadCount > 0): ?>
            <form method="POST" action="<?= url('/notifications/read-all') ?>">
                <?= csrf_field() ?>
                <button
                    type="submit"
                    class="text-[11px] font-bold text-indigo-400 hover:text-indigo-300 py-1.5 px-3 bg-indigo-500/10 rounded-xl border border-indigo-500/20 active:scale-95 transition whitespace-nowrap"
                >
                    Tandai Semua
                </button>
            </form>
        <?php endif; ?>
    </header>

    <!-- Pill Tabs Filter -->
    <div class="px-4 py-3 overflow-x-auto no-scrollbar">
        <div class="flex gap-2 w-max">
            <?php foreach ($tabs as $key => $label): ?>
                <a
                    href="<?= url('/notifications?filter=' . $key) ?>"
                    class="px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap border transition active:scale-95
                        <?= $filter === $key
                            ? 'bg-[#3B38D0] border-[#3B38D0] text-white shadow-lg shadow-indigo-900/40'
                            : 'bg-[#161926] border-[rgba(255,255,255,0.08)] text-slate-400 hover:text-white hover:border-indigo-500/40'
                        ?>"
                >
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Notification List -->
    <main class="flex-1 px-4 pb-6 space-y-2">
        <?php if (empty($notifications)): ?>
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-2xl bg-[#161926] border border-[rgba(255,255,255,0.08)] flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-white">Tidak Ada Notifikasi</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-[220px]">
                    <?= $filter === 'semua'
                        ? 'Belum ada notifikasi. Notifikasi akan muncul otomatis saat ada aktivitas penting.'
                        : 'Tidak ada notifikasi untuk kategori ini.'
                    ?>
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif):
                $isRead    = (bool) $notif['sudah_dibaca'];
                $kategori  = $notif['kategori'] ?? 'aktivitas';
                $icon      = $iconMap[$kategori] ?? $iconMap['aktivitas'];
                $waktu     = format_waktu_relatif($notif['dibuat_pada'] ?? '');
                $notifId   = (int) $notif['id'];
            ?>
                <div class="relative rounded-2xl border transition overflow-hidden
                    <?= !$isRead
                        ? 'bg-[#161926] border-[rgba(255,255,255,0.10)]'
                        : 'bg-[#0F111A] border-[rgba(255,255,255,0.05)]'
                    ?>">

                    <?php if (!$isRead): ?>
                        <!-- Unread indicator strip -->
                        <div class="absolute left-0 top-3 bottom-3 w-0.5 rounded-full" style="background: <?= htmlspecialchars($icon['color']) ?>"></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= url('/notifications/' . $notifId . '/read') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="w-full text-left px-4 py-3.5 flex items-start gap-3 active:scale-[0.98] transition">

                            <!-- Icon -->
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5"
                                style="background: <?= htmlspecialchars($icon['bg']) ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    style="color: <?= htmlspecialchars($icon['color']) ?>">
                                    <?= $icon['svg'] ?>
                                </svg>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="text-[13px] font-bold leading-tight <?= !$isRead ? 'text-white' : 'text-slate-400' ?> line-clamp-1">
                                        <?= htmlspecialchars($notif['judul']) ?>
                                    </h4>
                                    <?php if (!$isRead): ?>
                                        <span class="w-2 h-2 rounded-full flex-shrink-0 mt-1" style="background: <?= htmlspecialchars($icon['color']) ?>"></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-2 leading-relaxed">
                                    <?= htmlspecialchars($notif['pesan']) ?>
                                </p>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold capitalize"
                                        style="background: <?= htmlspecialchars($icon['bg']) ?>; color: <?= htmlspecialchars($icon['color']) ?>">
                                        <?= htmlspecialchars(ucfirst($kategori)) ?>
                                    </span>
                                    <span class="text-[10px] text-slate-600"><?= htmlspecialchars($waktu) ?></span>
                                </div>
                            </div>

                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>
