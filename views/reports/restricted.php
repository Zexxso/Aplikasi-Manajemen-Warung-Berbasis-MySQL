<?php
/**
 * @var array $user
 */
?>
<div class="flex-1 flex flex-col pb-24">
    <!-- Top Gradient Header -->
    <header class="bg-gradient-to-br from-[#5356FB] via-[#4346EC] to-[#3437D8] pt-6 pb-6 px-4 rounded-b-[2rem] shadow-xl text-white">
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Laporan</h1>
        <p class="text-xs text-white/80 mt-1">Laporan Keuangan & Performa Warung</p>
    </header>

    <!-- Main Restricted Content -->
    <main class="px-4 py-6 flex-1 flex flex-col justify-center">
        <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-6 text-center shadow-xl">
            <!-- Lock Icon with Glow -->
            <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-amber-500/10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>

            <!-- Status Badge -->
            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-950/60 text-amber-300 border border-amber-500/30 mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5"></span>
                Halaman Khusus Pemilik Warung
            </div>

            <h2 class="text-lg font-extrabold text-white mb-2">Akses Tidak Tersedia</h2>

            <p class="text-xs text-slate-300 leading-relaxed max-w-[300px] mx-auto mb-5">
                Halo <strong class="text-white"><?= htmlspecialchars($user['name'] ?? 'Penjaga') ?></strong>, Anda masuk sebagai akun <strong class="text-indigo-300">Penjaga (Staff)</strong>. Informasi laba, rincian biaya, dan laporan bulanan hanya dapat diakses oleh <strong class="text-amber-300">Pemilik Warung</strong>.
            </p>

            <!-- What staff can do info box -->
            <div class="bg-[#0B0E17] border border-[#232B3E] rounded-2xl p-4 text-left text-xs mb-6 space-y-2">
                <p class="font-bold text-slate-200 mb-1">Fitur yang dapat Anda akses:</p>
                <div class="flex items-center space-x-2 text-slate-400">
                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Kelola stok barang & pantau sisa stok</span>
                </div>
                <div class="flex items-center space-x-2 text-slate-400">
                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Catat barang masuk, keluar, & kas harian</span>
                </div>
                <div class="flex items-center space-x-2 text-slate-400">
                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Lihat riwayat transaksi yang tercatat</span>
                </div>
            </div>

            <!-- Action Button -->
            <a
                href="<?= url('/dashboard') ?>"
                class="w-full min-h-[48px] py-3.5 px-4 bg-[#4B4EF0] hover:bg-[#3A3BE0] active:scale-[0.99] text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center space-x-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </main>
</div>

