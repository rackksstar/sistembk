@extends('layouts.app')

@php
    $codeClasses = [
        'R' => ['ring' => 'bg-orange-500', 'bar' => 'bg-orange-500', 'chip' => 'bg-orange-100 text-orange-700 dark:bg-orange-950/50 dark:text-orange-300'],
        'I' => ['ring' => 'bg-blue-500', 'bar' => 'bg-blue-500', 'chip' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300'],
        'A' => ['ring' => 'bg-purple-500', 'bar' => 'bg-purple-500', 'chip' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300'],
        'S' => ['ring' => 'bg-emerald-500', 'bar' => 'bg-emerald-500', 'chip' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'],
        'E' => ['ring' => 'bg-amber-500', 'bar' => 'bg-amber-500', 'chip' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300'],
        'C' => ['ring' => 'bg-cyan-500', 'bar' => 'bg-cyan-500', 'chip' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-300'],
    ];
@endphp

@section('content')
<div class="space-y-6">
    <section class="overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-6 py-5 text-center">
            <h2 class="text-lg font-bold text-white">Hasil Minat Bakat — Talents Mapping</h2>
        </div>
        <div class="bg-white dark:bg-slate-900 px-6 py-6">
            <a href="{{ route('siswa.instruments.index', ['category' => 'minat_bakat']) }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                &larr; Kembali ke Instrumen Asesmen
            </a>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Dikirim {{ $submission->submitted_at?->format('d M Y H:i') }}
            </p>

            <div class="mt-4 flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-1">
                    @foreach(str_split($dominantCode) as $letter)
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl text-xl font-bold text-white {{ $codeClasses[$letter]['ring'] ?? 'bg-slate-500' }}">
                            {{ $letter }}
                        </span>
                    @endforeach
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Kode Dominan (Holland Code)</p>
                    <p class="text-lg font-bold text-slate-950 dark:text-white">{{ collect($results)->take(3)->pluck('label')->implode(' – ') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Rincian per Dimensi RIASEC" description="Semakin tinggi persentase, semakin kuat kecenderungan minatmu pada dimensi tersebut." />

        <div class="mt-6 space-y-5">
            @foreach($results as $result)
                @php($classes = $codeClasses[$result['code']] ?? ['ring' => 'bg-slate-500', 'bar' => 'bg-slate-500', 'chip' => 'bg-slate-100 text-slate-700'])
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white {{ $classes['ring'] }}">
                                {{ $result['code'] }}
                            </span>
                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $result['label'] }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes['chip'] }}">
                            {{ $result['percentage'] }}%
                        </span>
                    </div>
                    <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full {{ $classes['bar'] }}" style="width: {{ $result['percentage'] }}%"></div>
                    </div>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $result['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
