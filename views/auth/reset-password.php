<?php
/**
 * @var string $token
 * @var string $email
 */
$errors = flash('errors');
?>
<div class="flex-1 flex flex-col justify-between px-6 py-8">
    <!-- Top Branding / Header -->
    <div class="pt-6 pb-4 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#3A3BE0] via-[#4B4EF0] to-[#6F7CF5] shadow-lg shadow-indigo-600/30 mb-4 ring-1 ring-white/20">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Atur Ulang Kata Sandi</h1>
        <p class="text-sm text-slate-400 mt-1">Buat kata sandi baru untuk akun Anda</p>
    </div>

    <!-- Form Card -->
    <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-6 shadow-xl">
        <?php if (!empty($errors)): ?>
            <div class="mb-4 p-3.5 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 text-xs font-medium">
                <ul class="space-y-1">
                    <?php foreach ((array)$errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/reset-password') ?>" class="space-y-4">
            <?= csrf_field() ?>

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Alamat Email
                </label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($email ?? old('email')) ?>"
                    required
                    readonly
                    class="w-full px-4 py-3 bg-[#0B0E17]/60 border border-[#232B3E] rounded-xl text-slate-400 text-sm focus:outline-none min-h-[48px]"
                >
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Kata Sandi Baru *
                </label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Minimal 6 karakter"
                    required
                    autofocus
                    autocomplete="new-password"
                    class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] min-h-[48px]"
                >
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Konfirmasi Kata Sandi Baru *
                </label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    placeholder="Ulangi kata sandi baru"
                    required
                    autocomplete="new-password"
                    class="w-full px-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] min-h-[48px]"
                >
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full min-h-[48px] px-4 py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] active:scale-[0.99] text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center space-x-2"
                >
                    <span>Perbarui Kata Sandi</span>
                </button>
            </div>
        </form>
    </div>

    <div class="py-4 text-center">
        <a href="<?= url('/login') ?>" class="text-xs text-slate-400 hover:text-white transition">
            ← Batal dan Kembali ke Halaman Masuk
        </a>
    </div>
</div>

