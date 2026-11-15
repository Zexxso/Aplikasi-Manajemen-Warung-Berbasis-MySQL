<?php
/**
 * @var array $customer
 * @var array $transactions
 * @var int $totalOrders
 * @var int $totalItems
 * @var float $totalSpent
 * @var float $unpaidDebt
 * @var float $paidDebt
 */
$cleanPhone = preg_replace('/[^\d]/', '', $customer['telepon'] ?? $customer['phone'] ?? '');
if (str_starts_with($cleanPhone, '0')) {
    $waPhone = '62' . substr($cleanPhone, 1);
} else {
    $waPhone = $cleanPhone;
}
?>
<div class="flex-1 flex flex-col pb-24">
    <!-- Top Header -->
    <header class="px-4 pt-6 pb-2">
        <div class="flex items-center space-x-3 mb-4">
            <a
                href="<?= url('/customers') ?>"
                class="w-9 h-9 rounded-full bg-[#161B29] border border-[#232B3E] text-slate-400 hover:text-white flex items-center justify-center transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-white tracking-tight">Buku Kasbon</h1>
                <p class="text-xs text-slate-400">Detail catatan kasbon & riwayat pembelian</p>
            </div>
        </div>

        <!-- Customer Identity Card -->
        <div class="bg-gradient-to-br from-[#161B29] to-[#0F1422] border <?= $unpaidDebt > 0 ? 'border-amber-500/40 shadow-amber-950/20' : 'border-[#232B3E]' ?> rounded-3xl p-4 shadow-lg relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-1.5 flex-wrap gap-y-1">
                        <span class="px-2 py-0.5 rounded-md bg-indigo-950/90 border border-indigo-500/40 text-indigo-300 text-[10px] font-extrabold tracking-wider">
                            <?= htmlspecialchars($customer['kode'] ?? $customer['code']) ?>
                        </span>
                        <?php if ($unpaidDebt > 0): ?>
                            <span class="px-2 py-0.5 rounded-md bg-amber-950/90 border border-amber-500/40 text-amber-300 text-[10px] font-extrabold tracking-wider">
                                Sisa Kasbon: <?= format_rupiah($unpaidDebt) ?>
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-950/80 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold">
                                Semua Lunas
                            </span>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-lg font-extrabold text-white">
                        <?= htmlspecialchars($customer['nama'] ?? $customer['name']) ?>
                    </h2>
                    <?php if (!empty($customer['telepon'] ?? $customer['phone'])): ?>
                        <p class="text-xs text-indigo-300 mt-0.5 flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span><?= htmlspecialchars($customer['telepon'] ?? $customer['phone']) ?></span>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($customer['alamat'] ?? $customer['address'])): ?>
                        <p class="text-xs text-slate-400 mt-1 flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span><?= htmlspecialchars($customer['alamat'] ?? $customer['address']) ?></span>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($customer['catatan'] ?? $customer['note'])): ?>
                        <div class="mt-2 text-xs text-amber-300/90 bg-amber-950/30 border border-amber-500/20 rounded-xl px-2.5 py-1.5 inline-block">
                            📝 <?= htmlspecialchars($customer['catatan'] ?? $customer['note']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($waPhone)): ?>
                    <a
                        href="https://wa.me/<?= $waPhone ?>?text=Halo%20<?= urlencode($customer['nama'] ?? $customer['name']) ?>,%20mengingatkan%20catatan%20kasbon%20di%20warung%20sebesar%20<?= urlencode(format_rupiah($unpaidDebt)) ?>.%20Terima%20kasih."
                        target="_blank"
                        rel="noopener"
                        class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center space-x-1.5 shadow-md shadow-emerald-900/30 transition flex-shrink-0"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                        </svg>
                        <span>WhatsApp</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Stats Sub-Grid -->
            <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-[#232B3E]">
                <div>
                    <span class="text-[10px] text-amber-400/90 font-medium">Sisa Kasbon</span>
                    <p class="text-xs font-extrabold text-amber-400 tabular-nums mt-0.5"><?= format_rupiah($unpaidDebt) ?></p>
                </div>
                <div>
                    <span class="text-[10px] text-emerald-400/90 font-medium">Sudah Lunas</span>
                    <p class="text-xs font-extrabold text-emerald-400 tabular-nums mt-0.5"><?= format_rupiah($paidDebt) ?></p>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-medium">Total Belanja</span>
                    <p class="text-xs font-extrabold text-white tabular-nums mt-0.5"><?= format_rupiah($totalSpent) ?></p>
                </div>
            </div>
        </div>
    </header>

    <!-- Purchase Items List -->
    <main class="px-4 py-3 flex-1">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">
                Riwayat Kasbon & Pembelian (<?= count($transactions) ?>)
            </h3>
        </div>

        <?php if (empty($transactions)): ?>
            <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-8 text-center shadow-sm my-4">
                <div class="w-12 h-12 rounded-2xl bg-[#0B0E17] border border-[#232B3E] text-slate-500 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-white">Belum Ada Catatan Transaksi</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-[220px] mx-auto">
                    Pelanggan ini belum memiliki catatan kasbon atau pembelian.
                </p>
                <button
                    type="button"
                    @click="$dispatch('open-stock-out')"
                    class="mt-4 px-4 py-2 bg-[#4B4EF0] text-white text-xs font-bold rounded-xl shadow-md transition"
                >
                    + Catat Transaksi
                </button>
            </div>
        <?php else: ?>
            <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-2.5 shadow-sm divide-y divide-[#232B3E]/60">
                <?php foreach ($transactions as $tx): ?>
                    <?php
                    $productName = $tx['nama_produk'] ?? $tx['product_name'] ?? 'Barang Dihapus';
                    $productUnit = $tx['satuan'] ?? $tx['product_unit'] ?? 'pcs';
                    $qty = (int) ($tx['jumlah'] ?? $tx['quantity']);
                    $unitPrice = (float) ($tx['harga_satuan'] ?? $tx['unit_price']);
                    $subtotal = (float) $tx['subtotal'];
                    $movementDate = $tx['tanggal_mutasi'] ?? $tx['movement_date'];
                    $dateFormatted = format_date_id($movementDate);
                    $timeFormatted = format_time($movementDate);
                    $paymentStatus = $tx['status_pembayaran'] ?? $tx['payment_status'];
                    $isDebt = ($paymentStatus === 'debt');
                    $movementId = (int) ($tx['id_mutasi_stok'] ?? $tx['movement_id']);
                    $txNote = $tx['catatan'] ?? $tx['note'] ?? '';
                    ?>
                    <div class="p-3 hover:bg-[#1E2538]/40 rounded-2xl transition">
                        <div class="flex items-start justify-between">
                            <div class="min-w-0 pr-2">
                                <h4 class="text-sm font-bold text-white leading-tight truncate">
                                    <?= htmlspecialchars($productName) ?>
                                </h4>
                                <div class="flex items-center space-x-2 text-xs text-slate-400 mt-1 flex-wrap">
                                    <span class="font-bold text-indigo-300"><?= $qty ?> <?= htmlspecialchars($productUnit) ?></span>
                                    <span>·</span>
                                    <span>@ <?= format_rupiah($unitPrice) ?></span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-extrabold <?= $isDebt ? 'text-amber-400' : 'text-emerald-400' ?> tabular-nums">
                                    <?= format_rupiah($subtotal) ?>
                                </p>
                                <?php if ($isDebt): ?>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-950/70 text-amber-300 border border-amber-500/30">
                                        KASBON
                                    </span>
                                <?php else: ?>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-950/60 text-emerald-400 border border-emerald-500/20">
                                        Lunas
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Date, Time, Actions & Note -->
                        <div class="mt-2.5 pt-2 border-t border-[#232B3E]/40 flex items-center justify-between text-[11px] text-slate-400 flex-wrap gap-2">
                            <div class="flex items-center space-x-1.5 text-slate-400">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span><?= htmlspecialchars($dateFormatted) ?> · <?= htmlspecialchars($timeFormatted) ?> WIB</span>
                                <?php if (!empty($txNote)): ?>
                                    <span class="italic text-slate-500 truncate max-w-[120px]">
                                        "<?= htmlspecialchars($txNote) ?>"
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons: Tandai Lunas & Detail Transaksi -->
                            <div class="flex items-center space-x-1.5">
                                <?php if ($isDebt): ?>
                                    <form method="POST" action="<?= url('/kasbon/' . $movementId . '/settle') ?>" onsubmit="return confirm('Tandai kasbon <?= addslashes(htmlspecialchars($productName)) ?> sebesar <?= format_rupiah($subtotal) ?> ini sebagai LUNAS?')">
                                        <?= csrf_field() ?>
                                        <button
                                            type="submit"
                                            class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] rounded-lg shadow transition flex items-center space-x-1"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Tandai Lunas</span>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <a
                                    href="<?= url('/transactions/' . $movementId) ?>"
                                    class="px-2 py-1 bg-[#161B29] hover:bg-[#232B3E] text-slate-300 hover:text-white border border-[#232B3E] font-semibold text-[10px] rounded-lg transition flex items-center space-x-1"
                                    title="Lihat Detail Transaksi"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>Detail</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

