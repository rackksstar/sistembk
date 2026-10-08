@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Feedback Layanan" description="Masukan siswa untuk peningkatan layanan BK." />
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse($feedback as $item)
            <article class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold text-slate-950 dark:text-white">{{ $item->student?->name }}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $item->student?->classModel?->name ?? '-' }} | {{ $item->student?->schoolModel?->name ?? '-' }}</p>
                    </div>
                    <span class="rounded-full bg-blue-50 dark:bg-blue-950/40 px-3 py-1 text-sm font-semibold text-blue-700 dark:text-blue-300">{{ $item->rating }}/5</span>
                </div>
                <p class="mt-4 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $item->service_type }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $item->consultation?->subject ?? 'Feedback umum' }}</p>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $item->message }}</p>
                @if($item->suggestion)
                    <p class="mt-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $item->suggestion }}</p>
                @endif
            </article>
        @empty
            <div class="xl:col-span-2">
                <x-empty-state title="Belum ada feedback" description="Feedback siswa akan tampil setelah form dikirim." />
            </div>
        @endforelse
    </section>

    {{ $feedback->links() }}
</div>
@endsection
