<?php
/**
 * @var array $tx
 */
$isIncome = ($tx['jenis'] ?? $tx['type'] ?? '') === 'income';
$productName = $tx['nama_produk'] ?? $tx['product_name'] ?? null;
$productUnit = $tx['satuan'] ?? $tx['unit'] ?? 'item';
$movementQuantity = $tx['jumlah'] ?? $tx['quantity'] ?? null;
$movementType = $tx['jenis_mutasi'] ?? $tx['stock_type'] ?? null;
$customerName = $tx['nama_pelanggan'] ?? $tx['customer_name'] ?? null;
$categoryName = $tx['nama_kategori'] ?? $tx['category_name'] ?? 'Kas';
$entryDate = $tx['tanggal_transaksi'] ?? $tx['entry_date'] ?? '';
$amount = (float) ($tx['nominal'] ?? $tx['amount'] ?? 0);
$note = $tx['catatan'] ?? $tx['note'] ?? '';

// Title
if (!empty($productName)) {
    $itemTitle = $productName;
} elseif (!empty($note)) {
    $itemTitle = $note;
} else {
    $itemTitle = $categoryName;
}

// Subtitle
$timeStr = !empty($entryDate) ? format_time($entryDate) : '';
if (!empty($productName) && $movementQuantity !== null) {
    $sign = $movementType === 'out' ? '-' : '+';
    $subtitle = "{$sign}{$movementQuantity} {$productUnit} · {$timeStr}";
} else {
    $subtitle = $timeStr;
}
?>
<div class="flex items-center justify-between p-2.5 rounded-2xl hover:bg-[#1E2538]/50 transition group">
    <div class="flex items-center space-x-3 min-w-0 pr-2">
        <!-- Direction Icon (Income / Expense) -->
        <div class="w-9 h-9 rounded-xl <?= $isIncome ? 'bg-emerald-950/60 border border-emerald-500/30 text-emerald-400' : 'bg-rose-950/60 border border-rose-500/30 text-rose-400' ?> flex items-center justify-center flex-shrink-0">
            <?php if ($isIncome): ?>
                <!-- Green Arrow Down-Left (Pemasukan) -->
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            <?php else: ?>
                <!-- Red Arrow Up-Right (Pengeluaran) -->
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
            <?php endif; ?>
        </div>

        <div class="min-w-0">
            <h4 class="text-sm font-bold text-white truncate"><?= htmlspecialchars($itemTitle) ?></h4>
            <div class="flex items-center space-x-1.5 mt-0.5 flex-wrap">
                <span class="text-xs text-slate-400"><?= htmlspecialchars($subtitle) ?></span>
                <?php if (!empty($customerName)): ?>
                    <span class="inline-flex items-center space-x-0.5 px-1.5 py-0.2 rounded text-[10px] font-semibold bg-indigo-950/70 text-indigo-300 border border-indigo-500/30">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="truncate max-w-[100px]"><?= htmlspecialchars($customerName) ?></span>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="text-right">
        <p class="text-sm font-bold tabular-nums <?= $isIncome ? 'text-emerald-400' : 'text-rose-400' ?>">
            <?= $isIncome ? '+' : '-' ?><?= format_rupiah($amount) ?>
        </p>
        <p class="text-xs text-slate-400 mt-0.5">
            <?= htmlspecialchars($categoryName) ?>
        </p>
    </div>
</div>
