<x-guest-layout :role="$selectedRole ?? 'login'">
    @php
        $selectedRole ??= null;
        $roleLabels = [
            'admin' => 'Admin',
            'guru' => 'Guru BK',
            'siswa' => 'Siswa',
        ];
        $roleLabel = $roleLabels[$selectedRole] ?? null;
    @endphp

    <div class="space-y-8">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Masuk Akun</p>
            <h1 class="text-3xl font-semibold text-slate-950 sm:text-4xl">
                {{ $roleLabel ? 'Login '.$roleLabel : 'Selamat datang kembali' }}
            </h1>
            <p class="text-sm leading-6 text-slate-600">
                @if($selectedRole === 'guru')
                    Jika sudah memiliki akun Guru BK, masukkan email dan password untuk melanjutkan ke dashboard.
                @else
                    {{ $roleLabel ? 'Masukkan akun '.$roleLabel.' untuk melanjutkan ke dashboard.' : 'Pilih role dari landing page, lalu login ulang untuk masuk ke dashboard.' }}
                @endif
            </p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            @if($selectedRole)
                <input type="hidden" name="selected_role" value="{{ $selectedRole }}">
            @endif

            @if($selectedRole === 'siswa')
                <div class="space-y-2">
                    <label for="nisn" class="block text-sm font-semibold text-slate-900">NISN</label>
                    <input id="nisn" name="nisn" type="text" value="{{ old('nisn') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="NISN siswa" />
                    <x-input-error :messages="$errors->get('nisn')" class="mt-1 text-sm text-red-600" />
                </div>

                <div class="space-y-2">
                    <label for="birth_date" class="block text-sm font-semibold text-slate-900">Tanggal Lahir</label>
                    <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
                    <x-input-error :messages="$errors->get('birth_date')" class="mt-1 text-sm text-red-600" />
                </div>
            @else
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
            @endif

            <button type="submit" class="w-full rounded-full bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Login</button>

            @if($selectedRole === 'guru')
                <div class="text-center text-sm text-slate-600">
                    <p>Belum punya akun Guru BK? <a href="{{ route('guru.register') }}" class="font-semibold text-blue-600 hover:text-blue-500">Ajukan akun Guru BK</a></p>
                </div>
            @elseif(! in_array($selectedRole, ['admin', 'siswa'], true))
                <div class="space-y-2 text-center text-sm text-slate-600">
                    <p>Siswa dapat masuk lewat <a href="{{ route('login', ['role' => 'siswa']) }}" class="font-semibold text-blue-600 hover:text-blue-500">NISN dan tanggal lahir</a></p>
                    <p>Guru BK baru? <a href="{{ route('guru.register') }}" class="font-semibold text-blue-600 hover:text-blue-500">Ajukan akun Guru BK</a></p>
                </div>
            @endif
        </form>
    </div>
</x-guest-layout>
