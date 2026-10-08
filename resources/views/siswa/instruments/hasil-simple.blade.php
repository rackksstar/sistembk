@extends('layouts.app')

@php
    $category = $submission->category;
    $categoryLabel = $submission->categoryLabel();
    $percentage = $submission->percentage !== null ? (float) $submission->percentage : null;
    $isMbti = $category === \App\Models\InstrumentQuestion::CATEGORY_KEPRIBADIAN;
    $mbtiTally = $isMbti ? (array) ($submission->category_scores['tally'] ?? []) : [];
    $mbtiBreakdown = $isMbti ? \App\Support\Mbti::axisBreakdown($mbtiTally) : [];
    $scoreTone = match (true) {
        $percentage === null => [
            'ring' => 'from-slate-400 to-slate-500',
            'chip' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            'bar' => 'bg-slate-500',
        ],
        $percentage < 40 => [
            'ring' => 'from-red-500 to-rose-600',
            'chip' => 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300',
            'bar' => 'bg-red-500',
        ],
        $percentage < 70 => [
            'ring' => 'from-amber-400 to-orange-500',
            'chip' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300',
            'bar' => 'bg-amber-500',
        ],
        default => [
            'ring' => 'from-emerald-500 to-teal-600',
            'chip' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300',
            'bar' => 'bg-emerald-600',
        ],
    };
@endphp

@section('content')
<div class="space-y-6">
    <x-alert type="success" :message="session('success')" />

    <section class="overflow-hidden rounded-3xl border border-slate-200 shadow-xs dark:border-slate-700">
        <div class="bg-linear-to-r from-emerald-500 to-teal-600 px-6 py-5 text-center">
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-100">Modul Yola</p>
            <h1 class="mt-1 text-lg font-bold text-white">Hasil {{ $categoryLabel }}</h1>
        </div>

        <div class="relative bg-white px-6 py-6 dark:bg-slate-900">
            <a
                href="{{ route('siswa.instruments.index', ['category' => $category]) }}"
                class="text-xs font-semibold text-emerald-700 hover:underline dark:text-emerald-300"
            >
                ← Kembali ke Instrumen Asesmen
            </a>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Dikirim {{ $submission->submitted_at?->format('d M Y H:i') }}
            </p>

            <div class="mt-6 flex flex-col gap-5 sm:flex-row sm:items-center">
                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-3xl bg-linear-to-br {{ $scoreTone['ring'] }} text-white shadow-lg shadow-emerald-500/20">
                    @if($percentage !== null)
                        <div class="text-center">
                            <p class="text-2xl font-bold leading-none">{{ number_format($percentage, 0) }}</p>
                            <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide opacity-90">%</p>
                        </div>
                    @else
                        <div class="text-center">
                            <p class="text-2xl font-bold leading-none">{{ $submission->total_score }}</p>
                            <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide opacity-90">skor</p>
                        </div>
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $scoreTone['chip'] }}">
                        {{ $submission->result_label }}
                    </span>
                    <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                        {{ $submission->result_description }}
                    </p>
                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                        Skor total: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $submission->total_score }}</span>
                        @if($percentage !== null)
                            · {{ number_format($percentage, 2) }}%
                        @endif
                    </p>
                </div>
            </div>

            @if($percentage !== null)
                <div class="mt-6">
                    <div class="mb-2 flex items-center justify-between text-xs font-semibold text-slate-500 dark:text-slate-400">
                        <span>Tingkat kecocokan</span>
                        <span>{{ number_format($percentage, 1) }}%</span>
                    </div>
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full {{ $scoreTone['bar'] }}" style="width: {{ min(100, max(0, $percentage)) }}%"></div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if($isMbti)
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-xs dark:border-slate-700 dark:bg-slate-900">
            <x-section-title title="Rincian 4 Dimensi Kepribadian" description="Persentase pilihan tiap dimensi MBTI berdasarkan jawabanmu." />

            @if($mbtiBreakdown !== [])
                <div class="mt-5 space-y-4">
                    @foreach($mbtiBreakdown as $axis)
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-300">
                                <span>{{ $axis['chosen_label'] }} ({{ $axis['code'] }}) <span class="font-normal text-slate-400">vs {{ $axis['other_label'] }} ({{ $axis['other'] }})</span></span>
                                <span>{{ $axis['count'] }}/{{ $axis['total'] }} · {{ $axis['percentage'] }}%</span>
                            </div>
                            <div class="mt-1.5 h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-blue-600" style="width: {{ $axis['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Rincian per dimensi belum tersedia untuk jawaban ini.</p>
            @endif
        </section>
    @endif

    <section class="rounded-3xl border border-amber-200 bg-amber-50/80 p-5 text-sm leading-6 text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-200">
        Hasil ini gambaran kecenderungan dari jawabanmu, bukan keputusan akhir. Diskusikan dengan Guru BK bila ingin pendalaman.
    </section>

    <section class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
        <a
            href="{{ route('siswa.instruments.index', ['category' => $category]) }}"
            class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-500"
        >
            Ulangi asesmen
        </a>
        <a
            href="{{ route('siswa.dashboard') }}"
            class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60"
        >
            Kembali ke dashboard
        </a>
    </section>
</div>
@endsection
