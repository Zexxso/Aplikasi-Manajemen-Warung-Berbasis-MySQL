<?php
/**
 * @var string|null $status
 * @var array|null $errors
 */
$status = flash('status');
$errors = flash('errors');
?>
<div class="flex-1 flex flex-col justify-between px-6 py-8" x-data="{
    loginInput: '<?= old('login', '') ?>',
    passwordInput: '',
    showPassword: false,
    fillDemo() {
        this.loginInput = 'busari@warungku.id';
        this.passwordInput = 'password123';
    }
}">
    <!-- Top Branding / Header -->
    <div class="pt-6 pb-4 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#3A3BE0] via-[#4B4EF0] to-[#6F7CF5] shadow-lg shadow-indigo-600/30 mb-4 ring-1 ring-white/20">
            <!-- Shop / Storefront Icon -->
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Warungku</h1>
        <p class="text-sm text-slate-400 mt-1">Kelola stok & arus kas warung langsung dari HP</p>
    </div>

    <!-- Form Card -->
    <div class="bg-[#161B29] border border-[#232B3E] rounded-3xl p-6 shadow-xl">
        <h2 class="text-lg font-bold text-white mb-1">Masuk Pemilik Warung</h2>
        <p class="text-xs text-slate-400 mb-5">Gunakan Email atau Nomor HP yang terdaftar</p>

        <?php if ($status): ?>
            <div class="mb-4 p-3 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 text-xs font-medium">
                <?= htmlspecialchars($status) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-4 p-3 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 text-xs font-medium">
                <ul class="space-y-1">
                    <?php foreach ((array)$errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/login') ?>" class="space-y-4">
            <?= csrf_field() ?>

            <!-- Login (Email / Phone) -->
            <div>
                <label for="login" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Email atau Nomor HP
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input
                        id="login"
                        type="text"
                        name="login"
                        x-model="loginInput"
                        placeholder="nama@email.com atau 0812..."
                        required
                        autocomplete="username"
                        class="w-full pl-11 pr-4 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent transition"
                    >
                </div>
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold text-slate-300">
                        Kata Sandi
                    </label>
                    <a href="<?= url('/forgot-password') ?>" class="text-xs text-[#6F7CF5] hover:text-indigo-300 font-medium transition">
                        Lupa kata sandi?
                    </a>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        name="password"
                        x-model="passwordInput"
                        placeholder="Masukkan kata sandi"
                        required
                        autocomplete="current-password"
                        class="w-full pl-11 pr-11 py-3 bg-[#0B0E17] border border-[#232B3E] rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#4B4EF0] focus:border-transparent transition"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition"
                        aria-label="Lihat kata sandi"
                    >
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center space-x-2.5 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        name="remember"
                        id="remember"
                        class="w-4 h-4 rounded border-slate-700 bg-[#0B0E17] text-[#4B4EF0] focus:ring-[#4B4EF0] focus:ring-offset-0"
                        checked
                    >
                    <span class="text-xs text-slate-300">Ingat Saya di perangkat ini</span>
                </label>
            </div>

            <!-- Submit Button (Min 48px Touch Target) -->
            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full min-h-[48px] px-4 py-3 bg-[#4B4EF0] hover:bg-[#3A3BE0] active:scale-[0.99] text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center space-x-2"
                >
                    <span>Masuk ke Akun</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </form>

        <!-- Quick Demo Button -->
        <div class="mt-4 pt-4 border-t border-[#232B3E]/60 text-center">
            <button
                type="button"
                @click="fillDemo()"
                class="w-full py-2.5 px-3 bg-[#0B0E17] hover:bg-slate-900 border border-indigo-500/40 text-indigo-300 text-xs font-semibold rounded-xl transition flex items-center justify-center space-x-2"
            >
                <svg class="w-4 h-4 text-[#6F7CF5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>Gunakan Akun Demo (Warung Bu Sari)</span>
            </button>
        </div>
    </div>

    <!-- Footer / Security note -->
    <div class="py-4 text-center">
        <p class="text-[11px] text-slate-500 flex items-center justify-center space-x-1.5">
            <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Aman & Terlindungi untuk Pemilik Warung</span>
        </p>
    </div>
</div>

