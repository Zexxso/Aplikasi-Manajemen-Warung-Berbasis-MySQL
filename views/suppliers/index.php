<?php
/**
 * @var array $suppliers
 * @var string $q
 * @var array $user
 */
?>
<div
    x-data="{
        addSupplierOpen: false,
        editSupplierOpen: false,
        editData: {
            id: null,
            nama: '',
            nohp: '',
            email: '',
            alamat: ''
        },
        openEdit(s) {
            this.editData.id = s.id_pemasok;
            this.editData.nama = s.nama_pemasok || '';
            this.editData.nohp = s.no_hp_pemasok || '';
            this.editData.email = s.email_pemasok || '';
            this.editData.alamat = s.alamat_pemasok || '';
            this.editSupplierOpen = true;
        }
    }"
    class="flex-1 flex flex-col pb-24"
>
    <!-- Header -->
    <header class="px-4 pt-6 pb-4 border-b border-[#232B3E]">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-3">
                <a
                    href="<?= url('/dashboard') ?>"
                    class="w-10 h-10 rounded-xl bg-[#161B29] border border-[#232B3E] text-slate-300 hover:text-white flex items-center justify-center transition"
                    aria-label="Kembali"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-extrabold text-white leading-tight">Pemasok & Kulakan</h1>
                    <p class="text-xs text-slate-400 mt-0.5"><?= count($suppliers) ?> pemasok terdaftar</p>
                </div>
            </div>

            <!-- Tombol Tambah Pemasok -->
            <button
                type="button"
                @click="addSupplierOpen = true"
                class="px-3.5 py-2 bg-[#4B4EF0] hover:bg-[#3A3BE0] active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/30 transition flex items-center space-x-1.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah</span>
            </button>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="<?= url('/suppliers') ?>" class="relative mt-2">
            <input
                type="text"
                name="q"
                value="<?= htmlspecialchars($q) ?>"
                placeholder="Cari nama pemasok, kontak, atau alamat..."
                class="w-full pl-10 pr-10 py-2.5 bg-[#161B29] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]"
            >
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <?php if (!empty($q)): ?>
                <a
                    href="<?= url('/suppliers') ?>"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-white"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            <?php endif; ?>
        </form>
    </header>

    <!-- Main Content -->
    <main class="px-4 py-4 flex-1">
        <?php if (empty($suppliers)): ?>
            <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-8 text-center my-4">
                <div class="w-14 h-14 rounded-2xl bg-[#0B0E17] border border-[#232B3E] text-slate-500 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white"><?= !empty($q) ? 'Pemasok Tidak Ditemukan' : 'Belum Ada Pemasok' ?></h3>
                <p class="text-xs text-slate-400 mt-1 max-w-[260px] mx-auto">
                    <?= !empty($q) ? 'Coba gunakan kata kunci pencarian yang lain.' : 'Tambahkan data pemasok (supplier/agen/sales) agar setiap kulakan tercatat rapi.' ?>
                </p>
                <?php if (empty($q)): ?>
                    <button
                        type="button"
                        @click="addSupplierOpen = true"
                        class="mt-4 px-4 py-2.5 bg-[#4B4EF0] text-white text-xs font-bold rounded-xl shadow-md active:scale-95 transition"
                    >
                        + Tambah Pemasok Pertama
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($suppliers as $s): 
                    $supplierJson = htmlspecialchars(json_encode($s, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                ?>
                    <div class="bg-[#161B29] border border-[#232B3E] rounded-2xl p-4 shadow-sm hover:border-[#323D57] transition">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-700 to-blue-500 text-white font-extrabold text-sm flex items-center justify-center flex-shrink-0 shadow-sm mt-0.5">
                                    <?= htmlspecialchars(strtoupper(substr($s['nama_pemasok'], 0, 2))) ?>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white leading-tight">
                                        <a href="<?= url('/suppliers/' . $s['id_pemasok']) ?>" class="hover:text-indigo-400 transition">
                                            <?= htmlspecialchars($s['nama_pemasok']) ?>
                                        </a>
                                    </h3>
                                    <?php if (!empty($s['no_hp_pemasok'])): ?>
                                        <p class="text-xs text-slate-400 flex items-center space-x-1 mt-1">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span><?= htmlspecialchars($s['no_hp_pemasok']) ?></span>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($s['alamat_pemasok'])): ?>
                                        <p class="text-[11px] text-slate-400 flex items-center space-x-1 mt-0.5">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="truncate max-w-[200px]"><?= htmlspecialchars($s['alamat_pemasok']) ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Total Transaksi Suplai -->
                            <div class="text-right">
                                <span class="inline-block px-2 py-0.5 bg-indigo-950/60 border border-indigo-500/30 text-indigo-300 text-[10px] font-bold rounded-lg">
                                    <?= (int)$s['total_suplai'] ?>x Pasokan
                                </span>
                                <?php if ($s['total_nominal_suplai'] > 0): ?>
                                    <div class="text-[11px] font-semibold text-slate-300 mt-1">
                                        <?= format_rupiah($s['total_nominal_suplai']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Actions Row -->
                        <div class="flex items-center space-x-2 pt-3 mt-3 border-t border-[#232B3E]/80">
                            <a
                                href="<?= url('/suppliers/' . $s['id_pemasok']) ?>"
                                class="flex-1 py-1.5 px-3 bg-[#0B0E17] hover:bg-[#1E2538] border border-[#232B3E] text-indigo-300 text-xs font-semibold rounded-xl text-center transition"
                            >
                                Riwayat Pasokan
                            </a>
                            <button
                                type="button"
                                @click="openEdit(<?= $supplierJson ?>)"
                                class="py-1.5 px-3 bg-[#0B0E17] hover:bg-[#1E2538] border border-[#232B3E] text-slate-300 text-xs font-semibold rounded-xl transition"
                            >
                                Edit
                            </button>
                            <form method="POST" action="<?= url('/suppliers/' . $s['id_pemasok'] . '/delete') ?>" onsubmit="return confirm('Hapus pemasok ini?');">
                                <?= csrf_field() ?>
                                <button
                                    type="submit"
                                    class="py-1.5 px-3 bg-[#0B0E17] hover:bg-rose-950/40 border border-[#232B3E] hover:border-rose-500/40 text-rose-400 text-xs font-semibold rounded-xl transition"
                                >
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modal Tambah Pemasok -->
    <div
        x-show="addSupplierOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/75 backdrop-blur-sm"
        @click.self="addSupplierOpen = false"
    >
        <div class="w-full max-w-[420px] bg-[#161B29] border-t sm:border border-[#232B3E] rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl relative">
            <div class="w-12 h-1.5 bg-[#232B3E] rounded-full mx-auto mb-4 sm:hidden"></div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-extrabold text-white">Tambah Pemasok Baru</h2>
                <button type="button" @click="addSupplierOpen = false" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="<?= url('/suppliers') ?>" class="space-y-3.5">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Pemasok / Distributor *</label>
                    <input type="text" name="nama_pemasok" required placeholder="Contoh: Agen Sembako Barokah" class="w-full px-3.5 py-2.5 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor WhatsApp / HP</label>
                    <input type="tel" name="no_hp_pemasok" placeholder="Contoh: 081234567890" class="w-full px-3.5 py-2.5 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Email (Opsional)</label>
                    <input type="email" name="email_pemasok" placeholder="agen@supplier.com" class="w-full px-3.5 py-2.5 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Gudang / Toko</label>
                    <textarea name="alamat_pemasok" rows="2" placeholder="Alamat pemasok..." class="w-full px-3.5 py-2 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]"></textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] text-white text-xs font-bold rounded-xl shadow-lg transition">
                        Simpan Pemasok
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Pemasok -->
    <div
        x-show="editSupplierOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/75 backdrop-blur-sm"
        @click.self="editSupplierOpen = false"
    >
        <div class="w-full max-w-[420px] bg-[#161B29] border-t sm:border border-[#232B3E] rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl relative">
            <div class="w-12 h-1.5 bg-[#232B3E] rounded-full mx-auto mb-4 sm:hidden"></div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-extrabold text-white">Ubah Data Pemasok</h2>
                <button type="button" @click="editSupplierOpen = false" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="'<?= url('/suppliers/') ?>' + editData.id + '/update'" method="POST" class="space-y-3.5">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Pemasok / Distributor *</label>
                    <input type="text" name="nama_pemasok" x-model="editData.nama" required class="w-full px-3.5 py-2.5 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor WhatsApp / HP</label>
                    <input type="tel" name="no_hp_pemasok" x-model="editData.nohp" class="w-full px-3.5 py-2.5 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Email</label>
                    <input type="email" name="email_pemasok" x-model="editData.email" class="w-full px-3.5 py-2.5 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Gudang / Toko</label>
                    <textarea name="alamat_pemasok" x-model="editData.alamat" rows="2" class="w-full px-3.5 py-2 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]"></textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] text-white text-xs font-bold rounded-xl shadow-lg transition">
                        Perbarui Pemasok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
