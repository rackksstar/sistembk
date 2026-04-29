<x-guest-layout role="login">
    <div class="space-y-8">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Masuk Akun</p>
            <h1 class="text-3xl font-semibold text-slate-950 sm:text-4xl">Selamat datang kembali</h1>
            <p class="text-sm leading-6 text-slate-600">Masuk untuk Siswa dan Guru BK. Admin tetap dapat login dari akses kecil di pojok kanan atas.</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-sky-100 bg-sky-50 px-3 py-3 text-center">
                <p class="text-xs font-bold text-sky-700">Guru BK</p>
                <p class="mt-1 text-[11px] leading-4 text-sky-600">Konseling</p>
            </div>
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-3 py-3 text-center">
                <p class="text-xs font-bold text-indigo-700">Siswa</p>
                <p class="mt-1 text-[11px] leading-4 text-indigo-600">Pengajuan</p>
            </div>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div class="space-y-2">
                <label for="email" class="block text-sm font-semibold text-slate-900">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="email@sekolah.id" />
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-600" />
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-sm font-semibold text-slate-900">Password</label>
                <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Masukkan password Anda" />
                <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-600" />
            </div>

            <div class="flex items-center justify-between text-sm text-slate-600">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                    <span>Ingat saya</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="font-semibold text-blue-600 hover:text-blue-500">Lupa password?</a>
                @endif
            </div>

            <button type="submit" class="w-full rounded-full bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Login</button>

            <div class="space-y-2 text-center text-sm text-slate-600">
                <p>Belum punya akun siswa? <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-500">Daftar siswa</a></p>
                <p>Guru BK baru? <a href="{{ route('guru.register') }}" class="font-semibold text-blue-600 hover:text-blue-500">Ajukan akun Guru BK</a></p>
            </div>
        </form>
    </div>
</x-guest-layout>
