<x-guest-layout>
    <div class="space-y-8">
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

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-full border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Keluar</button>
            </form>
        </div>
    </div>
</x-guest-layout>
