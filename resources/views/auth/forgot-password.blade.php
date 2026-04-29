<x-guest-layout>
    <div class="space-y-8">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Reset Password</p>
            <h1 class="text-3xl font-semibold text-slate-950 sm:text-4xl">Lupa password?</h1>
            <p class="text-sm leading-6 text-slate-600">Masukkan email Anda dan kami akan mengirimkan instruksi reset password.</p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div class="space-y-2">
                <label for="email" class="block text-sm font-semibold text-slate-900">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="email@sekolah.id" />
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-600" />
            </div>
            <button type="submit" class="w-full rounded-full bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Kirim tautan reset</button>
            <p class="text-center text-sm text-slate-600">Ingat password? <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-500">Login</a></p>
        </form>
    </div>
</x-guest-layout>
