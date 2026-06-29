@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title
            title="Laporan Angket BK"
            description="Progress angket siswa di sekolah Anda atau yang pernah konseling dengan Anda."
        />

        <form method="GET" class="mt-6 flex max-w-md gap-2">
            <input type="search" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama atau NISN..."
                class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
            <button class="shrink-0 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Cari</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Nama Siswa</th>
                            <th class="px-5 py-4">Kelas</th>
                            <th class="px-5 py-4">Dijawab / Total</th>
                            <th class="px-5 py-4">Progress</th>
                            <th class="px-5 py-4">Predikat</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($students as $student)
                            @php
                                $progress = $totalSoalAktif > 0
                                    ? round(($student->total_dijawab / $totalSoalAktif) * 100)
                                    : 0;
                                $predikatClass = match ($student->predikat) {
                                    'Lengkap' => 'bg-emerald-100 text-emerald-800',
                                    'Sebagian' => 'bg-amber-100 text-amber-800 dark:text-amber-300',
                                    'Belum Ada Soal' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                                    default => 'bg-red-100 text-red-800',
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $student->user?->name ?? $student->name }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $student->kelas?->nama ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $student->total_dijawab }} / {{ $totalSoalAktif }}</td>
                                <td class="px-5 py-4">
                                    <div class="min-w-[120px]">
                                        <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                            <div class="h-full rounded-full bg-blue-600" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $progress }}%</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $predikatClass }}">
                                        {{ $student->predikat }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('guru.angket.show', $student) }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Detail</a>
                                        <a href="{{ route('guru.angket.pdf', $student) }}" class="rounded-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-500">Download PDF</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8">
                                    <x-empty-state title="Belum ada siswa dalam cakupan" description="Hubungkan profil Guru BK ke sekolah atau tunggu siswa mengajukan konseling." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $students->links() }}</div>
    </section>
</div>
@endsection
