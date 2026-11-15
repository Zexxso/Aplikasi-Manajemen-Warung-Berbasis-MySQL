<?php
/**
 * @var array $products
 * @var array $categories
 * @var string $initialFilter
 * @var string $initialCategory
 * @var string $initialSearch
 */
$productsData = array_map(function ($p) {
    $name = (string) ($p['nama'] ?? $p['name'] ?? '');
    $catId = !empty($p['id_kategori'] ?? $p['category_id']) ? (int) ($p['id_kategori'] ?? $p['category_id']) : null;
    $catName = (string) ($p['nama_kategori'] ?? $p['category_name'] ?? 'Tanpa Kategori');
    $unit = (string) ($p['satuan'] ?? $p['unit'] ?? 'pcs');
    $image = $p['gambar'] ?? $p['image'] ?? null;
    $buyPrice = (float) ($p['harga_beli'] ?? $p['buy_price'] ?? 0);
    $sellPrice = (float) ($p['harga_jual'] ?? $p['sell_price'] ?? 0);
    $stock = (int) ($p['stok'] ?? $p['stock'] ?? 0);
    $minStock = (int) ($p['stok_minimum'] ?? $p['min_stock'] ?? 5);

    return [
        'id' => (int) $p['id'],
        'nama' => $name,
        'name' => $name,
        'id_kategori' => $catId,
        'category_id' => $catId,
        'nama_kategori' => $catName,
        'category_name' => $catName,
        'satuan' => $unit,
        'unit' => $unit,
        'gambar' => $image,
        'image' => $image,
        'gambar_url' => !empty($image) ? url('/uploads/products/' . $image) : null,
        'image_url' => !empty($image) ? url('/uploads/products/' . $image) : null,
        'harga_beli' => $buyPrice,
        'buy_price' => $buyPrice,
        'harga_jual' => $sellPrice,
        'sell_price' => $sellPrice,
        'stok' => $stock,
        'stock' => $stock,
        'stok_minimum' => $minStock,
        'min_stock' => $minStock,
        'is_low_stock' => $stock <= $minStock,
    ];
}, $products ?? []);

