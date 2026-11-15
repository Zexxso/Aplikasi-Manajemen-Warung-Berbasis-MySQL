<?php
/**
 * @var string $slot
 * @var string|null $title
 * @var array|null $user
 * @var array $globalProducts
 * @var array $globalCashCategories
 * @var array $globalProductCategories
 */
$currentUser = $user ?? auth_user();
$statusMessage = flash('status');
$infoMessage = flash('info');
$errorMessage = flash('error');
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token()) ?>">

    <title><?= htmlspecialchars($title ?? 'Warungku') ?></title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: '#4B4EF0',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body
    class="h-full bg-slate-950 text-slate-100 font-sans antialiased selection:bg-[#4B4EF0] selection:text-white"
    x-data="{
        recordSheetOpen: false,
        profileSheetOpen: false,
        toast: {
            show: false,
            message: '',
            type: 'status',
            timer: null
        },
        showToast(msg, type = 'status') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            clearTimeout(this.toast.timer);
            this.toast.timer = setTimeout(() => {
                this.toast.show = false;
            }, type === 'error' ? 5000 : 2500);
        },
        openSheet(sheet) {
            this[sheet] = true;
            history.pushState({ sheetOpen: sheet }, '');
        },
        closeSheet(sheet) {
            this[sheet] = false;
        }
    }"
    @open-record-sheet.window="openSheet('recordSheetOpen')"
    @open-profile-sheet.window="openSheet('profileSheetOpen')"
    @popstate.window="
        if (recordSheetOpen) {
            recordSheetOpen = false;
        } else if (profileSheetOpen) {
            profileSheetOpen = false;
        }
    "
    x-init="
        <?php if ($errorMessage): ?>
            showToast(<?= json_encode($errorMessage, JSON_UNESCAPED_UNICODE) ?>, 'error');
        <?php elseif ($statusMessage): ?>
            showToast(<?= json_encode($statusMessage, JSON_UNESCAPED_UNICODE) ?>, 'success');
        <?php elseif ($infoMessage): ?>
            showToast(<?= json_encode($infoMessage, JSON_UNESCAPED_UNICODE) ?>, 'info');
        <?php endif; ?>
    "
>
    <!-- Mobile Screen Wrapper (Max 412px for Android WebView) -->
    <div class="w-full max-w-[412px] min-h-screen mx-auto bg-[#0B0E17] flex flex-col relative shadow-2xl overflow-x-hidden border-x border-slate-900/60 pb-20">
        <?= $slot ?? '' ?>

        <!-- Bottom Navigation -->
        <?php include __DIR__ . '/../components/bottom-nav.php'; ?>

        <!-- Floating Toast Notification (Above bottom nav) -->
        <div
            x-show="toast.show"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-3"
            :class="toast.type === 'error' ? 'border-rose-500/50 bg-[#1F1317]' : 'border-[#232B3E] bg-[#161B29]/95'"
            class="fixed bottom-20 left-4 right-4 max-w-[380px] mx-auto z-50 border text-white px-4 py-3 rounded-2xl shadow-xl flex items-center space-x-3 pointer-events-none"
        >
            <div
                :class="toast.type === 'error' ? 'bg-rose-500' : 'bg-[#4B4EF0]'"
                class="w-2.5 h-2.5 rounded-full animate-pulse flex-shrink-0"
            ></div>
            <p
                :class="toast.type === 'error' ? 'text-rose-200' : 'text-slate-100'"
                class="text-xs font-semibold flex-1"
                x-text="toast.message"
            ></p>
        </div>

        <!-- Transaction Sheets (+ Catat: 2x2 Grid, Stock In, Stock Out, Cash, Picker) -->
        <?php include __DIR__ . '/../components/record-sheets.php'; ?>

        <!-- Bottom Sheet: User Profile / Logout -->
        <div
            x-show="profileSheetOpen"
            x-cloak
            class="fixed inset-0 z-40 flex items-end justify-center"
        >
            <div
                x-show="profileSheetOpen"
                x-transition:enter="transition-opacity ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="closeSheet('profileSheetOpen')"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm"
            ></div>

            <div
                x-show="profileSheetOpen"
                x-transition:enter="transition ease-out duration-250 transform"
                x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full"
                class="relative w-full max-w-[412px] bg-[#161B29] border-t border-[#232B3E] rounded-t-3xl p-6 z-50 shadow-2xl"
            >
                <div class="w-12 h-1.5 bg-slate-700 rounded-full mx-auto mb-5"></div>
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-[#3A3BE0] to-[#6F7CF5] text-white text-xl font-bold flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <?= htmlspecialchars(strtoupper(substr($currentUser['nama'] ?? $currentUser['name'] ?? 'User', 0, 2))) ?>
                    </div>
                    <h3 class="text-base font-bold text-white"><?= htmlspecialchars($currentUser['nama_toko'] ?? $currentUser['shop_name'] ?? 'Warung Bu Sari') ?></h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        <?= htmlspecialchars($currentUser['nama'] ?? $currentUser['name'] ?? '') ?>
                        <?php if (($currentUser['peran'] ?? $currentUser['role'] ?? '') === 'staff'): ?>
                            <span class="inline-block ml-1 px-2 py-0.5 rounded-full text-[10px] bg-indigo-900/60 text-indigo-300 font-bold border border-indigo-500/30">Penjaga</span>
                        <?php else: ?>
                            <span class="inline-block ml-1 px-2 py-0.5 rounded-full text-[10px] bg-amber-900/60 text-amber-300 font-bold border border-amber-500/30">Pemilik</span>
                        <?php endif; ?>
                    </p>
                    <p class="text-[11px] text-slate-500 mt-0.5"><?= htmlspecialchars($currentUser['telepon'] ?? $currentUser['phone'] ?: ($currentUser['email'] ?? '')) ?></p>
                </div>

                <!-- Link to Kelola Pembeli (Pelanggan) -->
                <a
                    href="<?= url('/customers') ?>"
                    class="w-full min-h-[48px] px-4 py-3 bg-[#0B0E17] hover:bg-[#1E2538] border border-[#232B3E] hover:border-indigo-500/40 text-white font-bold text-sm rounded-xl transition flex items-center justify-between mb-3 shadow-sm"
                >
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span>Kelola Pembeli (Langganan)</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <?php if (is_owner()): ?>
                    <a
                        href="<?= url('/staff') ?>"
                        class="w-full min-h-[48px] px-4 py-3 bg-[#0B0E17] hover:bg-[#1E2538] border border-[#232B3E] hover:border-indigo-500/40 text-white font-bold text-sm rounded-xl transition flex items-center justify-between mb-3 shadow-sm"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <span>Kelola Karyawan</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                <?php endif; ?>

                <form method="POST" action="<?= url('/logout') ?>">
                    <?= csrf_field() ?>
                    <button
                        type="submit"
                        class="w-full min-h-[48px] px-4 py-3 bg-rose-600/20 hover:bg-rose-600/30 border border-rose-500/30 text-rose-300 font-bold text-sm rounded-xl transition flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar dari Aplikasi</span>
                    </button>
                </form>

                <div class="mt-3">
                    <button
                        type="button"
                        @click="closeSheet('profileSheetOpen')"
                        class="w-full py-3 bg-[#0B0E17] border border-[#232B3E] text-slate-300 font-semibold text-xs rounded-xl hover:bg-slate-900 transition"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

