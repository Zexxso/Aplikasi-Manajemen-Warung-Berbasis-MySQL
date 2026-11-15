<?php
/**
 * @var string|null $status
 * @var array|null $errors
 */
$status = flash('status');
$errors = flash('errors');
?>
<div class="flex-1 flex flex-col justify-between px-6 py-8">
    <!-- Top Branding / Header -->
    <div class="pt-6 pb-4 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#3A3BE0] via-[#4B4EF0] to-[#6F7CF5] shadow-lg shadow-indigo-600/30 mb-4 ring-1 ring-white/20">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Lupa Kata Sandi</h1>
        <p class="text-sm text-slate-400 mt-1">Khusus akun Pemilik Warung</p>
    </div>

    <!-- Form Card -->
    <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-6 shadow-xl">
        <p class="text-xs text-slate-300 leading-relaxed mb-5">
            Masukkan alamat email yang terdaftar sebagai <strong>Pemilik Warung</strong>. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
        </p>

        <?php if ($status): ?>
            <div class="mb-4 p-3.5 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 text-xs font-medium">
                <?= htmlspecialchars($status) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-4 p-3.5 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 text-xs font-medium">
                <ul class="space-y-1">
                    <?php foreach ((array)$errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/forgot-password') ?>" class="space-y-4">
            <?= csrf_field() ?>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Alamat Email Pemilik Warung *
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="<?= old('email') ?>"
                        placeholder="nama@email.com"
                        required
                        autofocus
                        class="w-full pl-11 pr-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] min-h-[48px]"
                    >
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full min-h-[48px] px-4 py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] active:scale-[0.99] text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center space-x-2"
                >
                    <span>Kirim Tautan Reset Password</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </form>

        <div class="mt-5 pt-4 border-t border-[#232B3E]/60 text-center">
            <a
                href="<?= url('/login') ?>"
                class="text-xs text-slate-400 hover:text-white transition inline-flex items-center space-x-1"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Halaman Masuk</span>
            </a>
        </div>
    </div>

    <div class="py-4 text-center">
        <p class="text-[11px] text-slate-500">
            Akun Penjaga/Karyawan tidak memiliki email dan dapat meminta reset langsung kepada Pemilik Warung.
        </p>
    </div>
</div>

