@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="overflow-hidden rounded-3xl border border-slate-200 shadow-xs dark:border-slate-700">
        <div class="bg-linear-to-r from-indigo-500 to-blue-600 px-6 py-5 text-center sm:text-left">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-indigo-100">Modul Key · Lanjut Kuliah</p>
                    <h1 class="mt-1 text-lg font-bold text-white sm:text-xl">Minat Bakat Kuliah — Talents Mapping</h1>
                    <p class="mt-1 text-sm text-indigo-50/90">
                        Asesmen RIASEC (99 item) → Kode Minat Holland + rekomendasi prodi PCR (SMA) atau bidang karier (SMK).
                    </p>
                </div>
                <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-white backdrop-blur-xs">
                    Key
                </span>
            </div>
        </div>

        <div class="bg-white p-6 dark:bg-slate-900">
            <x-alert type="success" :message="session('success')" />
            @error('jenjang')
                <x-alert class="mt-3" type="error" :message="$message" />
            @enderror

            <div class="mt-1 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 text-sm leading-6 text-slate-700 dark:border-emerald-900/40 dark:bg-emerald-950/20 dark:text-slate-300">
                <p class="font-semibold text-slate-950 dark:text-white">Mau fokus jalur kerja dulu?</p>
                <p class="mt-1">Pakai asesmen Yola (Minat Bakat Kerja) untuk eksplorasi minat dan kesiapan diri menuju dunia kerja.</p>
                <a href="{{ route('siswa.instruments.index', ['category' => 'minat_kerja']) }}" class="mt-2 inline-flex text-sm font-semibold text-emerald-700 hover:text-emerald-800 dark:text-emerald-300">
                    Buka Minat Bakat Kerja →
                </a>
            </div>

            @if($latestSubmission)
                <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/70 p-4 dark:border-blue-900/40 dark:bg-blue-950/20">
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
                        Lihat hasil lengkap →
                    </a>
                </div>
            @endif

            <p class="mt-5 text-xs leading-5 text-slate-500 dark:text-slate-400">
                Modul Yola lain (strategi belajar, kepribadian, masalah):
                <a href="{{ route('siswa.instruments.index', ['category' => 'gaya_belajar']) }}" class="font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-300">buka Instrumen Asesmen</a>.
            </p>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
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
                'track' => 'kuliah',
            ])
        @endif
    </section>
</div>
@endsection
