@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <x-section-title
            title="Konseling & Laporan"
            description="Monitoring semua pengajuan, jadwal, hasil konseling, dan evaluasi dari Guru BK."
        />
        <form method="GET" class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_170px_170px_190px_auto]">
            <div class="min-w-0">
                <label for="search" class="sr-only">Cari konseling</label>
                <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Cari topik, siswa, NISN, prodi, atau guru BK..."
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm placeholder:text-slate-400 focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
            </div>
            <div class="min-w-0">
                <label for="filter-status" class="sr-only">Filter status</label>
                <select id="filter-status" name="status" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
                    <option value="">Semua status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0">
                <label for="filter-kategori" class="sr-only">Filter kategori</label>
                <select id="filter-kategori" name="kategori" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
                    <option value="">Semua kategori</option>
                    @foreach($caseCategories as $value => $label)
                        <option value="{{ $value }}" @selected($kategori === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0">
                <label for="filter-sekolah" class="sr-only">Filter sekolah</label>
                <select id="filter-sekolah" name="sekolah_id" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
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

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Siswa</th>
                            <th class="px-5 py-4">Sekolah / Kelas</th>
                            <th class="px-5 py-4">Guru BK</th>
                            <th class="px-5 py-4">Keluhan/Topik</th>
                            <th class="px-5 py-4">Jadwal</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Laporan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($consultations as $consultation)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $consultation->student?->name }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                    <span class="block">{{ $consultation->student?->studentProfile?->kelas?->sekolah?->nama ?? '—' }}</span>
                                    <span class="text-xs text-slate-400">{{ $consultation->student?->studentProfile?->kelas?->nama ?? '—' }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $consultation->counselor?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                    <span class="block">{{ $consultation->subject }}</span>
                                    @if($consultation->isProdiKuliah())
                                        <span class="mt-1 inline-flex rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                                            Prodi: {{ $consultation->programStudi?->nama ?? '—' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                    @if($consultation->consultation_date)
                                        {{ $consultation->consultation_date->format('d M Y') }} {{ $consultation->consultation_time ? substr($consultation->consultation_time, 0, 5) : '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-5 py-4"><x-status-badge :status="$consultation->status" /></td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                    @if($consultation->result || $consultation->evaluation)
                                        <span class="rounded-full bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300">Ada laporan</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-400">Belum ada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-6">
                                    <x-empty-state title="Belum ada data konseling" description="Data akan muncul setelah siswa mengajukan konseling." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $consultations->links() }}</div>
    </section>
</div>
@endsection
