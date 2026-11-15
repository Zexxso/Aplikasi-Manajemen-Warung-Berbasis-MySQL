<?php
/**
 * @var array $groupedTransactions
 * @var string $filter
 */
?>
<div
    x-data="{
        deleteModalOpen: false,
        deleteType: '',
        deleteId: null,
        deleteTitle: '',
        deleteSubtitle: '',
        deleteAmount: '',
        receiptModalOpen: false,
        receipt: {
            invoice_no: '',
            date: '',
            time: '',
            product_name: '',
            quantity: 1,
            unit: 'pcs',
            unit_price: 0,
            amount: 0,
            payment_status: 'paid',
            customer_name: '',
            note: ''
        },
        confirmDelete(type, id, title, subtitle, amount) {
            this.deleteType = type;
            this.deleteId = id;
            this.deleteTitle = title;
            this.deleteSubtitle = subtitle;
            this.deleteAmount = amount;
            this.deleteModalOpen = true;
        },
        openReceipt(tx) {
            this.receipt.invoice_no = tx.invoice_no || ('TRX-' + String(tx.movement_id || tx.id).padStart(5, '0'));
            this.receipt.date = tx.date_header || '';
            this.receipt.time = tx.time_formatted || '';
            this.receipt.product_name = tx.product_name || tx.title || 'Barang';
            this.receipt.quantity = tx.quantity || 1;
            this.receipt.unit = tx.unit || 'item';
            this.receipt.unit_price = tx.unit_price || tx.amount;
            this.receipt.amount = tx.amount || 0;
            this.receipt.payment_status = tx.payment_status || 'paid';
            this.receipt.customer_name = tx.customer_name || '';
            this.receipt.note = tx.note || '';
            this.receiptModalOpen = true;
        },
        printReceipt() {
            window.print();
        }
    }"
    class="flex-1 flex flex-col pb-24"
