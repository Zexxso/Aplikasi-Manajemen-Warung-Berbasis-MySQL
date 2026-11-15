<?php
/**
 * @var array $user
 * @var string $greeting
 * @var string $period
 * @var array $summary
 * @var int $lowStockCount
 * @var array $recentTransactions
 */
?>
<div class="flex-1 flex flex-col">
    <!-- Top Gradient Header -->
    <header class="bg-gradient-to-br from-[#5356FB] via-[#4346EC] to-[#3437D8] pt-6 pb-6 px-4 rounded-b-[2rem] shadow-xl text-white relative">
        <!-- Top App Bar (User Profile & Notification) -->
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center space-x-3 cursor-pointer" @click="$dispatch('open-profile-sheet')">
                <!-- User Avatar Pill with initials -->
                <div class="w-10 h-10 rounded-full bg-white/20 border border-white/30 text-white font-bold text-sm flex items-center justify-center shadow-sm">
                    <?= htmlspecialchars(strtoupper(substr($user['name'] ?? 'User', 0, 2))) ?>
                </div>
                <div>
                    <p class="text-xs text-white/80 font-medium leading-none"><?= htmlspecialchars($greeting) ?></p>
                    <h1 class="text-base font-extrabold text-white mt-1 leading-tight flex items-center space-x-1">
                        <span><?= htmlspecialchars($user['shop_name'] ?? $user['name'] ?? 'Warungku') ?></span>
                        <svg class="w-3.5 h-3.5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </h1>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <!-- Supplier List Shortcut -->
                <a
                    href="<?= url('/suppliers') ?>"
                    title="Pemasok & Kulakan"
                    aria-label="Pemasok & Kulakan"
                    class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center transition text-white"
                >
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </a>

                <!-- Customer List Shortcut -->
                <a
                    href="<?= url('/customers') ?>"
                    title="Daftar Pembeli"
                    aria-label="Daftar Pembeli"
                    class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center transition text-white"
                >
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </a>

                <!-- Notification Bell with Alert Indicator -->
                <button
                    type="button"
                    @click="showToast('Tidak ada notifikasi baru')"
                    aria-label="Notifikasi"
                    class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center transition relative"
                >
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <?php if ($lowStockCount > 0): ?>
                        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-amber-400 ring-2 ring-[#4346EC]"></span>
                    <?php endif; ?>
                </button>
            </div>
        </div>

        <!-- Time Filter Pills (Hari ini | Minggu | Bulan) -->
        <div class="bg-black/25 p-1 rounded-full flex items-center justify-between mb-5 border border-white/10 max-w-[340px] mx-auto">
            <a
                href="<?= url('/dashboard?period=today') ?>"
                class="flex-1 py-1.5 text-center text-xs rounded-full font-bold transition <?= $period === 'today' ? 'bg-white text-[#3A3BE0] shadow-md' : 'text-white/80 hover:text-white' ?>"
            >
                Hari ini
            </a>
            <a
                href="<?= url('/dashboard?period=week') ?>"
                class="flex-1 py-1.5 text-center text-xs rounded-full font-bold transition <?= $period === 'week' ? 'bg-white text-[#3A3BE0] shadow-md' : 'text-white/80 hover:text-white' ?>"
            >
                Minggu
            </a>
            <a
                href="<?= url('/dashboard?period=month') ?>"
                class="flex-1 py-1.5 text-center text-xs rounded-full font-bold transition <?= $period === 'month' ? 'bg-white text-[#3A3BE0] shadow-md' : 'text-white/80 hover:text-white' ?>"
            >
                Bulan
            </a>
        </div>

        <!-- Main Profit Card -->
        <div class="mb-4">
            <p class="text-xs font-semibold text-white/80">Laba <?= htmlspecialchars($summary['period_label']) ?></p>
            <div class="flex items-baseline space-x-2 mt-0.5">
                <h2 class="text-3xl font-extrabold tracking-tight tabular-nums text-white">
                    <?= $summary['profit'] < 0 ? '-' : '' ?><?= format_rupiah(abs($summary['profit'])) ?>
                </h2>
            </div>
            <p class="text-xs text-white/70 font-medium mt-1">
                <?= htmlspecialchars($summary['comparison_text']) ?>
            </p>
        </div>

        <!-- Pemasukan & Pengeluaran Sub-Cards -->
        <div class="grid grid-cols-2 gap-3">
            <!-- Pemasukan -->
            <div class="bg-[#0B0E17]/85 border border-white/10 rounded-2xl p-3.5 shadow-inner">
                <div class="flex items-center space-x-1.5 mb-1 text-slate-400">
                    <div class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-slate-300">Pemasukan</span>
                </div>
                <p class="text-base font-extrabold text-emerald-400 tabular-nums">
                    <?= format_rupiah($summary['income']) ?>
                </p>
            </div>

            <!-- Pengeluaran -->
            <div class="bg-[#0B0E17]/85 border border-white/10 rounded-2xl p-3.5 shadow-inner">
                <div class="flex items-center space-x-1.5 mb-1 text-slate-400">
                    <div class="w-5 h-5 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-slate-300">Pengeluaran</span>
                </div>
                <p class="text-base font-extrabold text-rose-400 tabular-nums">
                    <?= format_rupiah($summary['expense']) ?>
                </p>
            </div>
        </div>
    </header>

    <!-- Main Body Content -->
    <main class="px-4 py-4 space-y-4">
        <!-- Warning Banner: Stok Menipis (PRD Section 1 & 7) -->
        <?php if ($lowStockCount > 0): ?>
            <a
                href="<?= url('/products?filter=low_stock') ?>"
                class="block bg-[#241A0B] border border-[#854D0E]/60 rounded-2xl p-3.5 shadow-md hover:bg-[#2C200E] active:scale-[0.99] transition group"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white flex items-center space-x-1.5">
                                <span><?= $lowStockCount ?> barang stok menipis</span>
                            </h3>
                            <p class="text-xs text-amber-300/80 mt-0.5">Ketuk untuk lihat dan kulakan</p>
                        </div>
                    </div>
                    <div class="text-slate-400 group-hover:text-white transition transform group-hover:translate-x-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <!-- Quick Action Buttons (Barang Masuk, Barang Keluar, Catat Uang, Data Pembeli) -->
        <div class="grid grid-cols-4 gap-2">
            <!-- Barang Masuk -->
            <button
                type="button"
                @click="$dispatch('open-stock-in')"
                class="bg-[#161B29] hover:bg-[#1E2538] active:scale-95 border border-[#232B3E] rounded-2xl p-2 flex flex-col items-center justify-center min-h-[82px] shadow-sm transition text-center"
            >
                <div class="w-8 h-8 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-400 flex items-center justify-center mb-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </div>
                <span class="text-[11px] font-bold text-white leading-tight">Barang Masuk</span>
            </button>

            <!-- Barang Keluar -->
            <button
                type="button"
                @click="$dispatch('open-stock-out')"
                class="bg-[#161B29] hover:bg-[#1E2538] active:scale-95 border border-[#232B3E] rounded-2xl p-2 flex flex-col items-center justify-center min-h-[82px] shadow-sm transition text-center"
            >
                <div class="w-8 h-8 rounded-xl bg-indigo-950/60 border border-indigo-500/30 text-indigo-400 flex items-center justify-center mb-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                </div>
                <span class="text-[11px] font-bold text-white leading-tight">Barang Keluar</span>
            </button>

            <!-- Catat Uang -->
            <button
                type="button"
                @click="$dispatch('open-cash', { type: 'expense' })"
                class="bg-[#161B29] hover:bg-[#1E2538] active:scale-95 border border-[#232B3E] rounded-2xl p-2 flex flex-col items-center justify-center min-h-[82px] shadow-sm transition text-center"
            >
                <div class="w-8 h-8 rounded-xl bg-amber-950/60 border border-amber-500/30 text-amber-400 flex items-center justify-center mb-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <span class="text-[11px] font-bold text-white leading-tight">Catat Uang</span>
            </button>

            <!-- Buku Kasbon -->
            <a
                href="<?= url('/customers') ?>"
                class="bg-[#161B29] hover:bg-[#1E2538] active:scale-95 border border-[#232B3E] rounded-2xl p-2 flex flex-col items-center justify-center min-h-[82px] shadow-sm transition text-center"
            >
                <div class="w-8 h-8 rounded-xl bg-amber-950/60 border border-amber-500/30 text-amber-400 flex items-center justify-center mb-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <span class="text-[11px] font-bold text-white leading-tight">Kasbon</span>
            </a>
        </div>

        <!-- Transaksi Terbaru Section -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-extrabold text-white">Transaksi Terbaru</h3>
                <a
                    href="<?= url('/history') ?>"
                    class="text-xs font-semibold text-[#6F7CF5] hover:text-indigo-300 transition"
                >
                    Lihat semua
                </a>
            </div>

            <?php if (empty($recentTransactions)): ?>
                <!-- Empty State -->
                <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-8 text-center shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-[#0B0E17] border border-[#232B3E] text-slate-500 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-white">Belum Ada Transaksi</h4>
                    <p class="text-xs text-slate-400 mt-1 max-w-[220px] mx-auto">
                        Catat transaksi barang masuk, keluar, atau kas untuk melihat ringkasan.
                    </p>
                    <button
                        type="button"
                        @click="$dispatch('open-record-sheet')"
                        class="mt-4 px-4 py-2 bg-[#4B4EF0] text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/30"
                    >
                        + Catat Transaksi Pertama
                    </button>
                </div>
            <?php else: ?>
                <!-- Transaction List Container -->
                <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-2.5 shadow-sm divide-y divide-[#232B3E]/60">
                    <?php foreach ($recentTransactions as $tx): ?>
                        <?php include __DIR__ . '/../components/transaction-item.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

