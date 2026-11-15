<?php
/**
 * @var string $period
 * @var array $report
 */
?>
<div class="flex-1 flex flex-col pb-24">
    <!-- Top Gradient Header with Summary & Bar Chart -->
    <header class="bg-gradient-to-br from-[#5356FB] via-[#4346EC] to-[#3437D8] pt-6 pb-6 px-4 rounded-b-[2rem] shadow-xl text-white relative">
        <h1 class="text-2xl font-extrabold text-white tracking-tight mb-4">Laporan</h1>

        <!-- Period Filter Pills (Hari ini | Minggu | Bulan) -->
        <div class="bg-black/25 p-1 rounded-full flex items-center justify-between mb-5 border border-white/10 max-w-[340px] mx-auto">
            <a
                href="<?= url('/reports?period=today') ?>"
                class="flex-1 py-1.5 text-center text-xs rounded-full font-bold transition <?= $period === 'today' ? 'bg-white text-[#3A3BE0] shadow-md' : 'text-white/80 hover:text-white' ?>"
            >
                Hari ini
            </a>
            <a
                href="<?= url('/reports?period=week') ?>"
                class="flex-1 py-1.5 text-center text-xs rounded-full font-bold transition <?= $period === 'week' ? 'bg-white text-[#3A3BE0] shadow-md' : 'text-white/80 hover:text-white' ?>"
            >
                Minggu
            </a>
            <a
                href="<?= url('/reports?period=month') ?>"
                class="flex-1 py-1.5 text-center text-xs rounded-full font-bold transition <?= $period === 'month' ? 'bg-white text-[#3A3BE0] shadow-md' : 'text-white/80 hover:text-white' ?>"
            >
                Bulan
            </a>
        </div>

        <!-- 3 Main Summary Metrics (Pemasukan, Pengeluaran, Laba) -->
        <div class="grid grid-cols-3 gap-2 mb-6">
            <!-- Pemasukan -->
            <div>
                <p class="text-[11px] font-medium text-white/75">Pemasukan</p>
                <p class="text-sm sm:text-base font-extrabold text-white tabular-nums mt-0.5 truncate" title="<?= format_rupiah($report['total_income']) ?>">
                    <?= format_rupiah($report['total_income']) ?>
                </p>
            </div>

            <!-- Pengeluaran -->
            <div>
                <p class="text-[11px] font-medium text-white/75">Pengeluaran</p>
                <p class="text-sm sm:text-base font-extrabold text-white tabular-nums mt-0.5 truncate" title="<?= format_rupiah($report['total_expense']) ?>">
                    <?= format_rupiah($report['total_expense']) ?>
                </p>
            </div>

            <!-- Laba -->
            <div>
                <p class="text-[11px] font-medium text-white/75">Laba</p>
                <p class="text-sm sm:text-base font-extrabold tabular-nums mt-0.5 truncate <?= $report['net_profit'] < 0 ? 'text-amber-300' : 'text-white' ?>" title="<?= format_rupiah($report['net_profit']) ?>">
                    <?= $report['net_profit'] < 0 ? '-' : '' ?><?= format_rupiah(abs($report['net_profit'])) ?>
                </p>
            </div>
        </div>

        <!-- Bar Chart (Pemasukan vs Pengeluaran) -->
        <div class="relative pt-4">
            <!-- Faint Grid lines -->
            <div class="absolute inset-x-0 top-4 bottom-10 flex flex-col justify-between pointer-events-none opacity-20">
                <div class="border-b border-white"></div>
                <div class="border-b border-white"></div>
                <div class="border-b border-white"></div>
                <div class="border-b border-white"></div>
            </div>

            <!-- Columns Container -->
            <div class="h-32 flex items-end justify-between px-1 relative z-10">
                <?php foreach ($report['chart_data']['bars'] as $bar): ?>
                    <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
                        <!-- Tooltip on hover/touch -->
                        <div class="opacity-0 group-hover:opacity-100 transition absolute -top-8 bg-[#0B0E17] text-white text-[10px] py-1 px-2 rounded shadow-lg whitespace-nowrap pointer-events-none z-20">
                            Masuk: <?= format_rupiah($bar['income']) ?> | Keluar: <?= format_rupiah($bar['expense']) ?>
                        </div>

                        <!-- Dual Bars (White for Income, Deep Indigo for Expense) -->
                        <div class="flex items-end space-x-1 h-24 mb-2">
                            <!-- Income Bar (White) -->
                            <div
                                class="w-2.5 sm:w-3 bg-white rounded-full transition-all duration-500 shadow-sm"
                                style="height: <?= max(4, $bar['income_height']) ?>%;"
                                title="Pemasukan: <?= format_rupiah($bar['income']) ?>"
                            ></div>

                            <!-- Expense Bar (Deep Indigo / Violet) -->
                            <div
                                class="w-2.5 sm:w-3 bg-[#1C1F82] rounded-full transition-all duration-500 shadow-sm border border-indigo-300/20"
                                style="height: <?= max(4, $bar['expense_height']) ?>%;"
                                title="Pengeluaran: <?= format_rupiah($bar['expense']) ?>"
                            ></div>
                        </div>

                        <!-- Day / Period Label -->
                        <div class="mt-1">
                            <?php if ($bar['is_current']): ?>
                                <span class="inline-block px-1.5 py-0.5 rounded-full border border-white text-[10px] font-extrabold text-white leading-none">
                                    <?= htmlspecialchars($bar['label']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-[10px] font-semibold text-white/70">
                                    <?= htmlspecialchars($bar['label']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Chart Legend (Pemasukan & Pengeluaran) -->
            <div class="flex items-center justify-center space-x-4 mt-3">
                <div class="flex items-center space-x-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-white shadow-sm inline-block"></span>
                    <span class="text-[11px] font-medium text-white/85">Pemasukan</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#1C1F82] border border-white/40 inline-block"></span>
                    <span class="text-[11px] font-medium text-white/85">Pengeluaran</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Body Content -->
    <main class="px-4 py-4 space-y-4">
        <!-- Detailed Financial Breakdown Card -->
        <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3 border-b border-[#232B3E] pb-2">
                <h3 class="text-sm font-extrabold text-white flex items-center space-x-2">
                    <svg class="w-4 h-4 text-[#6F7CF5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Rincian Laba & Biaya (<?= htmlspecialchars($report['period_label']) ?>)</span>
                </h3>
            </div>

            <div class="space-y-2.5 text-xs">
                <!-- Total Pemasukan -->
                <div class="flex items-center justify-between">
                    <span class="text-slate-300">Total Pemasukan</span>
                    <span class="font-bold text-emerald-400 tabular-nums">
                        +<?= format_rupiah($report['total_income']) ?>
                    </span>
                </div>

                <!-- Harga Pokok Penjualan (HPP) -->
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-slate-300">Harga Pokok Penjualan (HPP)</span>
                        <p class="text-[10px] text-slate-500">Modal barang yang terjual</p>
                    </div>
                    <span class="font-bold text-amber-400 tabular-nums">
                        -<?= format_rupiah($report['hpp']) ?>
                    </span>
                </div>

                <!-- Laba Kotor -->
                <div class="flex items-center justify-between pt-1 border-t border-[#232B3E]/60">
                    <span class="font-bold text-white">Laba Kotor</span>
                    <span class="font-extrabold tabular-nums <?= $report['gross_profit'] >= 0 ? 'text-white' : 'text-rose-400' ?>">
                        <?= $report['gross_profit'] < 0 ? '-' : '' ?><?= format_rupiah(abs($report['gross_profit'])) ?>
                    </span>
                </div>

                <!-- Pengeluaran Operasional -->
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-slate-300">Pengeluaran Operasional</span>
                        <p class="text-[10px] text-slate-500">Listrik, sewa, gaji, operasional kas</p>
                    </div>
                    <span class="font-bold text-rose-400 tabular-nums">
                        -<?= format_rupiah($report['operational_expense']) ?>
                    </span>
                </div>

                <!-- Laba Bersih Highlight -->
                <div class="flex items-center justify-between pt-2 border-t-2 border-[#232B3E] bg-[#0B0E17]/60 -mx-4 -mb-4 p-4 rounded-b-3xl mt-3">
                    <div>
                        <p class="text-xs font-extrabold text-white">Laba Bersih</p>
                        <p class="text-[10px] text-slate-400">Pemasukan - HPP - Operasional</p>
                    </div>
                    <div class="text-right">
                        <p class="text-base font-extrabold tabular-nums <?= $report['net_profit'] >= 0 ? 'text-emerald-400' : 'text-rose-400' ?>">
                            <?= $report['net_profit'] < 0 ? '-' : '' ?><?= format_rupiah(abs($report['net_profit'])) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barang Terlaris Section -->
        <div>
            <h3 class="text-base font-extrabold text-white mb-3 flex items-center space-x-2">
                <span>Barang Terlaris</span>
                <span class="text-xs font-normal text-slate-400">(<?= htmlspecialchars($report['period_label']) ?>)</span>
            </h3>

            <?php if (empty($report['top_products'])): ?>
                <!-- Empty State -->
                <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-6 text-center shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-[#0B0E17] border border-[#232B3E] text-slate-500 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-white">Belum Ada Barang Terjual</h4>
                    <p class="text-xs text-slate-400 mt-1">
                        Catat penjualan pada periode ini untuk melihat daftar barang terlaris.
                    </p>
                </div>
            <?php else: ?>
                <div class="space-y-2.5">
                    <?php foreach ($report['top_products'] as $product): ?>
                        <div class="bg-[#161B29] border border-[#232B3E] rounded-2xl p-3.5 shadow-sm hover:border-indigo-500/30 transition">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <!-- Rank Badge Circle -->
                                    <div class="w-7 h-7 rounded-full bg-[#1E2548] border border-indigo-500/30 text-indigo-300 font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                                        <?= $product['rank'] ?>
                                    </div>

                                    <!-- Name & Quantity -->
                                    <div>
                                        <h4 class="text-sm font-bold text-white leading-tight">
                                            <span><?= htmlspecialchars($product['name']) ?></span>
                                            <span class="text-xs font-normal text-slate-400 ml-1.5"><?= htmlspecialchars($product['quantity_formatted']) ?></span>
                                        </h4>
                                    </div>
                                </div>

                                <!-- Revenue -->
                                <div class="text-right">
                                    <p class="text-sm font-bold text-white tabular-nums">
                                        <?= htmlspecialchars($product['revenue_formatted']) ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Horizontal Progress Bar Indicator -->
                            <div class="w-full bg-[#0B0E17] rounded-full h-1.5 overflow-hidden">
                                <div
                                    class="bg-gradient-to-r from-[#4B4EF0] to-[#7A88FF] h-1.5 rounded-full transition-all duration-500"
                                    style="width: <?= $product['percentage'] ?>%;"
                                ></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

