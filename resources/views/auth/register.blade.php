<x-guest-layout role="siswa">
    <div class="space-y-8">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Daftar Siswa</p>
            <h1 class="text-3xl font-semibold text-slate-950 sm:text-4xl">Mulai akses layanan BK</h1>
            <p class="text-sm leading-6 text-slate-600">Akun siswa bisa langsung digunakan untuk pengajuan konseling.</p>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm leading-6 text-blue-700">
            Daftar siswa harus cocok dengan data NISN dan tanggal lahir yang sudah dibuat admin sekolah.
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="role" value="siswa">

            <div class="space-y-2">
                <label for="name" class="block text-sm font-semibold text-slate-900">Nama Lengkap</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Nama lengkap" />
                <x-input-error :messages="$errors->get('name')" class="mt-1 text-sm text-red-600" />
            </div>

            <div class="space-y-2">
                <label for="email" class="block text-sm font-semibold text-slate-900">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="email@sekolah.id" />
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-600" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
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
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-semibold text-slate-900">Password</label>
                    <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-600" />
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-900">Konfirmasi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Ulangi" />
                </div>
            </div>

            <button type="submit" class="w-full rounded-full bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Daftar sebagai siswa</button>

            <div class="space-y-2 text-center text-sm text-slate-600">
                <p>Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-500">Login</a></p>
            </div>
        </form>
    </div>
</x-guest-layout>
