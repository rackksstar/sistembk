@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }}, editOpen: null }">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Laporan Konseling Kelompok" description="Catat hasil pelaksanaan dari RPL konseling kelompok dan cetak PDF." />
            <button type="button" x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Tambah Laporan</button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        @if($errors->any())
            <x-alert class="mt-5" type="error" message="Periksa kembali data laporan kelompok." />
        @endif

        <form method="GET" action="{{ route('guru.group-reports.index') }}" class="mt-6 grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
            <x-form-select name="rpl_id">
                <option value="">Semua RPL kelompok</option>
                @foreach($groupRpls as $rpl)
                    <option value="{{ $rpl->id }}" @selected($rplId === $rpl->id)>{{ $rpl->title }} - {{ $rpl->classRoom?->name ?? 'Tanpa kelas' }}</option>
                @endforeach
            </x-form-select>
            <x-form-select name="case_category">
                <option value="">Semua kategori kasus</option>
                @foreach($caseCategories as $value => $label)
                    <option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>
                @endforeach
            </x-form-select>
            <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Filter</button>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse($reports as $report)
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-blue-700">{{ $report->caseCategoryLabel() }}</span>
                        <h3 class="mt-4 text-lg font-bold text-slate-950">{{ $report->title }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $report->classRoom?->name ?? '-' }} - {{ $report->service_date?->format('d M Y') }}</p>
                        <p class="mt-1 text-sm text-slate-500">RPL: {{ $report->rpl?->title ?? '-' }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('guru.group-reports.print', $report) }}" target="_blank" class="rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700">Cetak PDF</a>
                        <button type="button" x-on:click="editOpen = {{ $report->id }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit</button>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4 md:col-span-2">
                        <p class="font-semibold text-slate-900">Anggota Kelompok</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $report->rpl?->groupStudents?->pluck('name')->join(', ') ?: '-' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="font-semibold text-slate-900">Hasil</p><p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600">{{ $report->result }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="font-semibold text-slate-900">Evaluasi</p><p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600">{{ $report->evaluation }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4 md:col-span-2"><p class="font-semibold text-slate-900">Tindak Lanjut</p><p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600">{{ $report->follow_up ?: '-' }}</p></div>
                </div>

                <form method="POST" action="{{ route('guru.group-reports.destroy', $report) }}" class="mt-4" onsubmit="return confirm('Hapus laporan kelompok ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Hapus Laporan</button>
                </form>
            </article>
        @empty
            <div class="xl:col-span-2">
                <x-empty-state title="Belum ada laporan kelompok" description="Buat laporan dari RPL konseling kelompok yang sudah dibuat." />
            </div>
        @endforelse
    </section>

    {{ $reports->links() }}

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <x-section-title title="Tambah Laporan Kelompok" description="Hubungkan laporan dengan RPL konseling kelompok." />
                <button type="button" x-on:click="createOpen = false" class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">x</button>
            </div>
            <form method="POST" action="{{ route('guru.group-reports.store') }}" class="mt-6">
                @csrf
                @include('guru.group-reports.partials.form', ['report' => null, 'submit' => 'Simpan Laporan'])
            </form>
        </div>
    </div>

    @foreach($reports as $report)
        <div x-show="editOpen === {{ $report->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <x-section-title title="Edit Laporan Kelompok" description="Perbarui hasil pelaksanaan layanan kelompok." />
                    <button type="button" x-on:click="editOpen = null" class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">x</button>
                </div>
                <form method="POST" action="{{ route('guru.group-reports.update', $report) }}" class="mt-6">
                    @csrf
                    @method('PUT')
                    @include('guru.group-reports.partials.form', ['report' => $report, 'submit' => 'Update Laporan'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
