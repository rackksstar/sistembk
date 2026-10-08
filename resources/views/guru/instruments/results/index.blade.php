@extends('layouts.app')

@php
    $levelBadgeClasses = [
        'kurang' => 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300',
        'cukup' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
        'baik' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
    ];
@endphp

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <x-section-title
                :title="$module === 'key' ? 'Hasil Minat Bakat Kuliah (Key)' : 'Hasil Instrumen Siap Kerja (Yola)'"
                :description="$module === 'key'
                    ? 'Pantau Kode Minat Holland dan skor RIASEC untuk rekomendasi lanjut kuliah.'
                    : 'Pantau hasil Minat Bakat Kerja, strategi belajar, kepribadian, dan masalah.'"
            />
            <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] {{ $module === 'key' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                {{ $module === 'key' ? 'Key · Kuliah' : 'Yola · Kerja' }}
            </span>
        </div>
        <form method="GET" action="{{ route('guru.instrument-results.index') }}" class="mt-6 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
            <input type="hidden" name="module" value="{{ $module }}">
            <x-form-select name="category">
                <option value="">Semua kategori</option>
                @foreach($categories as $value => $label)
                    <option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>
                @endforeach
            </x-form-select>
            <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Filter</button>
        </form>
        @if($module === 'key')
            <div class="mt-4">
                <a href="{{ route('guru.instrument-results.talent-matrix') }}" class="inline-flex text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300">
                    Lihat Matriks Talents Mapping →
                </a>
            </div>
        @endif
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse($submissions as $submission)
            <article class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-xs">
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
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $submission->total_score }}</p>
                    </div>
                </div>
                @php
                    $strategiResults = $submission->category === \App\Models\InstrumentQuestion::CATEGORY_GAYA_BELAJAR
                        ? $submission->strategiBelajarSectionResults()
                        : [];
                @endphp
                @if($strategiResults !== [])
                    <div class="mt-4 space-y-2">
                        @foreach($strategiResults as $result)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-2.5 dark:border-slate-800 dark:bg-slate-800/60">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $result['label'] }}</span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $levelBadgeClasses[$result['level']] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $result['level_label'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @elseif($submission->category === \App\Models\InstrumentQuestion::CATEGORY_MINAT_BAKAT && $submission->kode_minat)
                    <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-blue-600 dark:text-blue-300">
                            Kode Minat: {{ $submission->kode_minat }}
                            @if($submission->jenjang)
                                · {{ $submission->jenjang }}
                            @endif
                        </p>
                        @php
                            $topScores = collect($submission->category_scores ?? [])->take(3);
                        @endphp
                        @if($topScores->isNotEmpty())
                            <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                @foreach($topScores as $scoreRow)
                                    {{ $scoreRow['kode'] ?? '?' }} {{ number_format((float) ($scoreRow['persen'] ?? 0), 1) }}%@if(! $loop->last) · @endif
                                @endforeach
                            </p>
                        @endif
                        <p class="mt-2 font-semibold text-slate-900 dark:text-slate-100">{{ $submission->result_label }}</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $submission->result_description }}</p>
                    </div>
                @elseif($submission->category === \App\Models\InstrumentQuestion::CATEGORY_KEPRIBADIAN)
                    @php
                        $mbtiTally = (array) ($submission->category_scores['tally'] ?? []);
                        $mbtiBreakdown = \App\Support\Mbti::axisBreakdown($mbtiTally);
                    @endphp
                    <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-blue-600 dark:text-blue-300">Tipe Kepribadian MBTI</p>
                        <p class="mt-1 text-lg font-bold text-slate-950 dark:text-white">{{ $submission->result_label }}</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $submission->result_description }}</p>
                        @if($submission->percentage !== null)
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Keyakinan skoring: {{ number_format((float) $submission->percentage, 1) }}%</p>
                        @endif

                        @if($mbtiBreakdown !== [])
                            <div class="mt-3 space-y-2">
                                @foreach($mbtiBreakdown as $axis)
                                    <div>
                                        <div class="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-300">
                                            <span>{{ $axis['chosen_label'] }} ({{ $axis['code'] }}) <span class="font-normal text-slate-400">vs {{ $axis['other_label'] }} ({{ $axis['other'] }})</span></span>
                                            <span>{{ $axis['count'] }}/{{ $axis['total'] }} · {{ $axis['percentage'] }}%</span>
                                        </div>
                                        <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                            <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400" style="width: {{ $axis['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $submission->result_label }}</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $submission->result_description }}</p>
                        @if($submission->percentage !== null)
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ number_format((float) $submission->percentage, 2) }}%</p>
                        @endif
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
