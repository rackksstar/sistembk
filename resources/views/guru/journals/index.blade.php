@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }}, editOpen: null }">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Jurnal Bulanan BK" description="Catat rekap layanan bulanan dan export ke PDF." />
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <label for="year" class="sr-only">Filter Tahun</label>
                    <select id="year" name="year" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $filterYear)
                            <option value="{{ $filterYear }}" @selected($filterYear === $year)>{{ $filterYear }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Filter</button>
                </form>
                <button type="button" x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Tambah Jurnal</button>
            </div>
        </div>
        <x-alert class="mt-5" type="success" :message="session('success')" />
        @if($errors->any())
            <x-alert class="mt-5" type="error" message="Periksa kembali isian jurnal." />
        @endif
    </section>

    @if($groupedJournals->isNotEmpty())
        @foreach($groupedJournals as $groupYear => $yearJournals)
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Tahun {{ $groupYear }}</h2>
            </section>
            <section class="grid gap-4 xl:grid-cols-2">
                @foreach($yearJournals as $journal)
                    <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">{{ $journal->periodLabel() }}</p>
                                <h3 class="mt-2 text-lg font-bold text-slate-950">{{ $journal->title }}</h3>
                                <p class="mt-1 text-sm text-slate-500">Individu {{ $journal->individual_services }} | Kelompok {{ $journal->group_services }} | Klasikal {{ $journal->classical_services }}</p>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('guru.journals.print', $journal) }}" target="_blank" class="rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white">PDF</a>
                                <button type="button" x-on:click="editOpen = {{ $journal->id }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold">Edit</button>
                            </div>
                        </div>
                        <p class="mt-4 line-clamp-3 text-sm leading-6 text-slate-600">{{ $journal->summary }}</p>
                        <form method="POST" action="{{ route('guru.journals.destroy', $journal) }}" class="mt-4" onsubmit="return confirm('Hapus jurnal ini?')">
                            @csrf @method('DELETE')
                            <button class="rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white">Hapus</button>
                        </form>
                    </article>
                @endforeach
            </section>
        @endforeach
    @else
        <div class="xl:col-span-2">
            <x-empty-state title="Belum ada jurnal" description="Buat jurnal bulanan pertama untuk rekap layanan BK." />
        </div>
    @endif

    {{ $journals->links() }}

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
            <x-section-title title="Tambah Jurnal" description="Rekap layanan dalam satu bulan." />
            <form method="POST" action="{{ route('guru.journals.store') }}" class="mt-6">
                @csrf
                @include('guru.journals.partials.form', ['journal' => null, 'submit' => 'Simpan Jurnal'])
            </form>
        </div>
    </div>

    @foreach($journals as $journal)
        <div x-show="editOpen === {{ $journal->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
                <x-section-title title="Edit Jurnal" description="{{ $journal->periodLabel() }}" />
                <form method="POST" action="{{ route('guru.journals.update', $journal) }}" class="mt-6">
                    @csrf @method('PUT')
                    @include('guru.journals.partials.form', ['journal' => $journal, 'submit' => 'Update Jurnal'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
