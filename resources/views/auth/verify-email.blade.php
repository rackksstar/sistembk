<x-guest-layout>
    <div class="space-y-8" x-data="{ logoutOpen: false }">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Verifikasi Email</p>
            <h1 class="text-3xl font-semibold text-slate-950 sm:text-4xl">Cek inbox Anda</h1>
            <p class="text-sm leading-6 text-slate-600">Klik tautan yang dikirim lewat email untuk menyelesaikan pendaftaran.</p>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 text-sm leading-6 text-blue-700">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full rounded-full bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Kirim ulang email</button>
            </form>

            <button type="button" x-on:click="logoutOpen = true" class="w-full rounded-full border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Keluar</button>
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
            <div x-on:click.outside="logoutOpen = false" class="w-full max-w-md rounded-2xl border border-white bg-white p-6 text-center shadow-2xl shadow-slate-950/20">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-blue-600">Konfirmasi Logout</p>
                <h2 id="verify-email-logout-confirmation-title" class="mt-3 text-2xl font-bold text-slate-950">Yakin ingin keluar?</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Jika keluar sekarang, Anda harus login ulang untuk melanjutkan verifikasi email.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <button type="button" x-on:click="logoutOpen = false" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                        Batal
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">
                            Ya, logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
