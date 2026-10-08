@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    skor_materi: {{ old('skor_materi', 0) }},
    skor_cara: {{ old('skor_cara', 0) }},
    skor_manfaat: {{ old('skor_manfaat', 0) }},
}">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title
            title="Form Penilaian Layanan"
            description="Berikan skor 1–5 untuk tiga aspek layanan konseling berikut."
        />

        <div class="mt-4 rounded-2xl border border-blue-100 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/30 p-4 text-sm text-slate-700 dark:text-slate-300">
            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $konseling->subject }}</p>
            <p class="mt-1 text-slate-600 dark:text-slate-400">
                @if($konseling->scheduled_at)
                    {{ $konseling->scheduled_at->format('d M Y H:i') }}
                @elseif($konseling->consultation_date)
                    {{ $konseling->consultation_date->format('d M Y') }}
                @endif
            </p>
        </div>

        <form action="{{ route('siswa.penilaian.store') }}" method="POST" class="mt-6 space-y-8">
            @csrf
            <input type="hidden" name="consultation_request_id" value="{{ $konseling->id }}">

            @php
                $aspects = [
                    ['key' => 'skor_materi', 'label' => 'Materi / Konten', 'hint' => 'Kejelasan dan relevansi materi yang dibahas'],
                    ['key' => 'skor_cara', 'label' => 'Cara Penyampaian', 'hint' => 'Sikap, empati, dan komunikasi Guru BK'],
                    ['key' => 'skor_manfaat', 'label' => 'Manfaat yang Dirasakan', 'hint' => 'Seberapa membantu sesi ini bagi Anda'],
                ];
            @endphp

            @foreach($aspects as $aspect)
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $aspect['label'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $aspect['hint'] }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @for($star = 1; $star <= 5; $star++)
                            <label class="cursor-pointer">
                                <input
                                    type="radio"
                                    name="{{ $aspect['key'] }}"
                                    value="{{ $star }}"
                                    class="sr-only"
                                    x-model="{{ $aspect['key'] }}"
                                    @checked(old($aspect['key']) == $star)
                                    required
                                >
                                <span
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border text-lg transition"
                                    :class="{{ $aspect['key'] }} >= {{ $star }}
                                        ? 'border-amber-300 bg-amber-50 dark:bg-amber-950/40 text-amber-500'
                                        : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-300 hover:border-amber-200 dark:hover:border-amber-800'"
                                    aria-hidden="true"
                                >★</span>
                            </label>
                        @endfor
                        <span class="ml-2 text-sm font-semibold text-slate-600 dark:text-slate-400" x-text="{{ $aspect['key'] }} ? {{ $aspect['key'] }} + '/5' : 'Pilih skor'"></span>
                    </div>
                    <x-input-error :messages="$errors->get($aspect['key'])" />
                </div>
            @endforeach

            <div class="space-y-2">
                <x-input-label for="catatan" value="Catatan (opsional)" />
                <textarea
                    id="catatan"
                    name="catatan"
                    rows="4"
                    maxlength="1000"
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50"
                    placeholder="Masukan tambahan untuk Guru BK (maks. 1000 karakter)"
                >{{ old('catatan') }}</textarea>
                <x-input-error :messages="$errors->get('catatan')" />
            </div>

            <div class="flex flex-wrap gap-3">
                <x-primary-button>Kirim penilaian</x-primary-button>
                <a
                    href="{{ route('siswa.penilaian.index') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60"
                >
                    Kembali
                </a>
            </div>
        </form>
    </section>
</div>
@endsection
