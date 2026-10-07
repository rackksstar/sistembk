@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-300">{{ $submission->categoryLabel() }}</p>
        <h1 class="mt-3 text-2xl font-bold text-slate-950 dark:text-white">{{ $submission->result_label }}</h1>
        <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400">{{ $submission->result_description }}</p>
        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
            Skor: {{ $submission->total_score }} · {{ $submission->submitted_at?->format('d M Y H:i') }}
        </p>
        <div class="mt-6">
            <a href="{{ route('siswa.instruments.index', ['category' => $submission->category]) }}" class="inline-flex rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white">
                Kembali ke instrumen
            </a>
        </div>
    </section>
</div>
@endsection
