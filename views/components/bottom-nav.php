<?php
$isDashboard = route_is('/dashboard');
$isProducts = route_is('/products*');
$isHistory = route_is('/history*');
$isReports = route_is('/reports*');
?>
<nav class="fixed bottom-0 left-0 right-0 z-30 max-w-[412px] mx-auto bg-[#0D111D]/95 backdrop-blur-md border-t border-[#1F2637] px-3 py-2 flex items-center justify-between">
    <!-- 1. Beranda -->
    <a
        href="<?= url('/dashboard') ?>"
        class="flex flex-col items-center justify-center flex-1 py-1 transition group <?= $isDashboard ? 'text-[#4B4EF0]' : 'text-slate-400 hover:text-slate-200' ?>"
    >
        <?php if ($isDashboard): ?>
            <!-- Solid Home Icon -->
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
        <?php else: ?>
            <!-- Outline Home Icon -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        <?php endif; ?>
        <span class="text-[11px] font-semibold mt-1">Beranda</span>
    </a>

    <!-- 2. Barang -->
    <a
        href="<?= url('/products') ?>"
        class="flex flex-col items-center justify-center flex-1 py-1 transition group <?= $isProducts ? 'text-[#4B4EF0]' : 'text-slate-400 hover:text-slate-200' ?>"
    >
        <?php if ($isProducts): ?>
            <!-- Solid Box Icon -->
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd" />
            </svg>
        <?php else: ?>
            <!-- Outline Box Icon -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        <?php endif; ?>
        <span class="text-[11px] font-semibold mt-1">Barang</span>
    </a>

    <!-- 3. Prominent "+ Catat" Button in Center -->
    <div class="flex-1 flex justify-center -mt-6">
        <button
            type="button"
            @click="$dispatch('open-record-sheet')"
            aria-label="Catat Transaksi"
            class="w-14 h-14 rounded-full bg-gradient-to-tr from-[#3A3BE0] via-[#4B4EF0] to-[#6F7CF5] text-white flex flex-col items-center justify-center shadow-lg shadow-indigo-600/50 hover:shadow-indigo-600/70 active:scale-95 transition transform border-4 border-[#0B0E17]"
        >
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
        </button>
    </div>

    <!-- 4. Riwayat -->
    <a
        href="<?= url('/history') ?>"
        class="flex flex-col items-center justify-center flex-1 py-1 transition group <?= $isHistory ? 'text-[#4B4EF0]' : 'text-slate-400 hover:text-slate-200' ?>"
    >
        <?php if ($isHistory): ?>
            <!-- Solid Clock Icon -->
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
        <?php else: ?>
            <!-- Outline Clock Icon -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        <?php endif; ?>
        <span class="text-[11px] font-semibold mt-1">Riwayat</span>
    </a>

    <!-- 5. Laporan -->
    <a
        href="<?= url('/reports') ?>"
        class="flex flex-col items-center justify-center flex-1 py-1 transition group <?= $isReports ? 'text-[#4B4EF0]' : 'text-slate-400 hover:text-slate-200' ?>"
    >
        <?php if ($isReports): ?>
            <!-- Solid Chart Icon -->
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
            </svg>
        <?php else: ?>
            <!-- Outline Chart Icon -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        <?php endif; ?>
        <span class="text-[11px] font-semibold mt-1">Laporan</span>
    </a>
</nav>

