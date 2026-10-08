@extends('layouts.app')

@php
    $levelClasses = [
        'kurang' => [
            'card' => 'border-red-200 dark:border-red-900/60',
            'badge' => 'bg-red-600 text-white',
            'badgeIcon' => 'bg-white/20 text-white',
            'box' => 'bg-red-50 dark:bg-red-950/30',
            'number' => 'bg-red-600 text-white',
        ],
        'cukup' => [
            'card' => 'border-amber-200 dark:border-amber-900/60',
            'badge' => 'bg-amber-500 text-white',
            'badgeIcon' => 'bg-white/20 text-white',
            'box' => 'bg-amber-50 dark:bg-amber-950/30',
            'number' => 'bg-amber-500 text-white',
        ],
        'baik' => [
            'card' => 'border-emerald-200 dark:border-emerald-900/60',
            'badge' => 'bg-emerald-600 text-white',
            'badgeIcon' => 'bg-white/20 text-white',
            'box' => 'bg-emerald-50 dark:bg-emerald-950/30',
            'number' => 'bg-emerald-600 text-white',
        ],
    ];
@endphp

@section('content')
<div class="space-y-6">
    <section class="overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="bg-gradient-to-r from-sky-500 to-blue-600 px-6 py-5 text-center">
            <h2 class="text-lg font-bold text-white">Hasil Tes Strategi Belajar</h2>
        </div>
        <div class="relative bg-white dark:bg-slate-900 px-6 py-6 pr-24">
            <a href="{{ route('siswa.instruments.index', ['category' => 'strategi_belajar']) }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                &larr; Kembali ke Instrumen Asesmen
            </a>
            <h3 class="mt-2 text-base font-bold text-slate-950 dark:text-white">Lihat hasil tesmu di sini</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Dikirim {{ $submission->submitted_at?->format('d M Y H:i') }}
            </p>
            <span class="pointer-events-none absolute right-6 top-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-500 dark:text-blue-400" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-8 w-8">
                    <rect x="4" y="3" width="16" height="18" rx="2" stroke="currentColor" stroke-width="1.6" />
                    <path d="M8 7h8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                    <rect x="7.5" y="10" width="3" height="3" rx="0.5" fill="currentColor" />
                    <rect x="13.5" y="10" width="3" height="3" rx="0.5" fill="currentColor" />
                    <rect x="7.5" y="15" width="3" height="3" rx="0.5" fill="currentColor" />
                    <rect x="13.5" y="15" width="3" height="3" rx="0.5" fill="currentColor" />
                </svg>
            </span>
        </div>
    </section>

    @foreach($results as $result)
        @php($classes = $levelClasses[$result['level']])
        <section
            class="rounded-3xl border-2 {{ $classes['card'] }} bg-white dark:bg-slate-900 p-6 shadow-sm"
            x-data="{ open: true }"
        >
            <div class="flex flex-wrap items-start justify-between gap-3">
                <h3 class="text-lg font-bold text-slate-950 dark:text-white">{{ $result['label'] }}</h3>
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold {{ $classes['badge'] }}">
                    <span class="flex h-4 w-4 items-center justify-center rounded-full text-[10px] {{ $classes['badgeIcon'] }}">
                        {{ $result['level'] === 'kurang' ? '!' : '✓' }}
                    </span>
                    {{ $result['level_label'] }}
                </span>
            </div>

            <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">
                {{ $result['description'] }}
            </p>

            <div class="mt-5 border-t border-dashed border-slate-200 dark:border-slate-700 pt-4">
                <button
                    type="button"
                    x-on:click="open = !open"
                    class="flex w-full items-center justify-between text-left text-sm font-semibold text-slate-900 dark:text-slate-100"
                >
                    Strategi belajar yang bisa dicoba
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div x-show="open" x-cloak class="mt-4 space-y-4 rounded-2xl {{ $classes['box'] }} p-5">
                    @foreach($result['tips'] as $index => $tip)
                        <div class="flex items-start gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $classes['number'] }}">
                                {{ $index + 1 }}
                            </span>
                            <p class="text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $tip }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach
</div>
@endsection
