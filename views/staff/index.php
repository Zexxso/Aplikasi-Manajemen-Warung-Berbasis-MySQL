<?php
/**
 * @var array $staffMembers
 * @var array $owner
 */
?>
<div
    x-data="{
        addStaffOpen: false,
        resetModalOpen: false,
        resetStaffId: null,
        resetStaffName: '',
        confirmReset(id, name) {
            this.resetStaffId = id;
            this.resetStaffName = name;
            this.resetModalOpen = true;
        }
    }"
    class="flex-1 flex flex-col pb-24"
>
    <!-- Top App Bar with Back to Dashboard -->
    <header class="px-4 pt-6 pb-4 flex items-center justify-between border-b border-[#232B3E]">
        <div class="flex items-center space-x-3">
            <a
                href="<?= url('/dashboard') ?>"
                class="w-10 h-10 rounded-xl bg-[#161B29] border border-[#232B3E] text-slate-300 hover:text-white flex items-center justify-center transition"
                aria-label="Kembali ke Beranda"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-white leading-tight">Kelola Karyawan</h1>
                <p class="text-xs text-slate-400 mt-0.5"><?= count($staffMembers) ?> penjaga terdaftar</p>
            </div>
        </div>

        <!-- Add Staff Trigger Button -->
        <button
            type="button"
            @click="addStaffOpen = true"
            class="px-3.5 py-2 bg-[#4B4EF0] hover:bg-[#3A3BE0] active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/30 transition flex items-center space-x-1.5"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah</span>
        </button>
    </header>

    <!-- Main Content -->
    <main class="px-4 py-4 flex-1">
        <!-- Instruction Box -->
        <div class="bg-[#161B29]/70 border border-[#232B3E] rounded-2xl p-3.5 mb-4 text-xs text-slate-300 flex items-start space-x-2.5">
            <svg class="w-5 h-5 text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>
                Karyawan dapat mencatat barang masuk, keluar, dan transaksi kas. Menu Laporan dan Pengaturan hanya dapat diakses oleh Anda sebagai Pemilik Warung.
            </p>
        </div>

        <?php if (empty($staffMembers)): ?>
            <!-- Empty State -->
            <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-8 text-center shadow-sm my-4">
                <div class="w-14 h-14 rounded-2xl bg-[#0B0E17] border border-[#232B3E] text-slate-500 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Belum Ada Karyawan</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-[240px] mx-auto">
                    Tambahkan akun staf/penjaga untuk membantu operasional warung Anda dengan aman.
                </p>
                <button
                    type="button"
                    @click="addStaffOpen = true"
                    class="mt-4 px-4 py-2.5 bg-[#4B4EF0] text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/30 active:scale-95 transition"
                >
                    + Tambah Karyawan Pertama
                </button>
            </div>
        <?php else: ?>
            <!-- Staff List -->
            <div class="space-y-3">
                <?php foreach ($staffMembers as $staff): 
                    $staffName = $staff['nama'] ?? $staff['name'] ?? 'Karyawan';
                    $staffPhone = $staff['telepon'] ?? $staff['phone'] ?? '';
                    $isActive = !empty($staff['status_aktif'] ?? $staff['is_active']);
                ?>
                    <div class="bg-[#161B29] border border-[#232B3E] rounded-2xl p-4 shadow-sm hover:border-[#323D57] transition">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <!-- Avatar -->
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-[#3A3BE0] to-[#6F7CF5] text-white font-extrabold text-sm flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <?= htmlspecialchars(strtoupper(substr($staffName, 0, 2))) ?>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white leading-tight"><?= htmlspecialchars($staffName) ?></h3>
                                    <p class="text-xs text-slate-400 flex items-center space-x-1 mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <span><?= htmlspecialchars($staffPhone) ?></span>
                                    </p>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div>
                                <?php if ($isActive): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-950/60 text-emerald-400 border border-emerald-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5"></span>
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-950/60 text-rose-400 border border-rose-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 mr-1.5"></span>
                                        Nonaktif
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Actions Row -->
                        <div class="flex items-center space-x-2 pt-3 border-t border-[#232B3E]/80">
                            <!-- Reset Password Button -->
                            <button
                                type="button"
                                @click="confirmReset(<?= $staff['id'] ?>, '<?= addslashes(htmlspecialchars($staffName)) ?>')"
                                class="flex-1 py-2 px-3 bg-[#0B0E17] hover:bg-[#1E2538] border border-[#232B3E] hover:border-amber-500/40 text-amber-300 text-xs font-semibold rounded-xl transition flex items-center justify-center space-x-1.5 min-h-[44px]"
                            >
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                <span>Reset Sandi</span>
                            </button>

                            <!-- Toggle Status Form -->
                            <form
                                action="<?= url('/staff/' . $staff['id'] . '/toggle-status') ?>"
                                method="POST"
                                class="flex-1"
                            >
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="PATCH">
                                <button
                                    type="submit"
                                    class="w-full py-2 px-3 <?= $isActive ? 'bg-rose-950/40 hover:bg-rose-950/70 border border-rose-500/30 text-rose-300' : 'bg-emerald-950/40 hover:bg-emerald-950/70 border border-emerald-500/30 text-emerald-300' ?> text-xs font-semibold rounded-xl transition flex items-center justify-center space-x-1.5 min-h-[44px]"
                                >
                                    <?php if ($isActive): ?>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        <span>Nonaktifkan</span>
                                    <?php else: ?>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Aktifkan</span>
                                    <?php endif; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Bottom Sheet Modal: Tambah Karyawan Baru -->
    <div
        x-show="addStaffOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/75 backdrop-blur-sm"
        @click.self="addStaffOpen = false"
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

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-extrabold text-white">Tambah Karyawan Baru</h2>
                <button
                    type="button"
                    @click="addStaffOpen = false"
                    class="text-slate-400 hover:text-white"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="<?= url('/staff') ?>" class="space-y-4">
                <?= csrf_field() ?>

                <!-- Nama -->
                <div>
                    <label for="staff_name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Nama Karyawan / Penjaga *
                    </label>
                    <input
                        id="staff_name"
                        type="text"
                        name="name"
                        placeholder="Contoh: Budi Santoso"
                        required
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] min-h-[48px]"
                    >
                </div>

                <!-- No HP -->
                <div>
                    <label for="staff_phone" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Nomor HP (digunakan untuk login) *
                    </label>
                    <input
                        id="staff_phone"
                        type="tel"
                        inputmode="tel"
                        name="phone"
                        placeholder="081234567890"
                        required
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm tabular-nums focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] min-h-[48px]"
                    >
                </div>

                <!-- Password Default -->
                <div>
                    <label for="staff_password" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Kata Sandi Default *
                    </label>
                    <input
                        id="staff_password"
                        type="text"
                        name="password"
                        value="123456"
                        required
                        class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] min-h-[48px]"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">
                        Kata sandi default ini dapat langsung digunakan karyawan untuk masuk ke aplikasi.
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full py-3.5 px-4 bg-[#4B4EF0] hover:bg-[#3A3BE0] active:scale-[0.99] text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/30 transition min-h-[48px]"
                    >
                        Simpan Akun Karyawan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal: Reset Password -->
    <div
        x-show="resetModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/75 backdrop-blur-sm"
        @click.self="resetModalOpen = false"
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

            <div class="flex items-start space-x-3 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-white">Reset Kata Sandi?</h3>
                    <p class="text-xs text-slate-300 mt-1">
                        Kembalikan kata sandi akun <strong class="text-white" x-text="resetStaffName"></strong> ke default: <strong class="text-amber-300">123456</strong>?
                    </p>
                </div>
            </div>

            <p class="text-[11px] text-slate-400 mb-5 bg-[#0B0E17] p-3 rounded-xl border border-[#232B3E]">
                Reset kata sandi instan tanpa OTP atau Email. Karyawan dapat langsung masuk menggunakan Nomor HP dan kata sandi 123456.
            </p>

            <form
                :action="'<?= url('/staff') ?>/' + resetStaffId + '/reset-password'"
                method="POST"
                class="space-y-2.5"
            >
                <?= csrf_field() ?>
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 bg-amber-500 hover:bg-amber-400 active:scale-[0.99] text-black font-bold text-sm rounded-2xl shadow-lg shadow-amber-500/20 transition min-h-[48px]"
                >
                    Ya, Reset ke 123456
                </button>
                <button
                    type="button"
                    @click="resetModalOpen = false"
                    class="w-full py-3 px-4 bg-[#232B3E] hover:bg-[#2C364D] text-slate-200 font-semibold text-sm rounded-2xl transition min-h-[48px]"
                >
                    Batal
                </button>
            </form>
        </div>
    </div>
</div>

