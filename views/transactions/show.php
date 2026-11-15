<?php
/**
 * @var array $tx
 * @var array|null $owner
 * @var array|null $user
 */
$isSale = ($tx['reason'] === 'sale');
$isDebt = ($tx['payment_status'] === 'debt');
$isPaid = ($tx['payment_status'] === 'paid');
$subtotal = (float) $tx['subtotal'];
$dateFormatted = format_date_id($tx['movement_date']);
$timeFormatted = format_time($tx['movement_date']);
$invoiceNo = $tx['invoice_no'] ?: ('TRX-' . sprintf('%05d', $tx['movement_id']));

$cleanPhone = preg_replace('/[^\d]/', '', $tx['customer_phone'] ?? '');
if (str_starts_with($cleanPhone, '0')) {
    $waPhone = '62' . substr($cleanPhone, 1);
} else {
    $waPhone = $cleanPhone;
}
?>
<div class="flex-1 flex flex-col pb-24">
    <!-- Top Header & Navigation -->
    <header class="px-4 pt-6 pb-2">
        <div class="flex items-center space-x-3 mb-4">
            <a
                href="<?= url('/history') ?>"
                class="w-9 h-9 rounded-full bg-[#161B29] border border-[#232B3E] text-slate-400 hover:text-white flex items-center justify-center transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-white tracking-tight">Detail Transaksi</h1>
                <p class="text-xs text-slate-400">Rincian penjualan & bukti pembayaran</p>
            </div>
        </div>

        <!-- Hero Card: Total Nominal & Status -->
        <div class="bg-gradient-to-br from-[#161B29] to-[#0F1422] border <?= $isDebt ? 'border-amber-500/40 shadow-amber-950/20' : 'border-[#232B3E]' ?> rounded-3xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-slate-400 font-medium">Total Nominal</span>
                <?php if ($isSale): ?>
                    <?php if ($isDebt): ?>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-950/80 border border-amber-500/40 text-amber-300">
                            KASBON (BELUM LUNAS)
                        </span>
                    <?php else: ?>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-950/80 border border-emerald-500/40 text-emerald-300">
                            LUNAS (TUNAI)
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-800 border border-slate-700 text-slate-300">
                        <?= htmlspecialchars(strtoupper($tx['reason'] ?? 'MUTASI')) ?>
                    </span>
                <?php endif; ?>
            </div>

            <p class="text-2xl font-black <?= $isDebt ? 'text-amber-400' : 'text-emerald-400' ?> tabular-nums">
                <?= format_rupiah($subtotal) ?>
            </p>

            <div class="mt-3 pt-3 border-t border-[#232B3E]/60 flex items-center justify-between text-xs text-slate-400">
                <span class="font-mono text-[11px] text-slate-400">#<?= htmlspecialchars($invoiceNo) ?></span>
                <span><?= htmlspecialchars($dateFormatted) ?> · <?= htmlspecialchars($timeFormatted) ?> WIB</span>
            </div>
        </div>
    </header>

    <!-- Main Content Body -->
    <main class="px-4 py-2 space-y-3.5 flex-1">
        <!-- 1. Rincian Barang -->
        <div class="bg-[#161B29] border border-[#232B3E] rounded-2xl p-4 shadow-sm">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Rincian Barang</h3>
            <?php if (!empty($tx['items'])): ?>
                <div class="space-y-3">
                    <?php foreach ($tx['items'] as $item): ?>
                        <div class="flex items-center justify-between py-1 border-b border-[#232B3E]/60 last:border-b-0">
                            <div>
                                <h4 class="text-sm font-bold text-white"><?= htmlspecialchars($item['nama_produk'] ?? 'Barang') ?></h4>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    <span class="text-indigo-300 font-semibold"><?= (int) $item['jumlah'] ?> <?= htmlspecialchars($item['satuan'] ?? 'pcs') ?></span>
                                    <span> × </span>
                                    <span><?= format_rupiah($item['harga_satuan']) ?></span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-extrabold text-white tabular-nums"><?= format_rupiah($item['subtotal']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3 min-w-0 pr-3">
                        <?php if (!empty($tx['product_image'])): ?>
                            <img
                                src="<?= url('/uploads/products/' . $tx['product_image']) ?>"
                                alt="<?= htmlspecialchars($tx['product_name'] ?? 'Barang') ?>"
                                class="w-12 h-12 rounded-xl object-cover border border-[#232B3E] flex-shrink-0 shadow-inner"
                            >
                        <?php else: ?>
                            <div class="w-12 h-12 rounded-xl bg-[#0B0E17] border border-[#232B3E] text-[#6F7CF5] font-extrabold text-base flex items-center justify-center flex-shrink-0 shadow-inner">
                                <?= strtoupper(substr($tx['product_name'] ?? 'B', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="min-w-0">
                            <h4 class="text-base font-bold text-white truncate"><?= htmlspecialchars($tx['product_name'] ?? 'Barang') ?></h4>
                            <p class="text-xs text-slate-400 mt-0.5">
                                <span class="text-indigo-300 font-semibold"><?= (int) $tx['quantity'] ?> <?= htmlspecialchars($tx['unit'] ?? 'item') ?></span>
                                <span> × </span>
                                <span><?= format_rupiah($tx['unit_price']) ?></span>
                            </p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-extrabold text-white tabular-nums"><?= format_rupiah($subtotal) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 2. Informasi Pembeli -->
        <div class="bg-[#161B29] border border-[#232B3E] rounded-2xl p-4 shadow-sm">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Informasi Pembeli</h3>
            <?php if (!empty($tx['customer_name'])): ?>
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-bold text-white"><?= htmlspecialchars($tx['customer_name']) ?></span>
                            <?php if (!empty($tx['customer_code'])): ?>
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-indigo-950/80 text-indigo-300 border border-indigo-500/30">
                                    <?= htmlspecialchars($tx['customer_code']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($tx['customer_phone'])): ?>
                            <p class="text-xs text-slate-400 mt-1"><?= htmlspecialchars($tx['customer_phone']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($tx['customer_address'])): ?>
                            <p class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($tx['customer_address']) ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($waPhone)): ?>
                        <a
                            href="https://wa.me/<?= $waPhone ?>?text=Halo%20<?= urlencode($tx['customer_name']) ?>,%20ini%20bukti%20transaksi%20<?= urlencode($invoiceNo) ?>%20sebesar%20<?= urlencode(format_rupiah($subtotal)) ?>."
                            target="_blank"
                            rel="noopener"
                            class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold flex items-center space-x-1 shadow transition flex-shrink-0"
                        >
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                            </svg>
                            <span>WhatsApp</span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="flex items-center space-x-2">
                    <div class="w-7 h-7 rounded-lg bg-[#0B0E17] border border-[#232B3E] text-slate-400 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">Pembeli Umum</p>
                        <p class="text-[11px] text-slate-400">Transaksi tunai langsung (tanpa kasbon)</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 3. Informasi Lengkap Transaksi -->
        <div class="bg-[#161B29] border border-[#232B3E] rounded-2xl p-4 shadow-sm space-y-2.5 text-xs">
            <h3 class="font-bold text-slate-400 uppercase tracking-wider mb-1">Rincian Transaksi</h3>

            <div class="flex justify-between py-1 border-b border-[#232B3E]/60">
                <span class="text-slate-400">Nomor Transaksi</span>
                <span class="font-mono font-bold text-white"><?= htmlspecialchars($invoiceNo) ?></span>
            </div>

            <div class="flex justify-between py-1 border-b border-[#232B3E]/60">
                <span class="text-slate-400">Metode Pembayaran</span>
                <span class="font-bold <?= $isDebt ? 'text-amber-400' : 'text-emerald-400' ?>">
                    <?= $isDebt ? 'Kasbon (Bayar Belakangan)' : 'Tunai (Lunas)' ?>
                </span>
            </div>

            <div class="flex justify-between py-1 border-b border-[#232B3E]/60">
                <span class="text-slate-400">Waktu Transaksi</span>
                <span class="font-medium text-white"><?= htmlspecialchars($dateFormatted) ?> · <?= htmlspecialchars($timeFormatted) ?> WIB</span>
            </div>

            <?php if (!empty($tx['paid_at'])): ?>
                <div class="flex justify-between py-1 border-b border-[#232B3E]/60">
                    <span class="text-slate-400">Waktu Pelunasan</span>
                    <span class="font-bold text-emerald-400"><?= format_date_id($tx['paid_at']) ?> · <?= format_time($tx['paid_at']) ?> WIB</span>
                </div>
            <?php endif; ?>

            <div class="flex justify-between py-1 border-b border-[#232B3E]/60">
                <span class="text-slate-400">Status Kas</span>
                <span class="font-medium <?= !empty($tx['cash_entry_id']) ? 'text-emerald-400' : 'text-amber-400' ?>">
                    <?= !empty($tx['cash_entry_id']) ? 'Tercatat di Kas Masuk' : 'Belum Masuk Kas' ?>
                </span>
            </div>

            <?php if (!empty($tx['creator_name'])): ?>
                <div class="flex justify-between py-1 border-b border-[#232B3E]/60">
                    <span class="text-slate-400">Kasir / Pencatat</span>
                    <span class="font-medium text-white"><?= htmlspecialchars($tx['creator_name']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($tx['note'])): ?>
                <div class="pt-1">
                    <span class="text-slate-400 block mb-0.5">Catatan</span>
                    <p class="text-white italic bg-[#0B0E17] border border-[#232B3E] rounded-xl p-2.5">
                        "<?= htmlspecialchars($tx['note']) ?>"
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- 4. Tombol Aksi (Cetak Struk, Pelunasan, Kembali) -->
        <div class="space-y-2 pt-2">
            <!-- Tombol Cetak Struk (untuk transaksi penjualan) -->
            <?php if ($isSale): ?>
                <a
                    href="<?= url('/receipt/' . $tx['movement_id']) ?>"
                    target="_blank"
                    class="w-full py-3.5 px-4 bg-[#4B4EF0] hover:bg-[#3A3BE0] active:scale-[0.99] text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/30 transition min-h-[48px] flex items-center justify-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Struk Transaksi</span>
                </a>
            <?php endif; ?>

            <!-- Tombol Pelunasan Cepat jika masih Kasbon -->
            <?php if ($isDebt): ?>
                <form method="POST" action="<?= url('/kasbon/' . $tx['movement_id'] . '/settle') ?>" onsubmit="return confirm('Tandai kasbon sebesar <?= addslashes(format_rupiah($subtotal)) ?> ini sebagai LUNAS?')">
                    <?= csrf_field() ?>
                    <button
                        type="submit"
                        class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 active:scale-[0.99] text-white font-bold text-sm rounded-2xl shadow-lg shadow-emerald-900/30 transition min-h-[48px] flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Tandai Kasbon Lunas Sekarang</span>
                    </button>
                </form>
            <?php endif; ?>

            <!-- Tombol Kembali -->
            <a
                href="<?= url('/history') ?>"
                class="w-full py-3 px-4 bg-[#161B29] hover:bg-[#232B3E] active:scale-[0.99] text-slate-300 font-semibold text-xs rounded-2xl transition border border-[#232B3E] text-center min-h-[44px] flex items-center justify-center"
            >
                Kembali ke Riwayat
            </a>
        </div>
    </main>
</div>

