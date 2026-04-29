<x-guest-layout role="guru">
    <div class="space-y-8">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Guru BK</p>
            <h1 class="text-3xl font-semibold text-slate-950 sm:text-4xl">Ajukan akun Guru BK</h1>
            <p class="text-sm leading-6 text-slate-600">Pendaftaran akan berstatus pending sampai disetujui Admin.</p>
        </div>

        <div class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm leading-6 text-sky-700">
            Admin akan meninjau data sekolah dan email sebelum akun Guru BK aktif.
        </div>

        <form method="POST" action="{{ route('guru.register.store') }}" class="space-y-5">
            @csrf

            <div class="space-y-2">
                <label for="name" class="block text-sm font-semibold text-slate-900">Nama</label>
                <input id="name" name="name" value="{{ old('name') }}" required autofocus class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Nama lengkap" />
                <x-input-error :messages="$errors->get('name')" class="text-sm text-red-600" />
            </div>

            <div class="space-y-2">
                <label for="email" class="block text-sm font-semibold text-slate-900">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="email@sekolah.id" />
                <x-input-error :messages="$errors->get('email')" class="text-sm text-red-600" />
            </div>

            <div class="space-y-2">
                <label for="school" class="block text-sm font-semibold text-slate-900">Sekolah</label>
                <input id="school" name="school" value="{{ old('school') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Nama sekolah" />
                <x-input-error :messages="$errors->get('school')" class="text-sm text-red-600" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-semibold text-slate-900">Password</label>
                    <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Password" />
                    <x-input-error :messages="$errors->get('password')" class="text-sm text-red-600" />
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-900">Konfirmasi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Ulangi" />
                </div>
            </div>

            <button class="w-full rounded-full bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Kirim pendaftaran</button>
            <p class="text-center text-sm text-slate-600">Sudah disetujui? <a href="{{ route('login', ['role' => 'guru']) }}" class="font-semibold text-blue-600 hover:text-blue-500">Login</a></p>
        </form>
    </div>
</x-guest-layout>
