@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    sisaDetik: {{ $tryout->durasi_menit * 60 }},
    init() {
        const tick = () => {
            if (this.sisaDetik > 0) this.sisaDetik--;
        };
        setInterval(tick, 1000);
    },
    formatWaktu() {
        const m = Math.floor(this.sisaDetik / 60);
        const s = this.sisaDetik % 60;
        return m + ' menit ' + s + ' detik';
    }
}">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title :title="$tryout->judul" :description="$tryout->deskripsi" />
        <p class="mt-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 px-4 py-3 text-sm font-semibold text-amber-800 dark:text-amber-300">
            Sisa waktu (panduan): <span x-text="formatWaktu()"></span>
        </p>
    </section>

    <form method="POST" action="{{ route('siswa.tryout.store', $tryout) }}" class="space-y-4">
        @csrf
        @foreach($soal as $index => $item)
            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Soal {{ $index + 1 }}</p>
                <p class="mt-2 font-medium text-slate-900 dark:text-slate-100">{{ $item->teks_pertanyaan }}</p>
                @if($item->tipe_input === \App\Models\MasterQuestion::TIPE_SKALA)
                    <x-form-select name="jawaban[{{ $item->id }}]" class="mt-4" required>
                        <option value="">Pilih skor 1–5</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </x-form-select>
                @else
                    <x-form-textarea name="jawaban[{{ $item->id }}]" rows="3" class="mt-4" required placeholder="Tulis jawabanmu"></x-form-textarea>
                @endif
            </section>
        @endforeach

        <x-primary-button class="w-full justify-center">Kumpulkan jawaban</x-primary-button>
    </form>
</div>
@endsection
