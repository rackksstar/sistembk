@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title
            title="Rapor BK"
            description="Siswa di sekolah Anda atau yang pernah konseling / memiliki rapor dengan Anda."
        />

        <form method="GET" class="mt-6 flex flex-wrap items-end gap-3">
            <div class="space-y-2">
                <x-input-label for="semester" value="Semester" />
                <select id="semester" name="semester" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm">
                    @foreach(\App\Models\RaporBk::SEMESTERS as $value => $label)
                        <option value="{{ $value }}" @selected($semester === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <x-input-label for="tahun_ajaran" value="Tahun ajaran" />
                <input type="text" id="tahun_ajaran" name="tahun_ajaran" value="{{ $tahunAjaran }}" placeholder="2025/2026"
                    class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm" pattern="\d{4}/\d{4}">
            </div>
            <div class="space-y-2">
                <x-input-label for="search" value="Cari siswa" />
                <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Nama atau NISN"
                    class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm">
            </div>
            <button class="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Terapkan</button>
        </form>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Nama Siswa</th>
                            <th class="px-5 py-4">Kelas</th>
                            <th class="px-5 py-4">Status Rapor</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($students as $student)
                            @php
                                $statusClass = match ($student->rapor_periode?->status) {
                                    \App\Models\RaporBk::STATUS_FINAL => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300',
                                    \App\Models\RaporBk::STATUS_DRAFT => 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
                                    default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $student->user?->name ?? $student->name }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                    {{ $student->kelas?->nama ?? '-' }}
                                    @if($student->kelas?->sekolah)
                                        <span class="block text-xs text-slate-400 dark:text-slate-500">{{ $student->kelas->sekolah->nama }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $student->status_rapor }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('guru.rapor.edit', ['student' => $student, 'semester' => $semester, 'tahun_ajaran' => $tahunAjaran]) }}"
                                            class="rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                            {{ $student->rapor_periode ? 'Edit' : 'Buat' }}
                                        </a>
                                        @if($student->rapor_periode)
                                            <a href="{{ route('guru.rapor.pdf', $student->rapor_periode) }}"
                                                class="rounded-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-500">
                                                PDF
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8">
                                    <x-empty-state title="Belum ada siswa dalam cakupan" description="Pastikan profil Guru BK terhubung ke sekolah, atau ada siswa yang pernah konseling dengan Anda." />
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
