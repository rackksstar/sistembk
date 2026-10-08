@extends('layouts.app')

@section('content')
@php
    $displayCategories = [
        \App\Models\InstrumentQuestion::CATEGORY_GAYA_BELAJAR => 'Strategi Belajar',
        \App\Models\InstrumentQuestion::CATEGORY_KEPRIBADIAN => 'Kepribadian',
        \App\Models\InstrumentQuestion::CATEGORY_ANGKET_MASALAH => 'Masalah',
    ];
@endphp
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <x-section-title
            title="Instrumen Asesmen"
            description="Isi instrumen minat bakat, strategi belajar, dan kepribadian sesuai kondisi diri."
        />
        <x-alert class="mt-5" type="success" :message="session('success')" />

        <div class="mt-6 flex flex-wrap gap-2">
            <a
                href="{{ route('siswa.minat-bakat.index') }}"
                class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60"
            >
                Minat Bakat
            </a>
            @foreach($displayCategories as $value => $label)
                <a
                    href="{{ route('siswa.instruments.index', ['category' => $value]) }}"
                    class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $category === $value ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                <p class="text-sm font-semibold text-slate-950 dark:text-white">Minat Bakat</p>
                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                    @if(($latestMinatSubmission ?? null))
                        {{ $latestMinatSubmission->kode_minat ?: $latestMinatSubmission->result_label }}
                        - {{ $latestMinatSubmission->submitted_at?->format('d M Y') }}
                    @else
                        Belum diisi
                    @endif
                </p>
                @if(($latestMinatSubmission ?? null))
                    <a href="{{ route('siswa.instruments.hasil', $latestMinatSubmission) }}" class="mt-2 inline-flex text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200">
                        Lihat rincian hasil →
                    </a>
                @endif
            </div>

            @foreach($displayCategories as $value => $label)
                @php($submission = $latestSubmissions[$value] ?? null)
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                    <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ $label }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                        @if($submission)
                            {{ $submission->result_label }} - {{ $submission->submitted_at?->format('d M Y') }}
                        @else
                            Belum diisi
                        @endif
                    </p>
                    @if($submission)
                        <a href="{{ route('siswa.instruments.hasil', $submission) }}" class="mt-2 inline-flex text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200">
                            Lihat rincian hasil →
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:p-8">
        @if($questions->isEmpty())
            <x-empty-state title="Soal belum tersedia" description="Guru BK belum mengaktifkan soal untuk kategori ini." />
        @else
            @include('siswa.instruments.partials.classic-form', [
                'questions' => $questions,
                'category' => $category,
                'sections' => $sections ?? collect(),
            ])
        @endif
    </section>
</div>
@endsection
