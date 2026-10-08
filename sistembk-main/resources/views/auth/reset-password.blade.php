<x-guest-layout>
    <div class="space-y-8">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Reset Password</p>
            <h1 class="text-3xl font-semibold text-slate-950 dark:text-white sm:text-4xl">Buat password baru</h1>
            <p class="text-sm leading-6 text-slate-600 dark:text-slate-400">Gunakan password yang aman dan mudah Anda ingat.</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="space-y-2">
                <label for="email" class="block text-sm font-semibold text-slate-900 dark:text-slate-100">Email</label>
                <x-text-input id="email" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-600" />
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-sm font-semibold text-slate-900 dark:text-slate-100">Password</label>
                <x-text-input id="password" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-600" />
            </div>

            <div class="space-y-2">
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-900 dark:text-slate-100">Konfirmasi Password</label>
                <x-text-input id="password_confirmation" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-sm text-red-600" />
            </div>

            <button type="submit" class="w-full rounded-full bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Reset Password</button>
        </form>
    </div>
</x-guest-layout>
