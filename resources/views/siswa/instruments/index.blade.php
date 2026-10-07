@extends('layouts.app')

@section('content')
@php
    $isMinat = $category === \App\Models\InstrumentQuestion::CATEGORY_MINAT_BAKAT;
    $oldAnswers = collect(old('answers', []));
@endphp
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Instrumen Asesmen" description="Isi instrumen minat bakat, gaya belajar, kepribadian, sosiometri, dan masalah sesuai kondisi diri." />
        <x-alert class="mt-5" type="success" :message="session('success')" />
        @error('jenjang')
            <x-alert class="mt-5" type="error" :message="$message" />
        @enderror

        <div class="mt-6 flex flex-wrap gap-2">
            @foreach($categories as $value => $label)
                <a href="{{ route('siswa.instruments.index', ['category' => $value]) }}" class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $category === $value ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-3">
            @foreach($categories as $value => $label)
                @php
                    $submission = $latestSubmissions[$value] ?? null;
                @endphp
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                    <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ $label }}</p>
                    @if($submission && $value === \App\Models\InstrumentQuestion::CATEGORY_MINAT_BAKAT && $submission->kode_minat)
                        <p class="mt-1 text-xs font-semibold text-blue-700 dark:text-blue-300">
                            Kode Minat: {{ $submission->kode_minat }}
                        </p>
                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            {{ $submission->result_label }} · {{ $submission->submitted_at?->format('d M Y') }}
                        </p>
                        <a href="{{ route('siswa.instruments.hasil', $submission) }}" class="mt-3 inline-flex text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300">
                            Lihat hasil
                        </a>
                    @else
                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            {{ $submission ? $submission->result_label.' - '.$submission->submitted_at?->format('d M Y') : 'Belum diisi' }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        @if($isMinat && ! empty($jenjangBlocked))
            <x-empty-state title="Asesmen tidak tersedia" description="Asesmen ini untuk siswa SMA/SMK." />
        @elseif($isMinat && ! empty($needsJenjangChooser) && ! $jenjang && ! request()->boolean('start'))
            <div class="mx-auto max-w-xl space-y-5 text-center">
                <x-section-title title="Asesmen Minat Bakat" description="Kenali kecenderungan minatmu (model RIASEC) lalu dapatkan rekomendasi yang sesuai jenjang." />
                <div class="grid gap-3 text-left sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4 text-sm text-slate-600 dark:text-slate-300">
                        <p class="font-semibold text-slate-900 dark:text-white">SMA</p>
                        <p class="mt-1">Hasil menampilkan rekomendasi Program Studi PCR.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4 text-sm text-slate-600 dark:text-slate-300">
                        <p class="font-semibold text-slate-900 dark:text-white">SMK</p>
                        <p class="mt-1">Hasil menampilkan rekomendasi Bidang Karier + Job Zone.</p>
                    </div>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Jenjang kelas belum terisi. Pilih SMA atau SMK untuk memulai.</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('siswa.instruments.index', ['category' => $category, 'jenjang' => 'SMA', 'start' => 1]) }}" class="rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/20">
                        Mulai sebagai SMA
                    </a>
                    <a href="{{ route('siswa.instruments.index', ['category' => $category, 'jenjang' => 'SMK', 'start' => 1]) }}" class="rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                        Mulai sebagai SMK
                    </a>
                </div>
            </div>
        @elseif($questions->isEmpty())
            <x-empty-state title="Soal belum tersedia" description="Guru BK belum mengaktifkan soal untuk kategori ini." />
        @elseif($isMinat)
            @include('siswa.instruments.partials.minat-wizard', [
                'questions' => $questions,
                'oldAnswers' => $oldAnswers,
                'category' => $category,
                'jenjang' => $jenjang,
                'needsJenjangChooser' => $needsJenjangChooser ?? false,
            ])
        @else
            @include('siswa.instruments.partials.classic-form', [
                'questions' => $questions,
                'category' => $category,
            ])
        @endif
    </section>
</div>
@endsection
