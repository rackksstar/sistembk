@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    sisaDetik: {{ $sisaDetik }},
    sudahKirim: false,
    init() {
        const tick = () => {
            if (this.sisaDetik > 0) this.sisaDetik--;
            if (this.sisaDetik <= 0 && !this.sudahKirim) {
                this.sudahKirim = true;
                const form = document.getElementById('tryout-form');
                if (form) form.submit();
            }
        };
        setInterval(tick, 1000);
    },
    formatWaktu() {
        const m = Math.floor(this.sisaDetik / 60);
        const s = this.sisaDetik % 60;
        return m + ' menit ' + s + ' detik';
    }
}">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <x-section-title :title="$tryout->judul" :description="$tryout->deskripsi" />
        <p class="mt-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 px-4 py-3 text-sm font-semibold text-amber-800 dark:text-amber-300">
            Sisa waktu: <span x-text="formatWaktu()"></span>
            <span x-show="sisaDetik <= 0" class="text-red-600 dark:text-red-400">— Waktu habis, jawaban akan dikumpulkan otomatis.</span>
        </p>
    </section>

    <form id="tryout-form" method="POST" action="{{ route('siswa.tryout.store', $tryout) }}" class="space-y-4">
        @csrf
        @foreach($soal as $index => $item)
            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Soal {{ $index + 1 }}</p>
                <p class="mt-2 font-medium text-slate-900 dark:text-slate-100">{{ $item->teks_pertanyaan }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $item->tipeLabel() }}</p>
                <x-master-question-field :question="$item" />
            </section>
        @endforeach

        <x-primary-button class="w-full justify-center" x-bind:disabled="sisaDetik <= 0">Kumpulkan jawaban</x-primary-button>
    </form>
</div>
@endsection
