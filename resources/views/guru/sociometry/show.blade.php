@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title :title="'Analisis Sosiometri: '. $student->name" description="Preview analisis sosiometri untuk siswa" />
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border p-4">
                <p class="text-sm text-slate-500">Total Masuk</p>
                <p class="text-2xl font-bold">{{ $totalInbound }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-slate-500">Total Keluar</p>
                <p class="text-2xl font-bold">{{ $totalOutbound }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-slate-500">Mutual (Reciprocity)</p>
                <p class="text-2xl font-bold">{{ $mutualCount }}</p>
            </div>
        </div>

        <div class="mt-6">
            <p class="font-semibold">Status: <span class="text-sm text-slate-700">{{ $status }}</span></p>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Pilihan Masuk (Diterima)" description="Siapa saja yang memilih siswa ini." />
        <div class="mt-4">
            @if($inbound->isEmpty())
                <p class="text-sm text-slate-500">Belum ada pilihan masuk.</p>
            @else
                <ul class="space-y-2">
                    @foreach($inbound as $r)
                        <li class="flex items-center justify-between border rounded-lg p-3">
                            <div>
                                <a href="{{ route('guru.sociometry.show', $r->student->id) }}" class="font-semibold">{{ $r->student->name ?? '—' }}</a>
                                <div class="text-xs text-slate-500">{{ \App\Models\SociometryResponse::TYPES[$r->relation_type] ?? $r->relation_type }}</div>
                            </div>
                            <div class="text-sm text-slate-600">{{ $r->reason ?: '-' }}</div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Pilihan Keluar (Dipilih oleh siswa ini)" description="Siapa yang dipilih oleh siswa." />
        <div class="mt-4">
            @if($outbound->isEmpty())
                <p class="text-sm text-slate-500">Belum memilih siapa pun.</p>
            @else
                <ul class="space-y-2">
                    @foreach($outbound as $r)
                        <li class="flex items-center justify-between border rounded-lg p-3">
                            <div>
                                <a href="{{ route('guru.sociometry.show', $r->chosenStudent->id) }}" class="font-semibold">{{ $r->chosenStudent->name ?? '—' }}</a>
                                <div class="text-xs text-slate-500">{{ \App\Models\SociometryResponse::TYPES[$r->relation_type] ?? $r->relation_type }}</div>
                            </div>
                            <div class="text-sm text-slate-600">{{ $r->reason ?: '-' }}</div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
</div>
@endsection
