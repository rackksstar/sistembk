<x-guest-layout>
    <div class="space-y-8" x-data="{ logoutOpen: false }">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Verifikasi Email</p>
            <h1 class="text-3xl font-semibold text-slate-950 dark:text-white sm:text-4xl">Cek inbox Anda</h1>
            <p class="text-sm leading-6 text-slate-600 dark:text-slate-400">Klik tautan yang dikirim lewat email untuk menyelesaikan pendaftaran.</p>
        </div>

        <div class="rounded-2xl border border-blue-100 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/40 p-5 text-sm leading-6 text-blue-700 dark:text-blue-300">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 p-4 text-sm text-emerald-700 dark:text-emerald-300">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full rounded-full bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Kirim ulang email</button>
            </form>

            <button type="button" x-on:click="logoutOpen = true" class="w-full rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 transition hover:bg-slate-50 dark:hover:bg-slate-800/60">Keluar</button>
        </div>

        <div
            x-cloak
            x-show="logoutOpen"
            x-transition.opacity
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="verify-email-logout-confirmation-title"
        >
            <div x-on:click.outside="logoutOpen = false" class="w-full max-w-md rounded-2xl border border-white dark:border-slate-700 bg-white dark:bg-slate-900 p-6 text-center shadow-2xl shadow-slate-950/20">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-blue-600">Konfirmasi Logout</p>
                <h2 id="verify-email-logout-confirmation-title" class="mt-3 text-2xl font-bold text-slate-950 dark:text-white">Yakin ingin keluar?</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                    Jika keluar sekarang, Anda harus login ulang untuk melanjutkan verifikasi email.
                </p>

                <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <button type="button" x-on:click="logoutOpen = false" class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 transition hover:border-blue-200 dark:border-blue-800 hover:text-blue-700 dark:text-blue-300">
                        Batal
                    </button>
                    <form method="POST" action="{{ route('logout') }}" class="inline-flex justify-center">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">
                            Ya, logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