>
    <!-- Top Title & Filter Section -->
    <header class="px-4 pt-6 pb-2">
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Riwayat</h1>

        <!-- Filter Chips (Semua | Barang | Uang) -->
        <div class="flex items-center space-x-2 mt-4">
            <a
                href="<?= url('/history?filter=all') ?>"
                class="px-4 py-2 rounded-full text-xs font-bold transition min-h-[40px] flex items-center justify-center <?= $filter === 'all' ? 'bg-[#4B4EF0] text-white shadow-md shadow-indigo-600/30' : 'bg-[#161B29] border border-[#232B3E] text-slate-300 hover:text-white' ?>"
            >
                Semua
            </a>
            <a
                href="<?= url('/history?filter=stock') ?>"
                class="px-4 py-2 rounded-full text-xs font-bold transition min-h-[40px] flex items-center justify-center <?= $filter === 'stock' ? 'bg-[#4B4EF0] text-white shadow-md shadow-indigo-600/30' : 'bg-[#161B29] border border-[#232B3E] text-slate-300 hover:text-white' ?>"
            >
                Barang
            </a>
            <a
                href="<?= url('/history?filter=cash') ?>"
                class="px-4 py-2 rounded-full text-xs font-bold transition min-h-[40px] flex items-center justify-center <?= $filter === 'cash' ? 'bg-[#4B4EF0] text-white shadow-md shadow-indigo-600/30' : 'bg-[#161B29] border border-[#232B3E] text-slate-300 hover:text-white' ?>"
            >
                Uang
            </a>
        </div>

        <p class="text-xs text-slate-400 mt-3">
            Ketuk tombol hapus pada transaksi untuk membatalkan catatan, stok dan uang akan otomatis dikembalikan.
        </p>
    </header>

    <!-- Main History List Grouped by Date -->
    <main class="px-4 py-2 flex-1">
        <?php if (empty($groupedTransactions)): ?>
            <!-- Empty State -->
            <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-8 text-center shadow-sm my-6">
                <div class="w-14 h-14 rounded-2xl bg-[#0B0E17] border border-[#232B3E] text-slate-500 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Belum Ada Riwayat</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-[240px] mx-auto">
                    <?php if ($filter === 'stock'): ?>
                        Belum ada riwayat transaksi barang masuk atau keluar.
                    <?php elseif ($filter === 'cash'): ?>
                        Belum ada riwayat transaksi kas uang masuk atau keluar.
                    <?php else: ?>
                        Catatan transaksi barang dan keuangan Anda akan muncul di sini.
                    <?php endif; ?>
                </p>
                <button
                    type="button"
                    @click="$dispatch('open-record-sheet')"
                    class="mt-4 px-4 py-2.5 bg-[#4B4EF0] text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/30 active:scale-95 transition"
                >
                    + Catat Transaksi
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($groupedTransactions as $dateHeader => $transactions): ?>
                <div class="mb-5">
                    <!-- Sticky Date Header -->
                    <div class="sticky top-0 z-10 py-1.5 px-1 backdrop-blur-md bg-[#0B0E17]/90 text-xs font-bold text-slate-400 flex items-center justify-between">
                        <span><?= htmlspecialchars($dateHeader) ?></span>
                        <span class="text-[11px] font-normal text-slate-500"><?= count($transactions) ?> transaksi</span>
                    </div>

                    <!-- Card Group -->
                    <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-2.5 shadow-sm divide-y divide-[#232B3E]/60 mt-1">
                        <?php foreach ($transactions as $tx): ?>
                            <div class="p-3 rounded-2xl hover:bg-[#1E2538]/50 transition group space-y-2">
                                <!-- Top Row: Icon + Title & Subtitle (Left) vs Nominal & Category (Right) -->
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                                        <!-- Direction Icon -->
                                        <?php if ($tx['type'] === 'income'): ?>
                                            <div class="w-9 h-9 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-400 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                </svg>
                                            </div>
                                        <?php elseif ($tx['type'] === 'expense'): ?>
                                            <div class="w-9 h-9 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-400 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                                </svg>
                                            </div>
                                        <?php elseif ($tx['type'] === 'debt'): ?>
                                            <div class="w-9 h-9 rounded-xl bg-amber-950/60 border border-amber-500/30 text-amber-400 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Title & Subtitle with Full Width -->
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-sm font-bold text-white truncate leading-tight"><?= htmlspecialchars($tx['title']) ?></h4>
                                            <p class="text-xs text-slate-400 mt-0.5 truncate"><?= htmlspecialchars($tx['subtitle']) ?></p>
                                        </div>
                                    </div>

                                    <!-- Nominal & Category Name -->
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-sm font-bold tabular-nums <?= $tx['type'] === 'income' ? 'text-emerald-400' : ($tx['type'] === 'expense' ? 'text-rose-400' : ($tx['type'] === 'debt' ? 'text-amber-400' : 'text-slate-300')) ?>">
                                            <?= htmlspecialchars($tx['amount_formatted']) ?>
                                        </p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">
                                            <?= htmlspecialchars($tx['category_name']) ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Bottom Row: Badges (Left) & Action Buttons (Right) -->
                                <div class="flex items-center justify-between pt-1 border-t border-[#232B3E]/40 text-xs">
                                    <!-- Badges: Customer & Status -->
                                    <div class="flex items-center space-x-1.5 flex-wrap gap-y-1 min-w-0 pr-2">
                                        <?php if (!empty($tx['customer_name'])): ?>
                                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-950/70 text-indigo-300 border border-indigo-500/30">
                                                <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <span class="truncate max-w-[120px]"><?= htmlspecialchars($tx['customer_name']) ?></span>
                                            </span>
                                        <?php endif; ?>

                                        <?php if (!empty($tx['is_sale'])): ?>
                                            <?php if (($tx['payment_status'] ?? '') === 'debt'): ?>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-950/70 text-amber-300 border border-amber-500/30">
                                                    Kasbon (Belum Lunas)
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-950/60 text-emerald-400 border border-emerald-500/20">
                                                    Tunai Lunas
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Action Buttons: Lunas (if debt), Detail, Delete -->
                                    <div class="flex items-center space-x-1.5 flex-shrink-0">
                                        <!-- Quick Settle Kasbon Button (if debt) -->
                                        <?php if (!empty($tx['movement_id']) && ($tx['payment_status'] ?? '') === 'debt'): ?>
                                            <form method="POST" action="<?= url('/kasbon/' . $tx['movement_id'] . '/settle') ?>" class="inline" onsubmit="return confirm('Tandai kasbon <?= addslashes(htmlspecialchars($tx['customer_name'] ?? 'pembeli')) ?> sebesar <?= addslashes(htmlspecialchars($tx['amount_formatted'])) ?> ini sebagai LUNAS?')">
                                                <?= csrf_field() ?>
                                                <button
                                                    type="submit"
                                                    class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white text-[10px] font-bold flex items-center space-x-1 shadow transition"
                                                    title="Tandai Kasbon Lunas"
                                                >
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>Lunas</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Detail Transaksi Link (if movement exists) -->
                                        <?php if (!empty($tx['movement_id'])): ?>
                                            <a
                                                href="<?= url('/transactions/' . $tx['movement_id']) ?>"
                                                class="px-2.5 py-1 rounded-lg bg-[#0B0E17] hover:bg-[#1E2538] active:scale-95 text-slate-300 hover:text-white border border-[#232B3E] text-[10px] font-semibold flex items-center space-x-1 transition"
                                                title="Lihat Detail Transaksi"
                                                aria-label="Detail Transaksi"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Detail</span>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Delete Transaction Action Button -->
                                        <button
                                            type="button"
                                            @click="confirmDelete('<?= $tx['delete_type'] ?>', <?= $tx['delete_id'] ?>, '<?= addslashes(htmlspecialchars($tx['title'])) ?>', '<?= addslashes(htmlspecialchars($tx['subtitle'])) ?>', '<?= addslashes(htmlspecialchars($tx['amount_formatted'])) ?>')"
                                            class="w-7 h-7 rounded-lg bg-rose-500/10 hover:bg-rose-500/25 active:bg-rose-500/40 text-rose-400 hover:text-rose-300 flex items-center justify-center transition border border-rose-500/20"
                                            title="Hapus / Batalkan Transaksi"
                                            aria-label="Hapus Transaksi"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <!-- Confirmation Dialog Modal for Deletion -->
    <div
        x-show="deleteModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/75 backdrop-blur-sm"
        @click.self="deleteModalOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            class="w-full max-w-[412px] bg-[#161B29] border-t sm:border border-[#232B3E] rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl relative"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-y-full sm:scale-95"
            x-transition:enter-end="translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0 sm:scale-100"
            x-transition:leave-end="translate-y-full sm:scale-95"
        >
            <div class="w-12 h-1.5 bg-[#232B3E] rounded-full mx-auto mb-4 sm:hidden"></div>

            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/20 border border-rose-500/30 text-rose-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <div class="flex-1">
                    <h3 class="text-base font-extrabold text-white">Hapus Transaksi?</h3>
                    <p class="text-xs text-rose-300 font-semibold mt-1">
                        Hapus transaksi ini? Stok dan uang akan dikembalikan.
                    </p>
                </div>
            </div>

            <!-- Transaction Details Card Preview -->
            <div class="bg-[#0B0E17] border border-[#232B3E] rounded-2xl p-3.5 my-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-white" x-text="deleteTitle"></p>
                        <p class="text-xs text-slate-400 mt-0.5" x-text="deleteSubtitle"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-white tabular-nums" x-text="deleteAmount"></p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Form -->
            <form
                :action="'<?= url('/history') ?>/' + deleteType + '/' + deleteId"
                method="POST"
                class="space-y-2.5 mt-5"
            >
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">

                <button
                    type="submit"
                    class="w-full py-3.5 px-4 bg-rose-600 hover:bg-rose-500 active:scale-[0.99] text-white font-bold text-sm rounded-2xl shadow-lg shadow-rose-600/30 transition min-h-[48px] flex items-center justify-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Ya, Hapus Transaksi</span>
                </button>

                <button
                    type="button"
                    @click="deleteModalOpen = false"
                    class="w-full py-3 px-4 bg-[#232B3E] hover:bg-[#2C364D] active:scale-[0.99] text-slate-200 font-semibold text-sm rounded-2xl transition min-h-[48px]"
                >
                    Batal
                </button>
            </form>
        </div>
    </div>
</div>

