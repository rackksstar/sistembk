@extends('layouts.app')

@php
    $levelBadgeClasses = [
        'kurang' => 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300',
        'cukup' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300',
        'baik' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300',
    ];
    $codeDotClasses = [
        'R' => 'bg-orange-500', 'I' => 'bg-blue-500', 'A' => 'bg-purple-500',
        'S' => 'bg-emerald-500', 'E' => 'bg-amber-500', 'C' => 'bg-cyan-500',
    ];
@endphp

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Hasil Skoring Instrumen" description="Pantau hasil skor otomatis dari jawaban siswa." />
            <a href="{{ route('guru.instrument-results.talent-matrix') }}" class="inline-flex w-fit items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">
                Lihat Matriks Talents Mapping
            </a>
        </div>
        <form method="GET" action="{{ route('guru.instrument-results.index') }}" class="mt-6 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
            <x-form-select name="category">
                <option value="">Semua kategori</option>
                @foreach($categories as $value => $label)
                    <option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>
                @endforeach
            </x-form-select>
            <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Filter</button>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse($submissions as $submission)
            <article class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">{{ $submission->categoryLabel() }}</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-950 dark:text-white">{{ $submission->student?->name }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $submission->submitted_at?->format('d M Y H:i') }}</p>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                            Kelas: {{ $submission->student?->classModel?->name ?? '-' }}<br>
                            Sekolah: {{ $submission->student?->schoolModel?->name ?? $submission->student?->school ?? '-' }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-blue-50 dark:bg-blue-950/40 px-4 py-3 text-right">
                        <p class="text-xs font-semibold text-blue-700 dark:text-blue-300">Skor</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $submission->total_score }}</p>
                    </div>
                </div>
                @if($submission->category === \App\Models\InstrumentQuestion::CATEGORY_STRATEGI_BELAJAR)
                    <div class="mt-4 space-y-2">
                        @foreach($submission->strategiBelajarSectionResults() as $result)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $result['label'] }}</span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $levelBadgeClasses[$result['level']] }}">
                                    {{ $result['level_label'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @elseif($submission->category === \App\Models\InstrumentQuestion::CATEGORY_MINAT_BAKAT)
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @foreach($submission->riasecScores() as $result)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                <span class="h-2 w-2 rounded-full {{ $codeDotClasses[$result['code']] ?? 'bg-slate-400' }}"></span>
                                {{ $result['code'] }} {{ $result['percentage'] }}%
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $submission->result_label }}</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $submission->result_description }}</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="xl:col-span-2">
                <x-empty-state title="Belum ada hasil" description="Hasil akan muncul setelah siswa mengisi instrumen pendukung." />
            </div>
        @endforelse
    </section>

    {{ $submissions->links() }}
</div>
@endsection
