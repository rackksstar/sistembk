@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <x-section-title
                title="Minat Bakat RIASEC (Key)"
                description="Asesmen minat untuk siswa SMA/SMK yang merencanakan lanjut kuliah (rekomendasi prodi PCR) atau pemetaan bidang karier."
            />
            <span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                Modul Key
            </span>
        </div>
        <x-alert class="mt-5" type="success" :message="session('success')" />
        @error('jenjang')
            <x-alert class="mt-5" type="error" :message="$message" />
        @enderror

        @if($latestSubmission)
            <div class="mt-5 rounded-2xl border border-blue-100 dark:border-blue-900/40 bg-blue-50/70 dark:bg-blue-950/20 p-4">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-300">Hasil terakhir</p>
                <p class="mt-2 text-lg font-bold text-slate-950 dark:text-white">
                    Kode Minat: {{ $latestSubmission->kode_minat ?: '-' }}
                </p>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    {{ $latestSubmission->result_label }}
                    @if($latestSubmission->jenjang) · {{ $latestSubmission->jenjang }} @endif
                    · {{ $latestSubmission->submitted_at?->format('d M Y H:i') }}
                </p>
                <a href="{{ route('siswa.instruments.hasil', $latestSubmission) }}" class="mt-3 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300">
                    Lihat hasil lengkap
                </a>
            </div>
        @endif

        <p class="mt-5 text-xs leading-5 text-slate-500 dark:text-slate-400">
            Untuk strategi belajar / kepribadian (Modul Yola),
            <a href="{{ route('siswa.instruments.index') }}" class="font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-300">buka Asesmen Diri</a>.
        </p>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        @if(! empty($jenjangBlocked))
            <x-empty-state title="Asesmen tidak tersedia" description="Asesmen Minat Bakat Key untuk siswa SMA/SMK." />
        @elseif(! empty($needsJenjangChooser) && ! $jenjang && ! request()->boolean('start'))
            <div class="mx-auto max-w-xl space-y-5 text-center">
                <x-section-title title="Mulai Asesmen Minat Bakat" description="Pilih jenjang agar rekomendasi sesuai (prodi PCR untuk SMA, bidang karier untuk SMK)." />
                <div class="grid gap-3 text-left sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4 text-sm text-slate-600 dark:text-slate-300">
                        <p class="font-semibold text-slate-900 dark:text-white">SMA</p>
                        <p class="mt-1">Rekomendasi Program Studi PCR.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4 text-sm text-slate-600 dark:text-slate-300">
                        <p class="font-semibold text-slate-900 dark:text-white">SMK</p>
                        <p class="mt-1">Rekomendasi Bidang Karier + Job Zone.</p>
                    </div>
                </div>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('siswa.minat-bakat.index', ['jenjang' => 'SMA', 'start' => 1]) }}" class="rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/20">
                        Mulai sebagai SMA
                    </a>
                    <a href="{{ route('siswa.minat-bakat.index', ['jenjang' => 'SMK', 'start' => 1]) }}" class="rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                        Mulai sebagai SMK
                    </a>
                </div>
            </div>
        @elseif($questions->isEmpty())
            <x-empty-state title="Soal belum tersedia" description="Guru BK belum mengaktifkan soal Minat Bakat RIASEC untuk jenjang ini." />
        @else
            @include('siswa.instruments.partials.minat-wizard', [
                'questions' => $questions,
                'oldAnswers' => $oldAnswers,
                'category' => $category,
                'jenjang' => $jenjang,
                'needsJenjangChooser' => $needsJenjangChooser ?? false,
            ])
        @endif
    </section>
</div>
@endsection
