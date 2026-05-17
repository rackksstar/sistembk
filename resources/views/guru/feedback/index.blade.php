@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Feedback Layanan" description="Masukan siswa untuk peningkatan layanan BK." />
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse($feedback as $item)
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold text-slate-950">{{ $item->student?->name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $item->student?->classModel?->name ?? '-' }} | {{ $item->student?->schoolModel?->name ?? '-' }}</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700">{{ $item->rating }}/5</span>
                </div>
                <p class="mt-4 text-sm font-semibold text-slate-900">{{ $item->service_type }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $item->consultation?->subject ?? 'Feedback umum' }}</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $item->message }}</p>
                @if($item->suggestion)
                    <p class="mt-3 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">{{ $item->suggestion }}</p>
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
