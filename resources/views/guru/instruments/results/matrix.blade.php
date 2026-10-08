@extends('layouts.app')

@php
    $codeDotClasses = [
        'R' => 'bg-orange-500',
        'I' => 'bg-blue-500',
        'A' => 'bg-purple-500',
        'S' => 'bg-emerald-500',
        'E' => 'bg-amber-500',
        'C' => 'bg-cyan-500',
    ];
@endphp

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <a href="{{ route('guru.instrument-results.index', ['module' => 'key', 'category' => 'minat_bakat']) }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
            &larr; Kembali ke Hasil Minat Bakat
        </a>
        <x-section-title class="mt-3" title="Matriks Talents Mapping" description="Peta skor RIASEC (Realistic, Investigative, Artistic, Social, Enterprising, Conventional) tiap siswa dari hasil Minat Bakat terakhir mereka." />
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xs">
        @if($submissions->isEmpty())
            <div class="p-6">
                <x-empty-state title="Belum ada hasil" description="Matriks akan muncul setelah siswa mengisi instrumen Minat Bakat." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Siswa</th>
                            <th class="px-5 py-4">Kelas</th>
                            @foreach($riasecCodes as $code => $label)
                                <th class="px-3 py-4 text-center" title="{{ $label }}">{{ $code }}</th>
                            @endforeach
                            <th class="px-5 py-4">Kode Dominan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($submissions as $submission)
                            @php($scores = collect($submission->riasecScores())->keyBy('code'))
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $submission->student?->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $submission->submitted_at?->format('d M Y') }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                    {{ $submission->student?->classModel?->name ?? '-' }}
                                </td>
                                @foreach($riasecCodes as $code => $label)
                                    @php($pct = $scores[$code]['percentage'] ?? 0)
                                    <td class="px-3 py-4 text-center">
                                        <span
                                            class="inline-flex h-9 w-12 items-center justify-center rounded-lg text-xs font-bold text-slate-900 dark:text-white"
                                            style="background-color: rgba(59, 130, 246, {{ min($pct / 100, 0.85) }})"
                                        >{{ $pct }}%</span>
                                    </td>
                                @endforeach
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-1">
                                        @foreach(str_split($submission->dominantRiasecCode()) as $letter)
                                            <span class="flex h-6 w-6 items-center justify-center rounded-md text-xs font-bold text-white {{ $codeDotClasses[$letter] ?? 'bg-slate-500' }}">
                                                {{ $letter }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
