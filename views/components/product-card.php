<?php
/**
 * @var array $product
 */
$prodName = $product['nama'] ?? $product['name'] ?? 'Barang';
$prodStock = (int) ($product['stok'] ?? $product['stock'] ?? 0);
$prodMinStock = (int) ($product['stok_minimum'] ?? $product['min_stock'] ?? 5);
$prodUnit = $product['satuan'] ?? $product['unit'] ?? 'pcs';
$prodSellPrice = (float) ($product['harga_jual'] ?? $product['sell_price'] ?? 0);
$prodBuyPrice = (float) ($product['harga_beli'] ?? $product['buy_price'] ?? 0);
$prodCatId = $product['id_kategori'] ?? $product['category_id'] ?? '';
$initial = strtoupper(substr($prodName, 0, 1));
$isLow = $prodStock <= $prodMinStock;
$categoryName = $product['nama_kategori'] ?? $product['category_name'] ?? 'Tanpa Kategori';
?>
<div
    class="bg-[#161B29] hover:bg-[#1C2335] active:scale-[0.99] border border-[#232B3E] rounded-2xl p-3.5 mb-2.5 transition flex items-center justify-between cursor-pointer shadow-sm group"
    @click="$dispatch('open-edit-product', <?= htmlspecialchars(json_encode([
        'id' => (int) $product['id'],
        'name' => $prodName,
        'category_id' => $prodCatId,
        'unit' => $prodUnit,
        'buy_price' => $prodBuyPrice,
        'sell_price' => $prodSellPrice,
        'stock' => $prodStock,
        'min_stock' => $prodMinStock,
        'update_url' => url('/products/' . $product['id']),
        'delete_url' => url('/products/' . $product['id']),
    ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>)"
>
    <div class="flex items-center space-x-3.5 flex-1 min-w-0 pr-3">
        <!-- Letter Avatar Square -->
        <div class="w-11 h-11 rounded-2xl bg-[#1E2436] border border-indigo-500/20 text-[#6F7CF5] font-extrabold text-base flex items-center justify-center flex-shrink-0 shadow-inner group-hover:scale-105 transition">
            <?= htmlspecialchars($initial) ?>
        </div>

        <!-- Name, Category, Stock & Alert Badge -->
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-bold text-white truncate"><?= htmlspecialchars($prodName) ?></h4>
            <p class="text-xs text-slate-400 mt-0.5 truncate">
                <?= htmlspecialchars($categoryName) ?> · Stok <?= $prodStock ?> <?= htmlspecialchars($prodUnit) ?>
            </p>

            <?php if ($isLow): ?>
                <div class="mt-1">
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md bg-[#33220E] border border-amber-500/40 text-amber-400 text-[10px] font-bold">
                        <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Stok Menipis</span>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sell Price & Chevron -->
    <div class="text-right flex-shrink-0 flex items-center space-x-1.5">
        <div>
            <p class="text-sm font-extrabold text-white tabular-nums">
                <?= format_rupiah($prodSellPrice) ?>
            </p>
        </div>
        <svg class="w-4 h-4 text-slate-500 group-hover:text-slate-300 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </div>
</div>

