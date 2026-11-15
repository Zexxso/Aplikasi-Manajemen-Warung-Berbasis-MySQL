<?php
/**
 * @var array $globalProducts
 * @var array $globalCashCategories
 * @var array $globalProductCategories
 */
$productsData = array_map(function ($p) {
    $name = $p['nama'] ?? $p['name'] ?? '';
    $unit = $p['satuan'] ?? $p['unit'] ?? 'pcs';
    $buyPrice = (float) ($p['harga_beli'] ?? $p['buy_price'] ?? 0);
    $sellPrice = (float) ($p['harga_jual'] ?? $p['sell_price'] ?? 0);
    $stock = (int) ($p['stok'] ?? $p['stock'] ?? 0);

    return [
        'id' => (int) $p['id'],
        'nama' => (string) $name,
        'satuan' => (string) $unit,
        'harga_beli' => $buyPrice,
        'harga_jual' => $sellPrice,
        'stok' => $stock,
        'name' => (string) $name,
        'unit' => (string) $unit,
        'buy_price' => $buyPrice,
        'sell_price' => $sellPrice,
        'stock' => $stock,
    ];
}, $globalProducts ?? []);

$cashCategoriesData = array_map(function ($c) {
    $name = $c['nama'] ?? $c['name'] ?? '';
    $type = $c['jenis'] ?? $c['type'] ?? 'expense';
    return [
        'id' => (int) $c['id'],
        'nama' => (string) $name,
        'jenis' => (string) $type,
        'name' => (string) $name,
        'type' => (string) $type,
    ];
}, $globalCashCategories ?? []);

$productCategoriesData = array_map(function ($cat) {
    $name = $cat['nama'] ?? $cat['name'] ?? '';
    return [
        'id' => (int) $cat['id'],
        'nama' => (string) $name,
        'name' => (string) $name,
    ];
}, $globalProductCategories ?? []);

$customersData = array_map(function ($c) {
    $code = $c['kode'] ?? $c['code'] ?? '';
    $name = $c['nama'] ?? $c['name'] ?? '';
    $phone = $c['telepon'] ?? $c['phone'] ?? '';
    return [
        'id' => (int) $c['id'],
        'kode' => (string) $code,
        'nama' => (string) $name,
        'telepon' => (string) $phone,
        'code' => (string) $code,
        'name' => (string) $name,
        'phone' => (string) $phone,
    ];
}, $globalCustomers ?? []);
?>

