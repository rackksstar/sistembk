@extends('layouts.app')

@section('content')
@php
    $hasStrategiSections = $category === \App\Models\InstrumentQuestion::CATEGORY_GAYA_BELAJAR
        && $questions->contains(fn ($q) => $q->section !== null);
    $activeLabel = $categories[$category] ?? 'Instrumen';
    $categoryHints = [
        \App\Models\InstrumentQuestion::CATEGORY_MINAT_KERJA => 'Eksplorasi minat dan kesiapan menuju dunia kerja.',
        \App\Models\InstrumentQuestion::CATEGORY_GAYA_BELAJAR => 'Tiga bagian: Perencanaan, Eksekusi, dan Refleksi belajar.',
        \App\Models\InstrumentQuestion::CATEGORY_KEPRIBADIAN => 'Gambaran singkat pola diri dalam situasi sehari-hari.',
        \App\Models\InstrumentQuestion::CATEGORY_ANGKET_MASALAH => 'Pemetaan area yang mungkin perlu didukung Guru BK.',
    ];
@endphp
<div class="space-y-6">
    <section class="overflow-hidden rounded-3xl border border-slate-200 shadow-sm dark:border-slate-700">
        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-5 text-center sm:text-left">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-100">Modul Yola · Siap Kerja</p>
                    <h1 class="mt-1 text-lg font-bold text-white sm:text-xl">Instrumen Asesmen</h1>
                    <p class="mt-1 text-sm text-emerald-50/90">
                        Asesmen kesiapan kerja dan pemahaman diri (minat kerja, strategi belajar, kepribadian, masalah).
                    </p>
                </div>
                <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-white backdrop-blur-sm">
                    Yola
                </span>
            </div>
        </div>

        <div class="bg-white p-6 dark:bg-slate-900">
            <x-alert type="success" :message="session('success')" />

            <div class="mt-1 rounded-2xl border border-blue-100 bg-blue-50/70 p-4 text-sm leading-6 text-slate-700 dark:border-blue-900/40 dark:bg-blue-950/20 dark:text-slate-300">
                <p class="font-semibold text-slate-950 dark:text-white">Mau lanjut kuliah?</p>
                <p class="mt-1">Pakai asesmen Key (Talents Mapping / RIASEC) untuk Kode Minat Holland dan rekomendasi prodi PCR.</p>
                <a href="{{ route('siswa.minat-bakat.index') }}" class="mt-2 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300">
                    Buka Minat Bakat Kuliah →
                </a>
            </div>

            <div class="mt-6 flex flex-wrap gap-2">
                @foreach($categories as $value => $label)
                    <a
                        href="{{ route('siswa.instruments.index', ['category' => $value]) }}"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $category === $value ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach($categories as $value => $label)
                    @php($submission = $latestSubmissions[$value] ?? null)
                    <div @class([
                        'rounded-2xl border p-4 transition',
                        'border-emerald-200 bg-emerald-50/70 dark:border-emerald-900/50 dark:bg-emerald-950/20' => $category === $value,
                        'border-slate-100 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/60' => $category !== $value,
                    ])>
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ $label }}</p>
                            @if($submission)
                                <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">Selesai</span>
                            @else
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-600 dark:bg-slate-700 dark:text-slate-300">Baru</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            @if($submission)
                                {{ $submission->result_label }}
                                @if($submission->percentage !== null)
                                    ({{ number_format((float) $submission->percentage, 2) }}%)
                                @endif
                                · {{ $submission->submitted_at?->format('d M Y') }}
                            @else
                                Belum diisi
                            @endif
                        </p>
                        @if($submission)
                            @if($value === \App\Models\InstrumentQuestion::CATEGORY_GAYA_BELAJAR)
                                <a href="{{ route('siswa.instruments.strategi-belajar-result') }}" class="mt-2 inline-flex text-xs font-semibold text-emerald-700 hover:text-emerald-800 dark:text-emerald-300">
                                    Lihat rincian hasil →
                                </a>
                            @else
                                <a href="{{ route('siswa.instruments.hasil', $submission) }}" class="mt-2 inline-flex text-xs font-semibold text-emerald-700 hover:text-emerald-800 dark:text-emerald-300">
                                    Lihat rincian hasil →
                                </a>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-600 dark:text-emerald-400">Sedang diisi</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950 dark:text-white">{{ $activeLabel }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $categoryHints[$category] ?? 'Jawab setiap soal sesuai kondisimu saat ini.' }}
                </p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                {{ $questions->count() }} soal
            </span>
        </div>

        @if($questions->isEmpty())
            <x-empty-state title="Soal belum tersedia" description="Guru BK belum mengaktifkan soal untuk kategori ini." />
        @elseif($hasStrategiSections)
            @include('siswa.instruments.partials.strategi-belajar-form', [
                'questions' => $questions,
                'category' => $category,
            ])
        @else
            @include('siswa.instruments.partials.yola-form', [
                'questions' => $questions,
                'category' => $category,
            ])
        @endif
    </section>
</div>
@endsection
