@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Log Aktivitas" description="Catatan aksi penting di sistem (read-only)." />
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="overflow-x-auto rounded-3xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4 text-left">Waktu</th>
                        <th class="px-5 py-4 text-left">Pengguna</th>
                        <th class="px-5 py-4 text-left">Aksi</th>
                        <th class="px-5 py-4 text-left">Subjek</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-5 py-4 text-slate-600">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="px-5 py-4">{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td class="px-5 py-4 font-medium text-slate-900">{{ $log->action }}</td>
                            <td class="px-5 py-4 text-xs text-slate-500">{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '-' }}</td>
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