$productError = flash('error');
$productStatus = flash('status');
?>
<div
    class="flex-1 flex flex-col px-4 pt-5 pb-6"
    x-data="{
        search: '<?= htmlspecialchars($initialSearch ?? '', ENT_QUOTES, 'UTF-8') ?>',
        selectedCategory: '<?= htmlspecialchars($initialCategory ?? '', ENT_QUOTES, 'UTF-8') ?>',
        lowStockFilter: <?= ($initialFilter ?? '') === 'low_stock' ? 'true' : 'false' ?>,
        createSheetOpen: <?= (!empty($productError) && old('name')) ? 'true' : 'false' ?>,
        editSheetOpen: false,
        deleteConfirmOpen: false,
        createImagePreview: null,
        editImagePreview: null,
        removeImage: false,
        editProduct: {
            id: null,
            name: '',
            category_id: '',
            unit: 'pcs',
            image: null,
            image_url: null,
            buy_price: 0,
            sell_price: 0,
            stock: 0,
            min_stock: 5,
            update_url: '',
            delete_url: ''
        },
        products: <?= htmlspecialchars(json_encode($productsData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>,
        get filteredProducts() {
            return this.products.filter(p => {
                const matchesSearch = this.search === '' || p.name.toLowerCase().includes(this.search.toLowerCase());
                const matchesCategory = this.selectedCategory === '' || p.category_id == this.selectedCategory;
                const matchesLowStock = !this.lowStockFilter || p.stock <= p.min_stock;

                return matchesSearch && matchesCategory && matchesLowStock;
            });
        },
        openCreate() {
            this.createImagePreview = null;
            this.createSheetOpen = true;
            history.pushState({ sheetOpen: 'createProduct' }, '');
        },
        closeCreate() {
            this.createSheetOpen = false;
            this.createImagePreview = null;
        },
        openEdit(data) {
            this.editProduct = { ...data };
            this.editImagePreview = null;
            this.removeImage = false;
            this.editSheetOpen = true;
            this.deleteConfirmOpen = false;
            history.pushState({ sheetOpen: 'editProduct' }, '');
        },
        closeEdit() {
            this.editSheetOpen = false;
            this.editImagePreview = null;
            this.removeImage = false;
            this.deleteConfirmOpen = false;
        },
        selectCategoryFilter(catId) {
            this.lowStockFilter = false;
            this.selectedCategory = catId;
        },
        toggleLowStock() {
            this.lowStockFilter = !this.lowStockFilter;
            if (this.lowStockFilter) {
                this.selectedCategory = '';
            }
        },
        clearAllFilters() {
            this.search = '';
            this.selectedCategory = '';
            this.lowStockFilter = false;
        }
    }"
    @open-edit-product.window="openEdit($event.detail)"
    @popstate.window="
        if (createSheetOpen) createSheetOpen = false;
        if (editSheetOpen) editSheetOpen = false;
        if (deleteConfirmOpen) deleteConfirmOpen = false;
    "
>
    <!-- Top Screen Bar (Title & + Tambah Button) -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Barang</h1>

        <button
            type="button"
            @click="openCreate()"
            class="min-h-[44px] px-4 py-2 bg-[#4B4EF0] hover:bg-[#3A3BE0] active:scale-95 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/30 transition flex items-center space-x-1.5"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah</span>
        </button>
    </div>

    <!-- Flash Alert Banners (Visible Immediately on Top) -->
    <?php if ($productError): ?>
        <div class="mb-4 p-3.5 bg-rose-950/70 border border-rose-500/40 rounded-2xl flex items-start space-x-3 text-rose-200 shadow-lg">
            <svg class="w-5 h-5 text-rose-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="flex-1 text-xs">
                <p class="font-bold text-white mb-0.5">Gagal Menyimpan Barang</p>
                <p><?= htmlspecialchars($productError) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($productStatus): ?>
        <div class="mb-4 p-3.5 bg-emerald-950/70 border border-emerald-500/40 rounded-2xl flex items-start space-x-3 text-emerald-200 shadow-lg">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <div class="flex-1 text-xs">
                <p class="font-bold text-white mb-0.5">Berhasil</p>
                <p><?= htmlspecialchars($productStatus) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Search Input Bar (Real-time & Responsive) -->
    <div class="relative mb-3">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input
            type="text"
            x-model="search"
            placeholder="Cari nama barang..."
            class="w-full pl-11 pr-10 py-3 bg-[#161B29] border border-[#232B3E] rounded-2xl text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent transition"
        >
        <button
            type="button"
            x-show="search.length > 0"
            x-cloak
            @click="search = ''"
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white"
            aria-label="Bersihkan pencarian"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Category & Stok Menipis Chip Filters (Horizontal Scrollable) -->
    <div class="flex items-center space-x-2 overflow-x-auto no-scrollbar pb-3 mb-2 -mx-4 px-4">
        <!-- Chip: Semua -->
        <button
            type="button"
            @click="selectCategoryFilter('')"
            :class="selectedCategory === '' && !lowStockFilter
                ? 'bg-[#4B4EF0] text-white border-transparent shadow-sm'
                : 'bg-[#161B29] text-slate-300 border-[#232B3E] hover:text-white'"
            class="px-4 py-2 rounded-full text-xs font-semibold border whitespace-nowrap transition min-h-[38px] flex items-center"
        >
            Semua
        </button>

        <!-- Chip: Stok Menipis Special Filter -->
        <button
            type="button"
            @click="toggleLowStock()"
            :class="lowStockFilter
                ? 'bg-amber-500 text-slate-950 font-bold border-amber-400 shadow-sm'
                : 'bg-[#251A0A] text-amber-400 border-amber-500/40 hover:bg-[#2F210D]'"
            class="px-3.5 py-2 rounded-full text-xs font-semibold border whitespace-nowrap transition min-h-[38px] flex items-center space-x-1.5"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>Stok Menipis</span>
        </button>

        <!-- Dynamic Category Chips -->
        <?php foreach (($categories ?? []) as $cat): ?>
            <button
                type="button"
                @click="selectCategoryFilter('<?= $cat['id'] ?>')"
                :class="selectedCategory == '<?= $cat['id'] ?>' && !lowStockFilter
                    ? 'bg-[#4B4EF0] text-white border-transparent shadow-sm'
                    : 'bg-[#161B29] text-slate-300 border-[#232B3E] hover:text-white'"
                class="px-4 py-2 rounded-full text-xs font-semibold border whitespace-nowrap transition min-h-[38px] flex items-center"
            >
                <?= htmlspecialchars($cat['name']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Product Cards List -->
    <div class="flex-1">
        <?php if (empty($products)): ?>
            <!-- Global Empty State: No Products in Database -->
            <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-8 text-center shadow-sm mt-4">
                <div class="w-16 h-16 rounded-2xl bg-[#0B0E17] border border-[#232B3E] text-indigo-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Belum Ada Barang</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-[240px] mx-auto">
                    Mulai daftarkan barang dagangan warung Anda agar stok dan harga tercatat rapi.
                </p>
                <button
                    type="button"
                    @click="openCreate()"
                    class="mt-5 min-h-[48px] px-5 py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition inline-flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Barang Pertama</span>
                </button>
            </div>
        <?php else: ?>
            <!-- Filtered List using Alpine -->
            <div x-show="filteredProducts.length > 0">
                <template x-for="p in filteredProducts" :key="p.id">
                    <div
                        class="bg-[#161B29] hover:bg-[#1C2335] active:scale-[0.99] border border-[#232B3E] rounded-2xl p-3.5 mb-2.5 transition flex items-center justify-between cursor-pointer shadow-sm group"
                        @click="openEdit({
                            id: p.id,
                            name: p.name,
                            category_id: p.category_id ?? '',
                            unit: p.unit,
                            image: p.image ?? null,
                            image_url: p.image_url ?? null,
                            buy_price: p.buy_price,
                            sell_price: p.sell_price,
                            stock: p.stock,
                            min_stock: p.min_stock,
                            update_url: '<?= url('/products') ?>/' + p.id,
                            delete_url: '<?= url('/products') ?>/' + p.id
                        })"
                    >
                        <div class="flex items-center space-x-3.5 flex-1 min-w-0 pr-3">
                            <!-- Product Avatar: Image or Letter Avatar Square -->
                            <template x-if="p.image_url">
                                <img
                                    :src="p.image_url"
                                    :alt="p.name"
                                    class="w-11 h-11 rounded-2xl object-cover border border-[#232B3E] flex-shrink-0 shadow-inner group-hover:scale-105 transition"
                                >
                            </template>
                            <template x-if="!p.image_url">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-[#1E2436] border border-indigo-500/20 text-[#6F7CF5] font-extrabold text-base flex items-center justify-center flex-shrink-0 shadow-inner group-hover:scale-105 transition"
                                    x-text="p.name.charAt(0).toUpperCase()"
                                ></div>
                            </template>

                            <!-- Name, Category, Stock & Alert Badge -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-white truncate" x-text="p.name"></h4>
                                <p class="text-xs text-slate-400 mt-0.5 truncate">
                                    <span x-text="p.category_name"></span> · Stok <span x-text="p.stock"></span> <span x-text="p.unit"></span>
                                </p>

                                <div class="mt-1" x-show="p.stock <= p.min_stock">
                                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md bg-[#33220E] border border-amber-500/40 text-amber-400 text-[10px] font-bold">
                                        <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>Stok Menipis</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Sell Price & Chevron -->
                        <div class="text-right flex-shrink-0 flex items-center space-x-1.5">
                            <div>
                                <p class="text-sm font-extrabold text-white tabular-nums">
                                    Rp <span x-text="Number(p.sell_price).toLocaleString('id-ID')"></span>
                                </p>
                            </div>
                            <svg class="w-4 h-4 text-slate-500 group-hover:text-slate-300 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Search/Filter Empty State -->
            <div
                x-show="filteredProducts.length === 0"
                x-cloak
                class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-8 text-center shadow-sm mt-4"
            >
                <div class="w-14 h-14 rounded-2xl bg-[#0B0E17] border border-[#232B3E] text-slate-500 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-white">Barang Tidak Ditemukan</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-[220px] mx-auto">
                    Tidak ada barang yang cocok dengan kata kunci atau filter yang Anda pilih.
                </p>
                <button
                    type="button"
                    @click="clearAllFilters()"
                    class="mt-4 px-4 py-2 bg-[#0B0E17] border border-[#232B3E] hover:bg-slate-900 text-indigo-400 text-xs font-bold rounded-xl transition"
                >
                    Reset Pencarian & Filter
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- ======================================================== -->
    <!-- BOTTOM SHEET: TAMBAH BARANG BARU                         -->
    <!-- ======================================================== -->
    <div
        x-show="createSheetOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end justify-center"
    >
        <!-- Overlay Backdrop -->
        <div
            x-show="createSheetOpen"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeCreate()"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm"
        ></div>

        <!-- Sheet Content -->
        <div
            x-show="createSheetOpen"
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
                <div>
                    <h3 class="text-lg font-bold text-white">Tambah Barang Baru</h3>
                    <p class="text-xs text-slate-400">Masukkan data stok dan harga barang</p>
                </div>
                <button
                    type="button"
                    @click="closeCreate()"
                    class="w-8 h-8 rounded-full bg-[#0B0E17] border border-[#232B3E] text-slate-400 hover:text-white flex items-center justify-center transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <?php if ($productError && old('name')): ?>
                <div class="mb-4 p-3 bg-rose-950/70 border border-rose-500/40 rounded-xl text-xs text-rose-300 flex items-start space-x-2">
                    <svg class="w-4 h-4 text-rose-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span><?= htmlspecialchars($productError) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= url('/products') ?>" enctype="multipart/form-data" class="space-y-4">
                <?= csrf_field() ?>

                <!-- Foto Barang (Opsional) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Foto Barang (Opsional)
                    </label>
                    <div class="flex items-center space-x-3.5">
                        <!-- Preview Box -->
                        <div class="relative w-14 h-14 rounded-2xl bg-[#0B0E17] border border-[#232B3E] flex items-center justify-center overflow-hidden flex-shrink-0 shadow-inner">
                            <template x-if="createImagePreview">
                                <img :src="createImagePreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!createImagePreview">
                                <div class="text-slate-500 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </template>
                        </div>

                        <!-- Upload Button & Info -->
                        <div class="flex-1">
                            <label class="inline-flex items-center px-3.5 py-2 bg-[#0B0E17] hover:bg-[#1E2538] border border-[#232B3E] rounded-xl text-xs font-semibold text-slate-200 cursor-pointer transition">
                                <svg class="w-4 h-4 mr-1.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span x-text="createImagePreview ? 'Ganti Foto' : 'Pilih Foto'"></span>
                                <input
                                    type="file"
                                    name="image"
                                    accept="image/*, .jpg, .jpeg, .jfif, .png, .webp, .gif, .avif"
                                    class="hidden"
                                    @change="
                                        const file = $event.target.files[0];
                                        if (file) {
                                            createImagePreview = URL.createObjectURL(file);
                                        } else {
                                            createImagePreview = null;
                                        }
                                    "
                                >
                            </label>
                            <button
                                type="button"
                                x-show="createImagePreview"
                                x-cloak
                                @click="
                                    createImagePreview = null;
                                    $el.closest('form').querySelector('input[name=image]').value = '';
                                "
                                class="ml-2 text-[11px] text-rose-400 hover:text-rose-300 font-semibold transition"
                            >
                                Hapus
                            </button>
                            <p class="text-[10px] text-slate-500 mt-1">Kosongkan jika ingin memakai inisial huruf otomatis.</p>
                        </div>
                    </div>
                </div>

                <!-- 1. Nama Barang -->
                <div>
                    <label for="create_name" class="block text-xs font-semibold text-slate-300 mb-1">
                        Nama Barang <span class="text-rose-400">*</span>
                    </label>
                    <input
                        type="text"
                        id="create_name"
                        name="name"
                        value="<?= old('name') ?>"
                        placeholder="Contoh: Beras Rojo Lele 5kg"
                        required
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <!-- 2. Kategori & Satuan -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="create_category_id" class="block text-xs font-semibold text-slate-300 mb-1">
                            Kategori
                        </label>
                        <select
                            id="create_category_id"
                            name="category_id"
                            class="w-full px-3 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                        >
                            <option value="">Pilih Kategori</option>
                            <?php foreach (($categories ?? []) as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= old('category_id') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="create_unit" class="block text-xs font-semibold text-slate-300 mb-1">
                            Satuan <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            id="create_unit"
                            name="unit"
                            placeholder="pcs / kg / botol"
                            required
                            value="<?= old('unit', 'pcs') ?>"
                            class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                        >
                    </div>
                </div>

                <!-- 3. Harga Beli & Harga Jual -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="create_buy_price" class="block text-xs font-semibold text-slate-300 mb-1">
                            Harga Beli (Rp) <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            inputmode="numeric"
                            id="create_buy_price"
                            name="buy_price"
                            placeholder="0"
                            value="<?= old('buy_price') ?>"
                            required
                            class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                        >
                    </div>

                    <div>
                        <label for="create_sell_price" class="block text-xs font-semibold text-slate-300 mb-1">
                            Harga Jual (Rp) <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            inputmode="numeric"
                            id="create_sell_price"
                            name="sell_price"
                            placeholder="0"
                            value="<?= old('sell_price') ?>"
                            required
                            class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                        >
                    </div>
                </div>

                <!-- 4. Stok Awal & Stok Minimum -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="create_stock" class="block text-xs font-semibold text-slate-300 mb-1">
                            Stok Awal <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            inputmode="numeric"
                            id="create_stock"
                            name="stock"
                            placeholder="0"
                            required
                            value="<?= old('stock', '0') ?>"
                            class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                        >
                    </div>

                    <div>
                        <label for="create_min_stock" class="block text-xs font-semibold text-slate-300 mb-1">
                            Stok Minimum <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            inputmode="numeric"
                            id="create_min_stock"
                            name="min_stock"
                            placeholder="5"
                            required
                            value="<?= old('min_stock', '5') ?>"
                            class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                        >
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full min-h-[48px] px-4 py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] active:scale-[0.99] text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Barang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- BOTTOM SHEET: UBAH & HAPUS BARANG                        -->
    <!-- ======================================================== -->
    <div
        x-show="editSheetOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end justify-center"
    >
        <!-- Overlay Backdrop -->
        <div
            x-show="editSheetOpen"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeEdit()"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm"
        ></div>

        <!-- Sheet Content -->
        <div
            x-show="editSheetOpen"
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
                <div>
                    <h3 class="text-lg font-bold text-white">Ubah Data Barang</h3>
                    <p class="text-xs text-slate-400" x-text="editProduct.name"></p>
                </div>
                <button
                    type="button"
                    @click="closeEdit()"
                    class="w-8 h-8 rounded-full bg-[#0B0E17] border border-[#232B3E] text-slate-400 hover:text-white flex items-center justify-center transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Edit Form -->
            <form :action="editProduct.update_url" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">

                <!-- Foto Barang (Opsional) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Foto Barang (Opsional)
                    </label>
                    <div class="flex items-center space-x-3.5">
                        <!-- Preview Box -->
                        <div class="relative w-14 h-14 rounded-2xl bg-[#0B0E17] border border-[#232B3E] flex items-center justify-center overflow-hidden flex-shrink-0 shadow-inner">
                            <template x-if="editImagePreview">
                                <img :src="editImagePreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!editImagePreview && editProduct.image_url && !removeImage">
                                <img :src="editProduct.image_url" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!editImagePreview && (!editProduct.image_url || removeImage)">
                                <div
                                    class="w-full h-full bg-[#1E2436] text-[#6F7CF5] font-extrabold text-lg flex items-center justify-center"
                                    x-text="editProduct.name ? editProduct.name.charAt(0).toUpperCase() : '?'"
                                ></div>
                            </template>
                        </div>

                        <!-- Upload Button & Info -->
                        <div class="flex-1">
                            <input type="hidden" name="remove_image" :value="removeImage ? '1' : '0'">
                            <label class="inline-flex items-center px-3.5 py-2 bg-[#0B0E17] hover:bg-[#1E2538] border border-[#232B3E] rounded-xl text-xs font-semibold text-slate-200 cursor-pointer transition">
                                <svg class="w-4 h-4 mr-1.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span x-text="editImagePreview || (editProduct.image_url && !removeImage) ? 'Ganti Foto' : 'Pilih Foto'"></span>
                                <input
                                    type="file"
                                    name="image"
                                    accept="image/*, .jpg, .jpeg, .jfif, .png, .webp, .gif, .avif"
                                    class="hidden"
                                    @change="
                                        const file = $event.target.files[0];
                                        if (file) {
                                            editImagePreview = URL.createObjectURL(file);
                                            removeImage = false;
                                        } else {
                                            editImagePreview = null;
                                        }
                                    "
                                >
                            </label>
                            <template x-if="editImagePreview">
                                <button
                                    type="button"
                                    @click="
                                        editImagePreview = null;
                                        $el.closest('form').querySelector('input[name=image]').value = '';
                                    "
                                    class="ml-2 text-[11px] text-rose-400 hover:text-rose-300 font-semibold transition"
                                >
                                    Batal
                                </button>
                            </template>
                            <template x-if="!editImagePreview && editProduct.image_url && !removeImage">
                                <button
                                    type="button"
                                    @click="removeImage = true"
                                    class="ml-2 text-[11px] text-rose-400 hover:text-rose-300 font-semibold transition"
                                >
                                    Hapus Foto
                                </button>
                            </template>
                            <template x-if="!editImagePreview && editProduct.image_url && removeImage">
                                <span class="inline-flex items-center ml-2 text-[11px] text-amber-400 font-semibold">
                                    <span>Foto akan dihapus</span>
                                    <button
                                        type="button"
                                        @click="removeImage = false"
                                        class="ml-1 text-slate-400 hover:text-white underline text-[10px]"
                                    >
                                        (Batal)
                                    </button>
                                </span>
                            </template>
                            <p class="text-[10px] text-slate-500 mt-1">Biarkan kosong jika tidak ingin mengubah foto barang.</p>
                        </div>
                    </div>
                </div>

                <!-- 1. Nama Barang -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                        Nama Barang <span class="text-rose-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        x-model="editProduct.name"
                        required
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <!-- 2. Kategori & Satuan -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">
                            Kategori
                        </label>
                        <select
                            name="category_id"
                            x-model="editProduct.category_id"
                            class="w-full px-3 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                        >
                            <option value="">Tanpa Kategori</option>
                            <?php foreach (($categories ?? []) as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">
                            Satuan <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="unit"
                            x-model="editProduct.unit"
                            required
                            class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                        >
                    </div>
                </div>

                <!-- 3. Harga Beli & Harga Jual -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">
                            Harga Beli (Rp) <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            inputmode="numeric"
                            name="buy_price"
                            x-model="editProduct.buy_price"
                            required
                            class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">
                            Harga Jual (Rp) <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            inputmode="numeric"
                            name="sell_price"
                            x-model="editProduct.sell_price"
                            required
                            class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                        >
                    </div>
                </div>

                <!-- 4. Stok Saat Ini & Stok Minimum -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">
                            Stok Saat Ini <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            inputmode="numeric"
                            name="stock"
                            x-model="editProduct.stock"
                            required
                            class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">
                            Stok Minimum <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            inputmode="numeric"
                            name="min_stock"
                            x-model="editProduct.min_stock"
                            required
                            class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                        >
                    </div>
                </div>

                <!-- Submit Save Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full min-h-[48px] px-4 py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] active:scale-[0.99] text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>

            <!-- Delete Action Trigger -->
            <div class="mt-4 pt-3 border-t border-[#232B3E]">
                <div x-show="!deleteConfirmOpen">
                    <button
                        type="button"
                        @click="deleteConfirmOpen = true"
                        class="w-full min-h-[48px] px-4 py-2.5 bg-rose-950/40 hover:bg-rose-900/60 border border-rose-500/30 text-rose-300 font-semibold text-xs rounded-xl transition flex items-center justify-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Hapus Barang Ini (Soft Delete)</span>
                    </button>
                </div>

                <!-- Delete Confirmation Box -->
                <div
                    x-show="deleteConfirmOpen"
                    x-cloak
                    class="bg-[#0B0E17] border border-rose-500/40 rounded-2xl p-4 text-center space-y-3"
                >
                    <p class="text-xs text-rose-200 font-medium">
                        Yakin ingin menghapus <strong class="text-white" x-text="editProduct.name"></strong>? Data transaksi mutasi yang pernah dicatat tetap tersimpan aman.
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="deleteConfirmOpen = false"
                            class="py-2.5 px-3 bg-[#161B29] border border-[#232B3E] text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-800 transition"
                        >
                            Batal
                        </button>
                        <form :action="editProduct.delete_url" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button
                                type="submit"
                                class="w-full py-2.5 px-3 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md transition"
                            >
                                Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

