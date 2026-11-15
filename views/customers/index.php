<?php
/**
 * @var array $customers
 * @var array $summary
 * @var string $search
 */
?>
<div
    x-data="{
        createOpen: false,
        editOpen: false,
        editCustomer: {
            id: null,
            name: '',
            phone: '',
            address: '',
            note: ''
        },
        openEdit(c) {
            this.editCustomer.id = c.id;
            this.editCustomer.name = c.nama || c.name || '';
            this.editCustomer.phone = c.telepon || c.phone || '';
            this.editCustomer.address = c.alamat || c.address || '';
            this.editCustomer.note = c.catatan || c.note || '';
            this.editOpen = true;
        }
    }"
    class="flex-1 flex flex-col pb-24"
>
    <!-- Top Header -->
    <header class="px-4 pt-6 pb-2">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-3">
                <a
                    href="<?= url('/dashboard') ?>"
                    class="w-9 h-9 rounded-full bg-[#161B29] border border-[#232B3E] text-slate-400 hover:text-white flex items-center justify-center transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-extrabold text-white tracking-tight">Buku Kasbon</h1>
                    <p class="text-xs text-slate-400">Catatan kasbon & piutang pelanggan warung</p>
                </div>
            </div>

            <!-- Add Customer Button -->
            <button
                type="button"
                @click="createOpen = true"
                class="px-3.5 py-2 bg-[#4B4EF0] hover:bg-[#3A3BE0] text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/30 flex items-center space-x-1.5 transition active:scale-95 whitespace-nowrap"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Pembeli</span>
            </button>
        </div>

        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-3 gap-2.5 my-3">
            <div class="bg-[#161B29] border border-[#232B3E] rounded-2xl p-3">
                <p class="text-[10px] text-slate-400 font-medium">Pelanggan</p>
                <p class="text-lg font-extrabold text-white mt-0.5 tabular-nums"><?= (int) ($summary['total_customers'] ?? 0) ?></p>
            </div>
            <div class="bg-[#161B29] border border-[#232B3E] rounded-2xl p-3">
                <p class="text-[10px] text-amber-400/90 font-medium">Sisa Kasbon</p>
                <p class="text-xs sm:text-sm font-extrabold text-amber-400 mt-1 tabular-nums"><?= format_rupiah($summary['total_unpaid_all'] ?? 0) ?></p>
            </div>
            <div class="bg-[#161B29] border border-[#232B3E] rounded-2xl p-3">
                <p class="text-[10px] text-emerald-400/90 font-medium">Sudah Lunas</p>
                <p class="text-xs sm:text-sm font-extrabold text-emerald-400 mt-1 tabular-nums"><?= format_rupiah($summary['total_paid_all'] ?? 0) ?></p>
            </div>
        </div>

        <!-- Search Form -->
        <form method="GET" action="<?= url('/customers') ?>" class="relative mt-2">
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Cari nama, kode (CUST-001), atau nomor HP..."
                class="w-full pl-10 pr-10 py-2.5 bg-[#161B29] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[44px]"
            >
            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <?php if ($search !== ''): ?>
                <a
                    href="<?= url('/customers') ?>"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white text-xs p-1"
                >
                    ✕
                </a>
            <?php endif; ?>
        </form>
    </header>

    <!-- Main List of Customers -->
    <main class="px-4 py-2 flex-1">
        <?php if (empty($customers)): ?>
            <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-8 text-center shadow-sm my-4">
                <div class="w-14 h-14 rounded-2xl bg-[#0B0E17] border border-[#232B3E] text-slate-500 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Belum Ada Data Pembeli</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-[260px] mx-auto">
                    <?= $search !== '' ? 'Tidak ditemukan pembeli sesuai kata kunci pencarian.' : 'Pembeli akan otomatis dicatat setiap ada transaksi barang keluar (penjualan), atau bisa ditambah manual di sini.' ?>
                </p>
                <button
                    type="button"
                    @click="createOpen = true"
                    class="mt-4 px-4 py-2.5 bg-[#4B4EF0] text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/30 active:scale-95 transition"
                >
                    + Tambah Pembeli Sekarang
                </button>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($customers as $c): ?>
                    <?php
                    $ordersCount = (int) ($c['total_orders'] ?? 0);
                    $spent = (float) ($c['total_spent'] ?? 0);
                    $unpaid = (float) ($c['total_unpaid_debt'] ?? 0);
                    $paid = (float) ($c['total_paid_debt'] ?? 0);
                    $cleanPhone = preg_replace('/[^\d]/', '', $c['telepon'] ?? $c['phone'] ?? '');
                    if (str_starts_with($cleanPhone, '0')) {
                        $waPhone = '62' . substr($cleanPhone, 1);
                    } else {
                        $waPhone = $cleanPhone;
                    }
                    ?>
                    <div class="bg-[#161B29] border <?= $unpaid > 0 ? 'border-amber-500/30' : 'border-[#232B3E]' ?> hover:border-indigo-500/40 rounded-2xl p-4 shadow-sm transition">
                        <!-- Top Row: Code Badge, Status Badge, Name, Action Buttons -->
                        <div class="flex items-start justify-between">
                            <a href="<?= url('/customers/' . $c['id']) ?>" class="flex-1 min-w-0 pr-2 group">
                                <div class="flex items-center space-x-2 mb-1 flex-wrap gap-y-1">
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-950/80 border border-indigo-500/30 text-indigo-300 text-[10px] font-extrabold tracking-wider">
                                        <?= htmlspecialchars($c['kode'] ?? $c['code']) ?>
                                    </span>
                                    <?php if ($unpaid > 0): ?>
                                        <span class="px-2 py-0.5 rounded-md bg-amber-950/80 border border-amber-500/40 text-amber-300 text-[10px] font-extrabold tracking-wider">
                                            Hutang: <?= format_rupiah($unpaid) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-950/60 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold">
                                            Lunas
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($c['telepon'] ?? $c['phone'])): ?>
                                        <span class="text-[11px] text-slate-400">· <?= htmlspecialchars($c['telepon'] ?? $c['phone']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="text-base font-bold text-white group-hover:text-indigo-300 transition truncate">
                                    <?= htmlspecialchars($c['nama'] ?? $c['name']) ?>
                                </h3>
                                <?php if (!empty($c['catatan'] ?? $c['note'])): ?>
                                    <p class="text-xs text-slate-400 mt-0.5 line-clamp-1 italic">
                                        "<?= htmlspecialchars($c['catatan'] ?? $c['note']) ?>"
                                    </p>
                                <?php endif; ?>
                            </a>

                            <!-- Actions -->
                            <div class="flex items-center space-x-1.5 flex-shrink-0">
                                <?php if (!empty($waPhone)): ?>
                                    <a
                                        href="https://wa.me/<?= $waPhone ?>"
                                        target="_blank"
                                        rel="noopener"
                                        title="Chat WhatsApp"
                                        class="w-8 h-8 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 flex items-center justify-center transition"
                                    >
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>

                                <button
                                    type="button"
                                    @click="openEdit(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>)"
                                    title="Ubah Data"
                                    class="w-8 h-8 rounded-lg bg-[#0B0E17] hover:bg-[#1E2538] text-slate-300 hover:text-white border border-[#232B3E] flex items-center justify-center transition"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Bottom Row: Statistics & Link to Details -->
                        <div class="mt-3 pt-3 border-t border-[#232B3E]/60 flex items-center justify-between">
                            <div class="flex items-center space-x-3 text-xs">
                                <div>
                                    <span class="text-slate-400">Sisa Kasbon:</span>
                                    <span class="font-bold <?= $unpaid > 0 ? 'text-amber-400' : 'text-slate-400' ?> tabular-nums"><?= format_rupiah($unpaid) ?></span>
                                </div>
                                <span class="text-slate-600">·</span>
                                <div>
                                    <span class="text-slate-400">Lunas:</span>
                                    <span class="font-bold text-emerald-400 tabular-nums"><?= format_rupiah($paid) ?></span>
                                </div>
                            </div>

                            <a
                                href="<?= url('/customers/' . $c['id']) ?>"
                                class="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center space-x-1"
                            >
                                <span>Buku Kasbon</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- ======================================================== -->
    <!-- BOTTOM SHEET: TAMBAH PEMBELI                             -->
    <!-- ======================================================== -->
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-40 flex items-end justify-center">
        <div
            x-show="createOpen"
            @click="createOpen = false"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm"
        ></div>

        <div
            x-show="createOpen"
            x-transition:enter="transition ease-out duration-250 transform"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="relative w-full max-w-[412px] bg-[#161B29] border-t border-[#232B3E] rounded-t-3xl p-5 z-50 shadow-2xl max-h-[90vh] overflow-y-auto no-scrollbar"
        >
            <div class="w-12 h-1.5 bg-slate-700 rounded-full mx-auto mb-4"></div>

            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-950/60 border border-indigo-500/40 text-indigo-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Tambah Data Pembeli</h3>
                        <p class="text-xs text-slate-400">Daftarkan pelanggan atau langganan warung</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="createOpen = false"
                    class="w-8 h-8 rounded-full bg-[#0B0E17] border border-[#232B3E] text-slate-400 hover:text-white flex items-center justify-center"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="<?= url('/customers') ?>" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label for="create_cust_name" class="block text-xs font-semibold text-slate-300 mb-1">
                        Nama Pembeli / Langganan <span class="text-rose-400">*</span>
                    </label>
                    <input
                        type="text"
                        id="create_cust_name"
                        name="name"
                        placeholder="Contoh: Bu Endang, Pak RT, Mas Budi"
                        required
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <div>
                    <label for="create_cust_phone" class="block text-xs font-semibold text-slate-300 mb-1">
                        Nomor HP / WhatsApp (Opsional)
                    </label>
                    <input
                        type="tel"
                        id="create_cust_phone"
                        name="phone"
                        placeholder="Contoh: 081234567890"
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <div>
                    <label for="create_cust_address" class="block text-xs font-semibold text-slate-300 mb-1">
                        Alamat / Lokasi Rumah (Opsional)
                    </label>
                    <input
                        type="text"
                        id="create_cust_address"
                        name="address"
                        placeholder="Contoh: RT 03 RW 02 samping musholla"
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <div>
                    <label for="create_cust_note" class="block text-xs font-semibold text-slate-300 mb-1">
                        Catatan Khusus (Opsional)
                    </label>
                    <input
                        type="text"
                        id="create_cust_note"
                        name="note"
                        placeholder="Contoh: Langganan telur & minyak, bayar tiap akhir bulan"
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full min-h-[48px] px-4 py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center space-x-2"
                    >
                        <span>Simpan Data Pembeli</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- BOTTOM SHEET: UBAH DATA PEMBELI                          -->
    <!-- ======================================================== -->
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-40 flex items-end justify-center">
        <div
            x-show="editOpen"
            @click="editOpen = false"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm"
        ></div>

        <div
            x-show="editOpen"
            x-transition:enter="transition ease-out duration-250 transform"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="relative w-full max-w-[412px] bg-[#161B29] border-t border-[#232B3E] rounded-t-3xl p-5 z-50 shadow-2xl max-h-[90vh] overflow-y-auto no-scrollbar"
        >
            <div class="w-12 h-1.5 bg-slate-700 rounded-full mx-auto mb-4"></div>

            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-950/60 border border-amber-500/40 text-amber-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Ubah Data Pembeli</h3>
                        <p class="text-xs text-slate-400">Perbarui kontak dan catatan pembeli</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="editOpen = false"
                    class="w-8 h-8 rounded-full bg-[#0B0E17] border border-[#232B3E] text-slate-400 hover:text-white flex items-center justify-center"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="'<?= url('/customers') ?>/' + editCustomer.id" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">

                <div>
                    <label for="edit_cust_name" class="block text-xs font-semibold text-slate-300 mb-1">
                        Nama Pembeli <span class="text-rose-400">*</span>
                    </label>
                    <input
                        type="text"
                        id="edit_cust_name"
                        name="name"
                        x-model="editCustomer.name"
                        required
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <div>
                    <label for="edit_cust_phone" class="block text-xs font-semibold text-slate-300 mb-1">
                        Nomor HP / WhatsApp
                    </label>
                    <input
                        type="tel"
                        id="edit_cust_phone"
                        name="phone"
                        x-model="editCustomer.phone"
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <div>
                    <label for="edit_cust_address" class="block text-xs font-semibold text-slate-300 mb-1">
                        Alamat / Lokasi
                    </label>
                    <input
                        type="text"
                        id="edit_cust_address"
                        name="address"
                        x-model="editCustomer.address"
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <div>
                    <label for="edit_cust_note" class="block text-xs font-semibold text-slate-300 mb-1">
                        Catatan Khusus
                    </label>
                    <input
                        type="text"
                        id="edit_cust_note"
                        name="note"
                        x-model="editCustomer.note"
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <div class="pt-2 space-y-2">
                    <button
                        type="submit"
                        class="w-full min-h-[48px] px-4 py-3 bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm rounded-xl shadow-lg transition flex items-center justify-center space-x-2"
                    >
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>

            <!-- Soft Delete Option -->
            <form :action="'<?= url('/customers') ?>/' + editCustomer.id" method="POST" class="mt-3" onsubmit="return confirm('Yakin ingin menghapus data pembeli ini?')">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button
                    type="submit"
                    class="w-full py-2.5 text-xs text-rose-400 hover:text-rose-300 font-semibold text-center transition"
                >
                    Hapus Data Pembeli Ini
                </button>
            </form>
        </div>
    </div>
</div>

