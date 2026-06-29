@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title
                title="Konseling & Laporan"
                description="Monitoring semua pengajuan, jadwal, hasil konseling, dan evaluasi dari Guru BK."
            />
            <form method="GET" class="flex gap-3">
                <select name="status" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
                    <option value="">Semua status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Filter</button>
            </form>
        </div>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Siswa</th>
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
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $consultation->counselor?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $consultation->details ?? $consultation->subject }}</td>
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
                                <td colspan="6" class="px-5 py-6">
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
