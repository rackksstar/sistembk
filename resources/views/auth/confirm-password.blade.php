<x-guest-layout>
    <div class="space-y-8">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Konfirmasi Password</p>
            <h1 class="text-3xl font-semibold text-slate-950 sm:text-4xl">Akses area aman</h1>
            <p class="text-sm leading-6 text-slate-600">Masukkan password Anda untuk melanjutkan.</p>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 text-sm leading-6 text-blue-700">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf
            <div class="space-y-2">
                <label for="password" class="block text-sm font-semibold text-slate-900">Password</label>
                <x-text-input id="password" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-600" />
            </div>
            <button type="submit" class="w-full rounded-full bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Konfirmasi</button>
        </form>
    </div>
</x-guest-layout>
