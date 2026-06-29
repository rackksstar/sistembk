@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Hasil Skoring Instrumen" description="Pantau hasil skor otomatis dari jawaban siswa." />
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
                <div class="mt-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $submission->result_label }}</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $submission->result_description }}</p>
                </div>
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
