@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title
            title="Angket BK"
            description="Isi angket bimbingan konseling dari daftar pertanyaan aktif."
        />
        <x-alert class="mt-5" type="success" :message="session('success')" />

        @if($totalSoal > 0)
            <div class="mt-6 rounded-2xl border border-blue-100 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/30 p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-700 dark:text-blue-300">Progress pengisian</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ $totalDijawab }} dari {{ $totalSoal }} soal dijawab
                        </p>
                    </div>
                    <a
                        href="{{ route('siswa.angket.show') }}"
                        class="inline-flex w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
                    >
                        {{ $sudahSelesai ? 'Lihat Jawaban' : 'Isi Angket' }}
                    </a>
                </div>

                @php($persen = $totalSoal > 0 ? round(($totalDijawab / $totalSoal) * 100) : 0)
                <div class="mt-4 h-3 overflow-hidden rounded-full bg-blue-100 dark:bg-blue-950/50">
                    <div class="h-full rounded-full bg-blue-600 transition-all" style="width: {{ $persen }}%"></div>
                </div>
                <p class="mt-2 text-xs text-slate-600 dark:text-slate-400">{{ $persen }}% selesai</p>
            </div>

            <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-5 py-4">No</th>
                                <th class="px-5 py-4">Pertanyaan</th>
                                <th class="px-5 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                            @foreach($pertanyaan as $index => $item)
                                <tr>
                                    <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $index + 1 }}</td>
                                    <td class="px-5 py-4 font-medium text-slate-900 dark:text-slate-100">{{ $item->teks_pertanyaan }}</td>
                                    <td class="px-5 py-4">
                                        @if($item->sudah_dijawab)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">Sudah dijawab</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-400">Belum dijawab</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="mt-6">
                <x-empty-state title="Belum ada soal angket aktif" description="Guru BK atau admin belum menyiapkan pertanyaan angket." />
            </div>
        @endif
    </section>
</div>
@endsection
