@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Log Aktivitas" description="Catatan aksi penting di sistem (read-only)." />

        <form method="GET" class="mt-6 grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]">
            <x-text-input name="search" value="{{ $search }}" placeholder="Cari aksi atau nama pengguna..." class="w-full" />
            <x-form-select name="action">
                <option value="">Semua aksi</option>
                @foreach($actions as $item)
                    <option value="{{ $item }}" @selected($action === $item)>{{ $item }}</option>
                @endforeach
            </x-form-select>
            <button class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Filter</button>
        </form>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="overflow-x-auto rounded-3xl border border-slate-200 dark:border-slate-700">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-4 text-left">Waktu</th>
                        <th class="px-5 py-4 text-left">Pengguna</th>
                        <th class="px-5 py-4 text-left">Aksi</th>
                        <th class="px-5 py-4 text-left">Subjek</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td class="px-5 py-4 font-medium text-slate-900 dark:text-slate-100">{{ $log->action }}</td>
                            <td class="px-5 py-4 text-xs text-slate-500 dark:text-slate-400">{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8"><x-empty-state title="Belum ada log" description="Log akan muncul saat pengguna melakukan aksi penting." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $logs->links() }}</div>
    </section>
</div>
@endsection
