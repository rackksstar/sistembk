@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title
            title="Laporan Angket BK"
            description="Pantau progress pengisian angket seluruh siswa."
        />

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Nama Siswa</th>
                            <th class="px-5 py-4">Kelas</th>
                            <th class="px-5 py-4">Dijawab / Total</th>
                            <th class="px-5 py-4">Progress</th>
                            <th class="px-5 py-4">Predikat</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($students as $student)
                            @php
                                $progress = $totalSoalAktif > 0
                                    ? round(($student->total_dijawab / $totalSoalAktif) * 100)
                                    : 0;
                                $predikatClass = match ($student->predikat) {
                                    'Lengkap' => 'bg-emerald-100 text-emerald-800',
                                    'Sebagian' => 'bg-amber-100 text-amber-800',
                                    'Belum Ada Soal' => 'bg-slate-100 text-slate-600',
                                    default => 'bg-red-100 text-red-800',
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $student->user?->name ?? $student->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $student->kelas?->nama ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $student->total_dijawab }} / {{ $totalSoalAktif }}</td>
                                <td class="px-5 py-4">
                                    <div class="min-w-[120px]">
                                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-blue-600" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500">{{ $progress }}%</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $predikatClass }}">
                                        {{ $student->predikat }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('guru.angket.show', $student) }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Detail</a>
                                        <a href="{{ route('guru.angket.pdf', $student) }}" class="rounded-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-500">Download PDF</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8">
                                    <x-empty-state title="Belum ada data siswa" description="Data siswa akan muncul setelah terdaftar di sistem." />
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
