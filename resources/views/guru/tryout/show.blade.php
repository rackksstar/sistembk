@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title :title="$tryout->judul" :description="'Rata-rata keseluruhan: '.number_format($rataKeseluruhan, 1)" />
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $tryout->mulai_at->format('d M Y H:i') }} – {{ $tryout->selesai_at->format('d M Y H:i') }} · {{ $tryout->durasi_menit }} menit</p>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ route('guru.tryout.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-500">← Kembali</a>
            <a href="{{ route('guru.tryout.edit', $tryout) }}" class="rounded-2xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/40 px-4 py-2 text-xs font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-100">Edit tryout</a>
            <a href="{{ route('guru.tryout.create') }}" class="rounded-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-500">Buat tryout baru</a>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Hasil per siswa" description="Peserta yang sudah mengumpulkan jawaban." />
        <div class="mt-6 overflow-x-auto rounded-3xl border border-slate-200 dark:border-slate-700">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-4 text-left">Siswa</th>
                        <th class="px-5 py-4 text-left">Rata skor</th>
                        <th class="px-5 py-4 text-left">Dikumpulkan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($tryout->details as $detail)
                        <tr>
                            <td class="px-5 py-4 font-semibold">{{ $detail->student->user?->name ?? $detail->student->name }}</td>
                            <td class="px-5 py-4">{{ number_format($detail->rata_skor ?? 0, 1) }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $detail->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-8"><x-empty-state title="Belum ada peserta" description="Hasil muncul setelah siswa mengumpulkan tryout." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
