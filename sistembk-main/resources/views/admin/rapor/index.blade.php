@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title
            title="Pantau Rapor BK"
            description="Tampilan read-only seluruh rapor yang dibuat guru BK."
        />

        <form method="GET" class="mt-6 flex flex-wrap items-end gap-3">
            <div class="space-y-2">
                <x-input-label for="semester" value="Semester" />
                <select id="semester" name="semester" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm">
                    <option value="">Semua</option>
                    @foreach(\App\Models\RaporBk::SEMESTERS as $value => $label)
                        <option value="{{ $value }}" @selected($semester === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <x-input-label for="tahun_ajaran" value="Tahun ajaran" />
                <input type="text" id="tahun_ajaran" name="tahun_ajaran" value="{{ $tahunAjaran }}" placeholder="2025/2026"
                    class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm">
            </div>
            <div class="space-y-2">
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm">
                    <option value="">Semua</option>
                    @foreach(\App\Models\RaporBk::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Filter</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Siswa</th>
                            <th class="px-5 py-4">Kelas</th>
                            <th class="px-5 py-4">Guru BK</th>
                            <th class="px-5 py-4">Periode</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($rapor as $item)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $item->student->user?->name ?? $item->student->name }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $item->student->kelas?->nama ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $item->counselor?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $item->semesterLabel() }} · {{ $item->tahun_ajaran }}</td>
                                <td class="px-5 py-4">
                                    <span @class([
                                        'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' => $item->status === \App\Models\RaporBk::STATUS_FINAL,
                                        'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300' => $item->status !== \App\Models\RaporBk::STATUS_FINAL,
                                    ])>{{ $item->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.rapor.show', $item) }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Detail</a>
                                        <a href="{{ route('admin.rapor.pdf', $item) }}" class="rounded-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-500">PDF</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8">
                                    <x-empty-state title="Belum ada rapor" description="Rapor akan muncul setelah guru BK menyimpan data." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-5">{{ $rapor->links() }}</div>
    </section>
</div>
@endsection
