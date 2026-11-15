<?php
/**
 * @var array $supplier
 * @var array $supplies
 * @var array $user
 */
?>
<div class="flex-1 flex flex-col pb-24">
    <!-- Header -->
    <header class="px-4 pt-6 pb-4 border-b border-[#232B3E]">
        <div class="flex items-center space-x-3">
            <a
                href="<?= url('/suppliers') ?>"
                class="w-10 h-10 rounded-xl bg-[#161B29] border border-[#232B3E] text-slate-300 hover:text-white flex items-center justify-center transition"
                aria-label="Kembali"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-white leading-tight"><?= htmlspecialchars($supplier['nama_pemasok']) ?></h1>
                <p class="text-xs text-slate-400 mt-0.5">Detail & Riwayat Pasokan Kulakan</p>
            </div>
        </div>
    </header>

    <main class="px-4 py-4 flex-1 space-y-4">
        <!-- Supplier Profile Info Card -->
        <div class="bg-[#161B29] border border-[#232B3E] rounded-2xl p-4 space-y-2">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Informasi Kontak</div>
            <?php if (!empty($supplier['no_hp_pemasok'])): ?>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">WhatsApp / Telepon:</span>
                    <a href="https://wa.me/<?= preg_replace('/[^\d]/', '', $supplier['no_hp_pemasok']) ?>" target="_blank" class="text-indigo-400 font-semibold hover:underline flex items-center space-x-1">
                        <span><?= htmlspecialchars($supplier['no_hp_pemasok']) ?></span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            <?php endif; ?>
            <?php if (!empty($supplier['email_pemasok'])): ?>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Email:</span>
                    <span class="text-white"><?= htmlspecialchars($supplier['email_pemasok']) ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($supplier['alamat_pemasok'])): ?>
                <div class="flex items-start justify-between text-xs pt-1 border-t border-[#232B3E]/60">
                    <span class="text-slate-400 flex-shrink-0 mr-2">Alamat:</span>
                    <span class="text-white text-right"><?= htmlspecialchars($supplier['alamat_pemasok']) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- History of Supplies -->
        <div>
            <h2 class="text-sm font-bold text-white mb-3 flex items-center justify-between">
                <span>Riwayat Pasokan / Kulakan</span>
                <span class="text-xs text-slate-400 font-normal"><?= count($supplies) ?> kali pasokan</span>
            </h2>

            <?php if (empty($supplies)): ?>
                <div class="bg-[#161B29] border border-[#232B3E] rounded-2xl p-6 text-center text-xs text-slate-400">
                    Belum ada riwayat kulakan dari pemasok ini.
                </div>
            <?php else: ?>
                <div class="space-y-2.5">
                    <?php foreach ($supplies as $sup): ?>
                        <div class="bg-[#161B29] border border-[#232B3E] rounded-xl p-3.5 text-xs hover:border-[#323D57] transition">
                            <div class="flex items-start justify-between mb-1.5">
                                <div>
                                    <span class="font-bold text-white text-sm"><?= htmlspecialchars($sup['nama_produk']) ?></span>
                                    <span class="text-slate-400 text-xs ml-1">(<?= (int)$sup['jumlah'] ?> <?= htmlspecialchars($sup['satuan_produk']) ?>)</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-extrabold text-white text-sm"><?= format_rupiah($sup['total_harga']) ?></span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1.5 border-t border-[#232B3E]/60">
                                <div>
                                    Harga Beli: <?= format_rupiah($sup['harga_beli']) ?> / <?= htmlspecialchars($sup['satuan_produk']) ?>
                                </div>
                                <div>
                                    <?= date('d M Y, H:i', strtotime($sup['tanggal_suplai'])) ?>
                                </div>
                            </div>
                            <?php if (!empty($sup['catatan'])): ?>
                                <div class="text-[11px] text-slate-400 mt-1 italic">
                                    "<?= htmlspecialchars($sup['catatan']) ?>"
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
