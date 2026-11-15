<?php
/**
 * @var array $receipt
 * @var array|null $owner
 * @var array|null $user
 */
$isKasbon = ($receipt['payment_status'] === 'debt');
$isPaid = ($receipt['payment_status'] === 'paid');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Struk Transaksi') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; margin: 0 !important; }
            #receipt-box { border: none !important; box-shadow: none !important; width: 100% !important; max-width: 320px !important; margin: 0 auto !important; }
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-900 flex flex-col items-center justify-center min-h-screen p-4">
    <div id="receipt-box" class="w-full max-w-[340px] bg-white rounded-3xl p-6 shadow-2xl font-mono text-xs space-y-4">
        <!-- Shop Header -->
        <div class="text-center pb-3 border-b border-dashed border-slate-400">
            <h1 class="text-lg font-black tracking-wider uppercase text-slate-900"><?= htmlspecialchars($owner['shop_name'] ?? $user['shop_name'] ?? 'WARUNGKU') ?></h1>
            <p class="text-[10px] text-slate-600 mt-0.5">STRUK BUKTI PENJUALAN RESMI</p>
            <?php if (!empty($owner['phone'])): ?>
                <p class="text-[10px] text-slate-500 mt-0.5">Telp/WA: <?= htmlspecialchars($owner['phone']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Receipt Meta -->
        <div class="text-[11px] space-y-1.5 py-1 border-b border-dashed border-slate-400">
            <div class="flex justify-between">
                <span class="text-slate-600">No. Transaksi:</span>
                <span class="font-extrabold text-slate-900"><?= htmlspecialchars($receipt['invoice_no'] ?: ('TRX-' . sprintf('%05d', $receipt['movement_id']))) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Waktu:</span>
                <span class="font-medium text-slate-800"><?= format_date_id($receipt['movement_date']) ?> · <?= format_time($receipt['movement_date']) ?> WIB</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Kasir:</span>
                <span class="font-medium text-slate-800"><?= htmlspecialchars($user['name'] ?? 'Kasir') ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Pembeli:</span>
                <span class="font-bold <?= $isKasbon ? 'text-amber-800' : 'text-slate-900' ?>">
                    <?= !empty($receipt['customer_name']) ? htmlspecialchars($receipt['customer_name']) : 'Pembeli Umum (Tunai)' ?>
                </span>
            </div>
            <div class="flex justify-between items-center pt-0.5">
                <span class="text-slate-600">Status Bayar:</span>
                <?php if ($isKasbon): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300">
                        KASBON (BELUM LUNAS)
                    </span>
                <?php else: ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-900 border border-emerald-300">
                        LUNAS (TUNAI)
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items Table -->
        <div class="py-2 border-b border-dashed border-slate-400 space-y-2">
            <?php if (!empty($receipt['items'])): ?>
                <?php foreach ($receipt['items'] as $item): ?>
                    <div class="text-[11px]">
                        <div class="font-extrabold text-slate-900"><?= htmlspecialchars($item['nama_produk'] ?? 'Barang') ?></div>
                        <div class="flex justify-between text-slate-700">
                            <span><?= (int)$item['jumlah'] ?> <?= htmlspecialchars($item['satuan'] ?? 'pcs') ?> x <?= format_rupiah($item['harga_satuan']) ?></span>
                            <span class="font-bold text-slate-900"><?= format_rupiah($item['subtotal']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-[11px]">
                    <div class="font-extrabold text-slate-900"><?= htmlspecialchars($receipt['product_name'] ?? 'Barang') ?></div>
                    <div class="flex justify-between text-slate-700">
                        <span><?= (int)($receipt['quantity'] ?? 1) ?> <?= htmlspecialchars($receipt['unit'] ?? 'item') ?> x <?= format_rupiah($receipt['unit_price'] ?? $receipt['subtotal']) ?></span>
                        <span class="font-bold text-slate-900"><?= format_rupiah($receipt['subtotal']) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($receipt['note'])): ?>
                <p class="text-[10px] text-slate-500 italic mt-1.5">Catatan: <?= htmlspecialchars($receipt['note']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Grand Total & Payment Summary -->
        <div class="py-1.5 border-b border-dashed border-slate-400 space-y-1">
            <div class="flex justify-between items-baseline text-xs font-black">
                <span class="tracking-wider">TOTAL BELANJA:</span>
                <span class="text-sm text-slate-900"><?= format_rupiah($receipt['total_harga'] ?? $receipt['subtotal']) ?></span>
            </div>
            <?php if (!$isKasbon): ?>
                <div class="flex justify-between text-[11px] text-slate-700">
                    <span>Uang Diterima:</span>
                    <span><?= format_rupiah($receipt['jumlah_bayar'] ?? ($receipt['total_harga'] ?? $receipt['subtotal'])) ?></span>
                </div>
                <div class="flex justify-between text-[11px] text-slate-900 font-extrabold">
                    <span>Kembalian:</span>
                    <span class="text-emerald-700"><?= format_rupiah($receipt['kembalian'] ?? 0) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="text-center text-[10px] text-slate-600 pt-1 leading-tight">
            <p class="font-bold text-slate-800">Terima kasih atas kunjungan Anda!</p>
            <p class="text-[9px] text-slate-500 mt-1">Harap simpan struk ini sebagai bukti transaksi sah.</p>
        </div>

        <!-- Action Buttons (No Print) -->
        <div class="pt-3 border-t border-slate-200 flex items-center space-x-2 no-print font-sans">
            <button
                type="button"
                onclick="window.print()"
                class="flex-1 py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Struk (Print)</span>
            </button>
            <a
                href="<?= url('/transactions/' . $receipt['movement_id']) ?>"
                class="px-4 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-xl transition text-center"
            >
                Kembali
            </a>
        </div>
    </div>
</body>
</html>

