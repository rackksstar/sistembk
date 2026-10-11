@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }}, editOpen: null, detailOpen: null }">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Laporan Konseling Individu" description="Pilih sesi konseling individu, catat hasil pelaksanaannya, dan cetak laporan PDF." />
            @if($isGuru)
                <button type="button" x-on:click="createOpen = true" @disabled($availableConsultations->isEmpty()) class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50">Tambah Laporan</button>
            @endif
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />
        @if($errors->any())
            <x-alert class="mt-5" type="error" :message="$errors->first()" />
        @endif

        <form method="GET" class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_170px_170px_190px_190px_auto]">
            <div class="min-w-0">
                <label for="search" class="sr-only">Cari laporan</label>
                <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Cari judul, sesi, siswa, NISN, atau guru BK..."
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm placeholder:text-slate-400 focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
            </div>
            <div class="min-w-0">
                <label for="filter-status" class="sr-only">Filter status</label>
                <select id="filter-status" name="status" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
                    <option value="">Semua status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0">
                <label for="filter-kategori" class="sr-only">Filter kategori kasus</label>
                <select id="filter-kategori" name="case_category" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
                    <option value="">Semua kategori kasus</option>
                    @foreach($caseCategories as $value => $label)
                        <option value="{{ $value }}" @selected($kategori === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0">
                <label for="filter-rpl" class="sr-only">Filter RPL individu</label>
                <select id="filter-rpl" name="rpl_id" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
                    <option value="">Semua RPL individu</option>
                    @foreach($rplOptions as $rpl)
                        <option value="{{ $rpl->id }}" @selected($rplId === $rpl->id)>{{ $rpl->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0">
                <label for="filter-sekolah" class="sr-only">Filter sekolah</label>
                <select id="filter-sekolah" name="sekolah_id" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
                    <option value="">Semua sekolah</option>
                    @foreach($sekolahOptions as $sekolah)
                        <option value="{{ $sekolah->id }}" @selected((string) $sekolahId === (string) $sekolah->id)>{{ $sekolah->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 sm:col-span-2 xl:col-span-1">
                <button class="flex-1 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Filter</button>
                <x-filter-reset />
            </div>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse($consultations as $consultation)
            <article class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-xs">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <span class="rounded-full bg-blue-50 dark:bg-blue-950/40 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-blue-700 dark:text-blue-300">{{ $consultation->caseCategoryLabel() }}</span>
                        <h3 class="mt-4 text-lg font-bold text-slate-950 dark:text-white">{{ $consultation->report_title ?: $consultation->subject }}</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $consultation->student?->name ?? 'Siswa tidak tersedia' }} · {{ $consultation->student?->studentProfile?->kelas?->nama ?? 'Tanpa kelas' }} · {{ $consultation->consultation_date?->format('d M Y') ?? '-' }}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sesi: {{ $consultation->subject }}@if($consultation->rpl) · RPL: {{ $consultation->rpl->title }}@endif</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route($printRoute, $consultation) }}" target="_blank" class="rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">Cetak PDF</a>
                        <button type="button" x-on:click="detailOpen = {{ $consultation->id }}" class="rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Detail</button>
                        @if($isGuru)
                            <button type="button" x-on:click="editOpen = {{ $consultation->id }}" class="rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Edit</button>
                        @endif
                    </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><p class="font-semibold text-slate-900 dark:text-slate-100">Hasil</p><p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $consultation->result ?: '-' }}</p></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><p class="font-semibold text-slate-900 dark:text-slate-100">Evaluasi</p><p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $consultation->evaluation ?: '-' }}</p></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 md:col-span-2"><p class="font-semibold text-slate-900 dark:text-slate-100">Tindak Lanjut</p><p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $consultation->follow_up ?: '-' }}</p></div>
                </div>

                @if($isGuru)
                    <form method="POST" action="{{ route('guru.individual-reports.destroy', $consultation) }}" class="mt-4" onsubmit="return confirm('Hapus laporan ini? Sesi konseling tetap tersimpan.')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Hapus Laporan</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="xl:col-span-2">
                <x-empty-state title="Belum ada laporan individu" description="{{ $isGuru ? 'Pilih sesi konseling individu yang sudah dijadwalkan untuk membuat laporan.' : 'Laporan konseling individu akan muncul di sini setelah dibuat.' }}" />
            </div>
        @endforelse
    </section>

    {{ $consultations->links() }}

    @if($isGuru)
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="createOpen = false" class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <x-section-title title="Tambah Laporan Individu" description="Pilih sesi konseling individu lalu isi laporan pelaksanaannya." />
                    <button type="button" x-on:click="createOpen = false" class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-sm font-semibold text-slate-600 dark:text-slate-300">x</button>
                </div>
                @if($availableConsultations->isEmpty())
                    <x-empty-state class="mt-5" title="Tidak ada sesi konseling yang tersedia" description="Sesi konseling individu yang sudah dijadwalkan dan belum memiliki laporan akan muncul di sini." />
                @else
                    <form method="POST" action="{{ route('guru.individual-reports.store') }}" class="mt-6">
                        @csrf
                        @include('guru.individual-reports.partials.form', ['report' => null, 'availableConsultations' => $availableConsultations, 'submit' => 'Simpan Laporan'])
                    </form>
                @endif
            </div>
        </div>

        @foreach($consultations as $consultation)
            <div x-show="editOpen === {{ $consultation->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
                <div x-on:click.outside="editOpen = null" class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <x-section-title title="Edit Laporan Individu" description="Perbarui laporan untuk sesi konseling yang dipilih." />
                        <button type="button" x-on:click="editOpen = null" class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-sm font-semibold text-slate-600 dark:text-slate-300">x</button>
                    </div>
                    <form method="POST" action="{{ route('guru.individual-reports.update', $consultation) }}" class="mt-6">
                        @csrf
                        @method('PUT')
                        @include('guru.individual-reports.partials.form', ['report' => $consultation, 'availableConsultations' => collect(), 'submit' => 'Update Laporan'])
                    </form>
                </div>
            </div>

            <div x-show="detailOpen === {{ $consultation->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
                <div x-on:click.outside="detailOpen = null" class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <x-section-title title="Detail Laporan Individu" :description="$consultation->report_title ?: $consultation->subject" />
                        <button type="button" x-on:click="detailOpen = null" class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-sm font-semibold text-slate-600 dark:text-slate-300">Tutup</button>
                    </div>
                    <dl class="mt-6 grid gap-3 md:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Siswa / NISN</dt><dd class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $consultation->student?->name ?? '-' }} / {{ $consultation->student?->studentProfile?->nisn ?? '-' }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Guru BK</dt><dd class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $consultation->counselor?->name ?? '-' }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Kategori / Status</dt><dd class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $consultation->caseCategoryLabel() }} / {{ $consultation->statusLabel() }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Tanggal / Waktu</dt><dd class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $consultation->consultation_date?->format('d M Y') ?? '-' }} / {{ $consultation->consultation_time ? substr($consultation->consultation_time, 0, 5) : '-' }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 md:col-span-2"><dt class="font-semibold">Sesi / Uraian Masalah</dt><dd class="mt-1 whitespace-pre-line text-sm text-slate-600 dark:text-slate-400">{{ $consultation->subject }} — {{ $consultation->details ?: '-' }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 md:col-span-2"><dt class="font-semibold">Hasil</dt><dd class="mt-1 whitespace-pre-line text-sm text-slate-600 dark:text-slate-400">{{ $consultation->result ?: '-' }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 md:col-span-2"><dt class="font-semibold">Evaluasi</dt><dd class="mt-1 whitespace-pre-line text-sm text-slate-600 dark:text-slate-400">{{ $consultation->evaluation ?: '-' }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 md:col-span-2"><dt class="font-semibold">Tindak Lanjut</dt><dd class="mt-1 whitespace-pre-line text-sm text-slate-600 dark:text-slate-400">{{ $consultation->follow_up ?: '-' }}</dd></div>
                    </dl>
                    <a href="{{ route($printRoute, $consultation) }}" target="_blank" class="mt-5 inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">Cetak PDF</a>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