<div
    x-data="{
        menuOpen: false,
        stockInOpen: false,
        stockOutOpen: false,
        cashOpen: false,
        quickProductOpen: false,
        productPickerOpen: false,
        pickerMode: 'stockIn',
        pickerSearch: '',

        allProducts: <?= htmlspecialchars(json_encode($productsData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>,
        cashCategories: <?= htmlspecialchars(json_encode($cashCategoriesData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>,
        productCategories: <?= htmlspecialchars(json_encode($productCategoriesData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>,
        allCustomers: <?= htmlspecialchars(json_encode($customersData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>,

        stockIn: {
            product: null,
            quantity: '',
            buy_price: '',
            note: '',
            get total() {
                const qty = parseInt(this.quantity) || 0;
                const price = parseFloat(this.buy_price) || 0;
                return qty * price;
            }
        },

        stockOut: {
            product: null,
            quantity: '',
            reason: 'sale',
            payment_method: 'cash',
            customer_name: '',
            note: '',
            get total() {
                const qty = parseInt(this.quantity) || 0;
                const price = this.product ? parseFloat(this.product.sell_price) : 0;
                return qty * price;
            },
            get isOverStock() {
                if (!this.product) return false;
                const qty = parseInt(this.quantity) || 0;
                return qty > this.product.stock;
            }
        },

        cash: {
            type: 'expense',
            category_id: '',
            amountFormatted: '',
            entry_date: '<?= date('Y-m-d') ?>',
            note: '',
            formatCurrency(e) {
                let val = e.target.value.replace(/\D/g, '');
                if (!val) {
                    this.amountFormatted = '';
                    return;
                }
                this.amountFormatted = 'Rp ' + Number(val).toLocaleString('id-ID');
            }
        },

        quickProduct: {
            name: '',
            category_id: '',
            unit: 'pcs',
            buy_price: '',
            sell_price: '',
            stock: 0,
            isSaving: false
        },

        openMenu() {
            this.closeAll();
            this.menuOpen = true;
            history.pushState({ sheet: 'menu' }, '');
        },
        openStockIn(product = null) {
            this.closeAll();
            this.stockIn.quantity = '';
            this.stockIn.note = '';
            if (product) {
                this.selectProductForStockIn(product);
            } else if (this.allProducts.length > 0 && !this.stockIn.product) {
                this.selectProductForStockIn(this.allProducts[0]);
            }
            this.stockInOpen = true;
            history.pushState({ sheet: 'stockIn' }, '');
        },
        openStockOut(product = null) {
            this.closeAll();
            this.stockOut.quantity = '';
            this.stockOut.reason = 'sale';
            this.stockOut.payment_method = 'cash';
            this.stockOut.customer_name = '';
            this.stockOut.note = '';
            if (product) {
                this.selectProductForStockOut(product);
            } else if (this.allProducts.length > 0 && !this.stockOut.product) {
                this.selectProductForStockOut(this.allProducts[0]);
            }
            this.stockOutOpen = true;
            history.pushState({ sheet: 'stockOut' }, '');
        },
        openCash(type = 'expense') {
            this.closeAll();
            this.cash.type = type;
            this.cash.amountFormatted = '';
            this.cash.note = '';
            this.cash.category_id = '';
            const available = this.filteredCashCategories;
            if (available.length > 0) {
                this.cash.category_id = available[0].id;
            }
            this.cashOpen = true;
            history.pushState({ sheet: 'cash' }, '');
        },
        openProductPicker(mode) {
            this.pickerMode = mode;
            this.pickerSearch = '';
            this.productPickerOpen = true;
            history.pushState({ sheet: 'picker' }, '');
        },
        closeAll() {
            this.menuOpen = false;
            this.stockInOpen = false;
            this.stockOutOpen = false;
            this.cashOpen = false;
            this.quickProductOpen = false;
            this.productPickerOpen = false;
        },
        selectProductForStockIn(p) {
            this.stockIn.product = p;
            this.stockIn.buy_price = p.buy_price;
            this.productPickerOpen = false;
        },
        selectProductForStockOut(p) {
            this.stockOut.product = p;
            this.productPickerOpen = false;
        },
        get filteredPickerProducts() {
            if (!this.pickerSearch.trim()) return this.allProducts;
            const q = this.pickerSearch.toLowerCase();
            return this.allProducts.filter(p => p.name.toLowerCase().includes(q));
        },
        get filteredCashCategories() {
            return this.cashCategories.filter(c => c.type === this.cash.type);
        },
        async submitQuickProduct() {
            if (!this.quickProduct.name || !this.quickProduct.buy_price || !this.quickProduct.sell_price) {
                alert('Mohon isi nama, harga beli, dan harga jual barang.');
                return;
            }
            this.quickProduct.isSaving = true;
            try {
                const response = await fetch('<?= url('/products/quick-store') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify(this.quickProduct)
                });
                const res = await response.json();
                if (res.success && res.product) {
                    this.allProducts.unshift(res.product);
                    if (this.pickerMode === 'stockIn') {
                        this.selectProductForStockIn(res.product);
                    } else {
                        this.selectProductForStockOut(res.product);
                    }
                    this.quickProductOpen = false;
                    this.quickProduct.name = '';
                    this.quickProduct.buy_price = '';
                    this.quickProduct.sell_price = '';
                } else if (res.message) {
                    alert(res.message);
                }
            } catch (err) {
                alert('Gagal menambahkan barang baru.');
            } finally {
                this.quickProduct.isSaving = false;
            }
        }
    }"
    @open-record-sheet.window="openMenu()"
    @open-stock-in.window="openStockIn($event.detail?.product)"
    @open-stock-out.window="openStockOut($event.detail?.product)"
    @open-cash.window="openCash($event.detail?.type ?? 'expense')"
    @popstate.window="
        if (quickProductOpen) { quickProductOpen = false; }
        else if (productPickerOpen) { productPickerOpen = false; }
        else if (stockInOpen) { stockInOpen = false; }
        else if (stockOutOpen) { stockOutOpen = false; }
        else if (cashOpen) { cashOpen = false; }
        else if (menuOpen) { menuOpen = false; }
    "
>
    <!-- ======================================================== -->
    <!-- 1. MENU UTAMA (+ CATAT): GRID 2X2 KARTU BESAR            -->
    <!-- ======================================================== -->
    <div x-show="menuOpen" x-cloak class="fixed inset-0 z-40 flex items-end justify-center">
        <!-- Backdrop -->
        <div
            x-show="menuOpen"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="menuOpen = false"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm"
        ></div>

        <!-- Sheet Modal Content -->
        <div
            x-show="menuOpen"
            x-transition:enter="transition ease-out duration-250 transform"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="relative w-full max-w-[412px] bg-[#161B29] border-t border-[#232B3E] rounded-t-3xl p-5 z-50 shadow-2xl pb-8"
        >
            <div class="w-12 h-1.5 bg-slate-700 rounded-full mx-auto mb-4"></div>
            <h3 class="text-base font-extrabold text-white text-center">Catat Transaksi Baru</h3>
            <p class="text-xs text-slate-400 text-center mb-5">Pilih salah satu menu di bawah ini</p>

            <!-- 2x2 Big Cards Grid -->
            <div class="grid grid-cols-2 gap-3 mb-4">
                <!-- 1. Barang Masuk -->
                <button
                    type="button"
                    @click="openStockIn()"
                    class="bg-[#0B0E17] hover:bg-[#1C2335] active:scale-95 border border-emerald-500/30 rounded-2xl p-4 flex flex-col items-center justify-center text-center transition min-h-[120px] shadow-sm group"
                >
                    <div class="w-12 h-12 rounded-2xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-400 flex items-center justify-center mb-2 shadow-inner group-hover:scale-105 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-white leading-tight">Barang Masuk</span>
                    <span class="text-[10px] text-slate-400 mt-0.5">Kulakan / Stok</span>
                </button>

                <!-- 2. Barang Keluar -->
                <button
                    type="button"
                    @click="openStockOut()"
                    class="bg-[#0B0E17] hover:bg-[#1C2335] active:scale-95 border border-indigo-500/30 rounded-2xl p-4 flex flex-col items-center justify-center text-center transition min-h-[120px] shadow-sm group"
                >
                    <div class="w-12 h-12 rounded-2xl bg-indigo-950/60 border border-indigo-500/40 text-indigo-400 flex items-center justify-center mb-2 shadow-inner group-hover:scale-105 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-white leading-tight">Barang Keluar</span>
                    <span class="text-[10px] text-slate-400 mt-0.5">Penjualan / Rusak</span>
                </button>

                <!-- 3. Pemasukan Kas (Green Accent) -->
                <button
                    type="button"
                    @click="openCash('income')"
                    class="bg-[#0B0E17] hover:bg-emerald-950/20 active:scale-95 border border-emerald-500/30 rounded-2xl p-4 flex flex-col items-center justify-center text-center transition min-h-[120px] shadow-sm group"
                >
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center mb-2 shadow-inner group-hover:scale-105 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-emerald-300 leading-tight">Pemasukan Kas</span>
                    <span class="text-[10px] text-slate-400 mt-0.5">Uang Masuk Lain</span>
                </button>

                <!-- 4. Pengeluaran Kas (Red Accent) -->
                <button
                    type="button"
                    @click="openCash('expense')"
                    class="bg-[#0B0E17] hover:bg-rose-950/20 active:scale-95 border border-rose-500/30 rounded-2xl p-4 flex flex-col items-center justify-center text-center transition min-h-[120px] shadow-sm group"
                >
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 flex items-center justify-center mb-2 shadow-inner group-hover:scale-105 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M20 12H4" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-rose-300 leading-tight">Pengeluaran Kas</span>
                    <span class="text-[10px] text-slate-400 mt-0.5">Listrik, Gas, Gaji</span>
                </button>
            </div>

            <button
                type="button"
                @click="menuOpen = false"
                class="w-full py-3 bg-[#0B0E17] border border-[#232B3E] text-slate-400 hover:text-white font-semibold text-xs rounded-xl transition"
            >
                Tutup
            </button>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 2. FORM BOTTOM SHEET: BARANG MASUK (KULAKAN)             -->
    <!-- ======================================================== -->
    <div x-show="stockInOpen" x-cloak class="fixed inset-0 z-40 flex items-end justify-center">
        <div
            x-show="stockInOpen"
            @click="stockInOpen = false"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm"
        ></div>

        <div
            x-show="stockInOpen"
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
                    <div class="w-8 h-8 rounded-xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Barang Masuk (Kulakan)</h3>
                        <p class="text-xs text-slate-400">Tambah stok & catat pengeluaran kas</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="stockInOpen = false"
                    class="w-8 h-8 rounded-full bg-[#0B0E17] border border-[#232B3E] text-slate-400 hover:text-white flex items-center justify-center"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="<?= url('/stock/in') ?>" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" :value="stockIn.product ? stockIn.product.id : ''">

                <!-- 1. Pilih Barang -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                        Pilih Barang <span class="text-rose-400">*</span>
                    </label>
                    <div
                        @click="openProductPicker('stockIn')"
                        class="w-full px-4 py-3 bg-[#0B0E17] hover:bg-[#1E2538] border border-[#232B3E] rounded-xl flex items-center justify-between cursor-pointer min-h-[48px] transition"
                    >
                        <template x-if="stockIn.product">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-bold text-white" x-text="stockIn.product.name"></span>
                                <span class="text-xs text-slate-400">(Stok: <span x-text="stockIn.product.stock"></span> <span x-text="stockIn.product.unit"></span>)</span>
                            </div>
                        </template>
                        <template x-if="!stockIn.product">
                            <span class="text-sm text-slate-400">-- Ketuk untuk pilih barang --</span>
                        </template>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- 2. Jumlah & Harga Beli -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="stock_in_quantity" class="block text-xs font-semibold text-slate-300 mb-1">
                            Jumlah Masuk <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            inputmode="numeric"
                            id="stock_in_quantity"
                            name="quantity"
                            x-model="stockIn.quantity"
                            placeholder="0"
                            required
                            class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-sm font-bold tabular-nums focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent min-h-[48px]"
                        >
                    </div>

                    <div>
                        <label for="stock_in_buy_price" class="block text-xs font-semibold text-slate-300 mb-1">
                            Harga Beli/Unit (Rp) <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            inputmode="numeric"
                            id="stock_in_buy_price"
                            name="buy_price"
                            x-model="stockIn.buy_price"
                            placeholder="0"
                            required
                            class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-sm font-bold tabular-nums focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent min-h-[48px]"
                        >
                    </div>
                </div>

                <!-- Subtotal Preview -->
                <div class="bg-[#0B0E17] border border-[#232B3E] rounded-xl p-3 flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium">Total Pengeluaran Kas:</span>
                    <span class="text-sm font-extrabold text-rose-400 tabular-nums">
                        Rp <span x-text="Number(stockIn.total).toLocaleString('id-ID')"></span>
                    </span>
                </div>

                <!-- 3. Catatan -->
                <div>
                    <label for="stock_in_note" class="block text-xs font-semibold text-slate-300 mb-1">
                        Catatan (Opsional)
                    </label>
                    <input
                        type="text"
                        id="stock_in_note"
                        name="note"
                        x-model="stockIn.note"
                        placeholder="Contoh: Belanja di grosir pasar"
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent min-h-[48px]"
                    >
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="!stockIn.product || !stockIn.quantity"
                        class="w-full min-h-[48px] px-4 py-3 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-900/30 transition flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Barang Masuk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 3. FORM BOTTOM SHEET: BARANG KELUAR                      -->
    <!-- ======================================================== -->
    <div x-show="stockOutOpen" x-cloak class="fixed inset-0 z-40 flex items-end justify-center">
        <div
            x-show="stockOutOpen"
            @click="stockOutOpen = false"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm"
        ></div>

        <div
            x-show="stockOutOpen"
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Barang Keluar</h3>
                        <p class="text-xs text-slate-400">Penjualan, pakai pribadi, atau rusak</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="stockOutOpen = false"
                    class="w-8 h-8 rounded-full bg-[#0B0E17] border border-[#232B3E] text-slate-400 hover:text-white flex items-center justify-center"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="<?= url('/stock/out') ?>" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" :value="stockOut.product ? stockOut.product.id : ''">

                <!-- 1. Pilih Barang -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                        Pilih Barang <span class="text-rose-400">*</span>
                    </label>
                    <div
                        @click="openProductPicker('stockOut')"
                        class="w-full px-4 py-3 bg-[#0B0E17] hover:bg-[#1E2538] border border-[#232B3E] rounded-xl flex items-center justify-between cursor-pointer min-h-[48px] transition"
                    >
                        <template x-if="stockOut.product">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-bold text-white" x-text="stockOut.product.name"></span>
                                <span class="text-xs font-semibold" :class="stockOut.product.stock <= 0 ? 'text-rose-400' : 'text-slate-400'">
                                    (Stok: <span x-text="stockOut.product.stock"></span> <span x-text="stockOut.product.unit"></span>)
                                </span>
                            </div>
                        </template>
                        <template x-if="!stockOut.product">
                            <span class="text-sm text-slate-400">-- Ketuk untuk pilih barang --</span>
                        </template>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- 2. Jumlah Keluar -->
                <div>
                    <label for="stock_out_quantity" class="block text-xs font-semibold text-slate-300 mb-1">
                        Jumlah Keluar <span class="text-rose-400">*</span>
                    </label>
                    <input
                        type="text"
                        inputmode="numeric"
                        id="stock_out_quantity"
                        name="quantity"
                        x-model="stockOut.quantity"
                        placeholder="0"
                        required
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-sm font-bold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >

                    <!-- Real-time Warning if stock not enough -->
                    <template x-if="stockOut.isOverStock">
                        <p class="text-xs text-rose-400 font-bold mt-1.5 flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Jumlah keluar melebihi stok fisik (<span x-text="stockOut.product.stock"></span> <span x-text="stockOut.product.unit"></span>)!</span>
                        </p>
                    </template>
                </div>

                <!-- 3. Alasan Barang Keluar -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Alasan Pengeluaran <span class="text-rose-400">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <label
                            @click="stockOut.reason = 'sale'"
                            :class="stockOut.reason === 'sale' ? 'bg-[#4B4EF0] text-white border-transparent' : 'bg-[#0B0E17] text-slate-300 border-[#232B3E]'"
                            class="border rounded-xl p-2.5 text-center cursor-pointer transition min-h-[48px] flex flex-col justify-center items-center"
                        >
                            <input type="radio" name="reason" value="sale" x-model="stockOut.reason" class="sr-only">
                            <span class="text-xs font-bold">Terjual</span>
                            <span class="text-[9px] opacity-80">+ Pemasukan</span>
                        </label>

                        <label
                            @click="stockOut.reason = 'personal'"
                            :class="stockOut.reason === 'personal' ? 'bg-[#4B4EF0] text-white border-transparent' : 'bg-[#0B0E17] text-slate-300 border-[#232B3E]'"
                            class="border rounded-xl p-2.5 text-center cursor-pointer transition min-h-[48px] flex flex-col justify-center items-center"
                        >
                            <input type="radio" name="reason" value="personal" x-model="stockOut.reason" class="sr-only">
                            <span class="text-xs font-bold">Pribadi</span>
                            <span class="text-[9px] opacity-80">Pakai sendiri</span>
                        </label>

                        <label
                            @click="stockOut.reason = 'damaged'"
                            :class="stockOut.reason === 'damaged' ? 'bg-[#4B4EF0] text-white border-transparent' : 'bg-[#0B0E17] text-slate-300 border-[#232B3E]'"
                            class="border rounded-xl p-2.5 text-center cursor-pointer transition min-h-[48px] flex flex-col justify-center items-center"
                        >
                            <input type="radio" name="reason" value="damaged" x-model="stockOut.reason" class="sr-only">
                            <span class="text-xs font-bold">Rusak</span>
                            <span class="text-[9px] opacity-80">Hilang/Koreksi</span>
                        </label>
                    </div>
                </div>

                <!-- 4. Pilihan Metode Pembayaran (Khusus Penjualan: Tunai vs Kasbon) -->
                <template x-if="stockOut.reason === 'sale'">
                    <div class="space-y-3 pt-1">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Status Pembayaran <span class="text-rose-400">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <!-- 1. Tunai -->
                                <label
                                    @click="stockOut.payment_method = 'cash'"
                                    :class="stockOut.payment_method === 'cash' ? 'bg-emerald-600 text-white border-transparent' : 'bg-[#0B0E17] text-slate-300 border-[#232B3E]'"
                                    class="border rounded-xl p-2 text-center cursor-pointer transition min-h-[48px] flex flex-col justify-center items-center"
                                >
                                    <input type="radio" name="payment_method" value="cash" x-model="stockOut.payment_method" class="sr-only">
                                    <span class="text-xs font-bold flex items-center space-x-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Tunai</span>
                                    </span>
                                    <span class="text-[9px] opacity-80 mt-0.5">Uang kas</span>
                                </label>

                                <!-- 2. Kasbon -->
                                <label
                                    @click="stockOut.payment_method = 'kasbon'"
                                    :class="stockOut.payment_method === 'kasbon' ? 'bg-amber-600 text-white border-transparent' : 'bg-[#0B0E17] text-slate-300 border-[#232B3E]'"
                                    class="border rounded-xl p-2 text-center cursor-pointer transition min-h-[48px] flex flex-col justify-center items-center"
                                >
                                    <input type="radio" name="payment_method" value="kasbon" x-model="stockOut.payment_method" class="sr-only">
                                    <span class="text-xs font-bold flex items-center space-x-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Kasbon</span>
                                    </span>
                                    <span class="text-[9px] opacity-80 mt-0.5">Hutang</span>
                                </label>
                            </div>
                        </div>

                        <!-- Preview Status Kas -->
                        <div
                            :class="stockOut.payment_method === 'cash' ? 'border-emerald-500/30 bg-emerald-950/20' : 'border-amber-500/30 bg-amber-950/20'"
                            class="border rounded-xl p-3 flex items-center justify-between"
                        >
                            <span class="text-xs text-slate-300 font-medium"
                                x-text="stockOut.payment_method === 'cash' ? 'Otomatis Masuk Kas:' : 'Dicatat di Buku Kasbon:'">
                            </span>
                            <span
                                :class="stockOut.payment_method === 'cash' ? 'text-emerald-400' : 'text-amber-400'"
                                class="text-sm font-extrabold tabular-nums"
                            >
                                <span x-text="stockOut.payment_method === 'kasbon' ? 'Rp ' : '+Rp '"></span><span x-text="Number(stockOut.total).toLocaleString('id-ID')"></span>
                            </span>
                        </div>

                        <!-- Nama Pembeli Khusus KASBON -->
                        <div x-show="stockOut.payment_method === 'kasbon'" x-cloak class="space-y-1.5">
                            <label for="stock_out_customer" class="block text-xs font-semibold text-amber-300">
                                Nama Pembeli yang Kasbon <span class="text-rose-400">*</span>
                            </label>
                            <input
                                type="text"
                                id="stock_out_customer"
                                name="customer_name"
                                x-model="stockOut.customer_name"
                                placeholder="Contoh: Pak RT Bambang, Bu Joko, Mas Doni"
                                autocomplete="off"
                                class="w-full px-4 py-3 bg-[#0B0E17] border border-amber-500/40 focus:border-amber-400 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50 min-h-[48px]"
                            >
                            <template x-if="allCustomers && allCustomers.length > 0">
                                <div class="flex items-center space-x-1.5 overflow-x-auto no-scrollbar pt-1">
                                    <span class="text-[10px] text-slate-500 flex-shrink-0">Pilih:</span>
                                    <template x-for="c in allCustomers" :key="c.id">
                                        <button
                                            type="button"
                                            @click="stockOut.customer_name = c.name"
                                            class="px-2 py-0.5 rounded-md bg-[#0B0E17] hover:bg-[#1E2538] border border-[#232B3E] hover:border-amber-500/40 text-[10px] text-slate-300 hover:text-white flex-shrink-0 transition"
                                            x-text="c.name"
                                        ></button>
                                    </template>
                                </div>
                            </template>
                            <p class="text-[10px] text-slate-400">
                                * Nama wajib diisi agar kasbon tercatat di Buku Kasbon pembeli tersebut.
                            </p>
                        </div>
                    </div>
                </template>

                <!-- 4. Catatan -->
                <div>
                    <label for="stock_out_note" class="block text-xs font-semibold text-slate-300 mb-1">
                        Catatan (Opsional)
                    </label>
                    <input
                        type="text"
                        id="stock_out_note"
                        name="note"
                        x-model="stockOut.note"
                        placeholder="Contoh: Pembeli bawa pulang langsung"
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="!stockOut.product || !stockOut.quantity || stockOut.isOverStock || (stockOut.reason === 'sale' && stockOut.payment_method === 'kasbon' && !stockOut.customer_name.trim())"
                        class="w-full min-h-[48px] px-4 py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="stockOut.reason === 'sale' && stockOut.payment_method === 'kasbon' ? 'Simpan Penjualan Kasbon' : 'Simpan Barang Keluar'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 4. FORM BOTTOM SHEET: CATAT KAS MANUAL (MASUK / KELUAR)  -->
    <!-- ======================================================== -->
    <div x-show="cashOpen" x-cloak class="fixed inset-0 z-40 flex items-end justify-center">
        <div
            x-show="cashOpen"
            @click="cashOpen = false"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm"
        ></div>

        <div
            x-show="cashOpen"
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
                    <div
                        class="w-8 h-8 rounded-xl flex items-center justify-center border"
                        :class="cash.type === 'income' ? 'bg-emerald-950/60 border-emerald-500/40 text-emerald-400' : 'bg-rose-950/60 border-rose-500/40 text-rose-400'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white" x-text="cash.type === 'income' ? 'Catat Pemasukan Kas' : 'Catat Pengeluaran Kas'"></h3>
                        <p class="text-xs text-slate-400">Buku kas harian di luar transaksi barang</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="cashOpen = false"
                    class="w-8 h-8 rounded-full bg-[#0B0E17] border border-[#232B3E] text-slate-400 hover:text-white flex items-center justify-center"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="<?= url('/cash') ?>" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="type" :value="cash.type">

                <!-- Type Selector Tabs (Pemasukan / Pengeluaran) -->
                <div class="bg-[#0B0E17] p-1 rounded-xl flex items-center border border-[#232B3E]">
                    <button
                        type="button"
                        @click="cash.type = 'income'"
                        :class="cash.type === 'income' ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'text-slate-400 hover:text-white'"
                        class="flex-1 py-2 rounded-lg text-xs transition min-h-[38px]"
                    >
                        + Pemasukan Kas
                    </button>
                    <button
                        type="button"
                        @click="cash.type = 'expense'"
                        :class="cash.type === 'expense' ? 'bg-rose-600 text-white font-bold shadow-sm' : 'text-slate-400 hover:text-white'"
                        class="flex-1 py-2 rounded-lg text-xs transition min-h-[38px]"
                    >
                        - Pengeluaran Kas
                    </button>
                </div>

                <!-- Nominal (Rupiah Formatted Text Input) -->
                <div>
                    <label for="cash_amount" class="block text-xs font-semibold text-slate-300 mb-1">
                        Nominal Uang (Rp) <span class="text-rose-400">*</span>
                    </label>
                    <input
                        type="text"
                        inputmode="numeric"
                        id="cash_amount"
                        name="amount"
                        x-model="cash.amountFormatted"
                        @input="cash.formatCurrency($event)"
                        placeholder="Rp 0"
                        required
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-base font-extrabold tabular-nums focus:outline-none focus:ring-2 focus:border-transparent min-h-[48px]"
                        :class="cash.type === 'income' ? 'focus:ring-emerald-500 text-emerald-400' : 'focus:ring-rose-500 text-rose-400'"
                    >
                </div>

                <!-- Kategori Kas -->
                <div>
                    <label for="cash_category_id" class="block text-xs font-semibold text-slate-300 mb-1">
                        Kategori Kas <span class="text-rose-400">*</span>
                    </label>
                    <select
                        id="cash_category_id"
                        name="cash_category_id"
                        x-model="cash.category_id"
                        required
                        class="w-full px-3 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                        <option value="">Pilih Kategori</option>
                        <template x-for="c in filteredCashCategories" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Tanggal Transaksi -->
                <div>
                    <label for="cash_entry_date" class="block text-xs font-semibold text-slate-300 mb-1">
                        Tanggal Transaksi
                    </label>
                    <input
                        type="date"
                        id="cash_entry_date"
                        name="entry_date"
                        x-model="cash.entry_date"
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <!-- Catatan Kas -->
                <div>
                    <label for="cash_note" class="block text-xs font-semibold text-slate-300 mb-1">
                        Catatan (Opsional)
                    </label>
                    <input
                        type="text"
                        id="cash_note"
                        name="note"
                        x-model="cash.note"
                        placeholder="Contoh: Token listrik toko 50rb"
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent min-h-[48px]"
                    >
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="!cash.amountFormatted || !cash.category_id"
                        :class="cash.type === 'income' ? 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-900/30' : 'bg-rose-600 hover:bg-rose-500 shadow-rose-900/30'"
                        class="w-full min-h-[48px] px-4 py-3 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-lg transition flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="cash.type === 'income' ? 'Simpan Pemasukan Kas' : 'Simpan Pengeluaran Kas'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 5. SEARCHABLE PRODUCT PICKER MODAL (WITH QUICK CREATE)   -->
    <!-- ======================================================== -->
    <div x-show="productPickerOpen" x-cloak class="fixed inset-0 z-50 flex items-end justify-center">
        <div
            x-show="productPickerOpen"
            @click="productPickerOpen = false"
            class="fixed inset-0 bg-black/80 backdrop-blur-sm"
        ></div>

        <div
            x-show="productPickerOpen"
            x-transition:enter="transition ease-out duration-250 transform"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="relative w-full max-w-[412px] bg-[#161B29] border-t border-[#232B3E] rounded-t-3xl p-5 z-50 shadow-2xl max-h-[85vh] flex flex-col"
        >
            <div class="w-12 h-1.5 bg-slate-700 rounded-full mx-auto mb-4"></div>

            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-bold text-white">Pilih Barang</h3>
                <!-- Shortcut Button "+ Buat Barang Baru" (PRD Section 10) -->
                <button
                    type="button"
                    @click="quickProductOpen = true"
                    class="py-1.5 px-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] text-white text-xs font-bold rounded-xl transition flex items-center space-x-1 whitespace-nowrap"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Buat Baru</span>
                </button>
            </div>

            <!-- Search Field inside Picker -->
            <div class="relative mb-3">
                <input
                    type="text"
                    x-model="pickerSearch"
                    placeholder="Ketik nama barang..."
                    class="w-full pl-9 pr-4 py-2.5 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Scrollable List of Products -->
            <div class="flex-1 overflow-y-auto space-y-2 no-scrollbar pr-1 max-h-[50vh]">
                <template x-for="p in filteredPickerProducts" :key="p.id">
                    <div
                        @click="pickerMode === 'stockIn' ? selectProductForStockIn(p) : selectProductForStockOut(p)"
                        class="p-3 bg-[#0B0E17] hover:bg-[#1E2538] border border-[#232B3E] rounded-xl cursor-pointer flex items-center justify-between transition group"
                    >
                        <div>
                            <h4 class="text-sm font-bold text-white group-hover:text-indigo-400 transition" x-text="p.name"></h4>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Stok: <span class="font-semibold text-white" x-text="p.stock"></span> <span x-text="p.unit"></span>
                                · Beli: Rp <span x-text="Number(p.buy_price).toLocaleString('id-ID')"></span>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-emerald-400 tabular-nums">
                                Rp <span x-text="Number(p.sell_price).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                    </div>
                </template>

                <template x-if="filteredPickerProducts.length === 0">
                    <div class="text-center py-8 text-slate-400 text-xs">
                        Tidak ada barang yang cocok.
                        <button
                            type="button"
                            @click="quickProductOpen = true"
                            class="block mx-auto mt-2 text-[#6F7CF5] font-bold hover:underline"
                        >
                            + Buat Barang Baru Sekarang
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 6. MODAL QUICK CREATE PRODUCT                            -->
    <!-- ======================================================== -->
    <div x-show="quickProductOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div
            x-show="quickProductOpen"
            @click="quickProductOpen = false"
            class="fixed inset-0 bg-black/85 backdrop-blur-sm"
        ></div>

        <div
            x-show="quickProductOpen"
            class="relative w-full max-w-[380px] bg-[#161B29] border border-[#232B3E] rounded-3xl p-5 z-50 shadow-2xl max-h-[90vh] overflow-y-auto no-scrollbar"
        >
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-white">Tambah Barang Cepat</h3>
                <button
                    type="button"
                    @click="quickProductOpen = false"
                    class="w-7 h-7 rounded-full bg-[#0B0E17] text-slate-400 hover:text-white flex items-center justify-center"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Barang *</label>
                    <input
                        type="text"
                        x-model="quickProduct.name"
                        placeholder="Contoh: Kopi Bubuk 100g"
                        class="w-full px-3 py-2.5 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]"
                    >
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Satuan *</label>
                        <input
                            type="text"
                            x-model="quickProduct.unit"
                            placeholder="pcs"
                            class="w-full px-3 py-2.5 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori</label>
                        <select
                            x-model="quickProduct.category_id"
                            class="w-full px-2 py-2.5 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]"
                        >
                            <option value="">Pilih</option>
                            <template x-for="cat in productCategories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Beli *</label>
                        <input
                            type="text"
                            inputmode="numeric"
                            x-model="quickProduct.buy_price"
                            placeholder="0"
                            class="w-full px-3 py-2.5 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs tabular-nums focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Jual *</label>
                        <input
                            type="text"
                            inputmode="numeric"
                            x-model="quickProduct.sell_price"
                            placeholder="0"
                            class="w-full px-3 py-2.5 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white text-xs tabular-nums focus:outline-none focus:ring-2 focus:ring-[#4B4EF0]"
                        >
                    </div>
                </div>

                <div class="pt-2">
                    <button
                        type="button"
                        @click="submitQuickProduct()"
                        :disabled="quickProduct.isSaving"
                        class="w-full py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center justify-center space-x-2"
                    >
                        <span x-text="quickProduct.isSaving ? 'Menyimpan...' : 'Simpan & Pilih'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

