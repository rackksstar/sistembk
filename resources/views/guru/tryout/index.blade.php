@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <x-section-title title="Tryout BK" description="Buat dan pantau hasil tryout per kelas." />
            <a href="{{ route('guru.tryout.create') }}" class="rounded-2xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Buat tryout</a>
        </div>
        <x-alert class="mt-4" type="success" :message="session('success')" />
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="overflow-x-auto rounded-3xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4 text-left">Judul</th>
                        <th class="px-5 py-4 text-left">Periode</th>
                        <th class="px-5 py-4 text-left">Kelas</th>
                        <th class="px-5 py-4 text-left">Peserta</th>
                        <th class="px-5 py-4 text-left">Status</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tryouts as $tryout)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $tryout->judul }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $tryout->mulai_at->format('d M Y H:i') }} – {{ $tryout->selesai_at->format('d M H:i') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $tryout->kelas->pluck('nama')->join(', ') ?: '-' }}</td>
                            <td class="px-5 py-4">{{ $tryout->details_count }}</td>
                            <td class="px-5 py-4">{{ $tryout->statusLabel() }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('guru.tryout.show', $tryout) }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Hasil</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8"><x-empty-state title="Belum ada tryout" description="Buat tryout pertama untuk siswa di kelas yang ditugaskan." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $tryouts->links() }}</div>
    </section>
</div>
@endsection
