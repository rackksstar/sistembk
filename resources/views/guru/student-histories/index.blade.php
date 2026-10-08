@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title
                title="Riwayat Siswa"
                description="Pantau rekam jejak layanan siswa dan status perkembangan kasus secara berkelanjutan."
            />
        </div>

        <form method="GET" action="{{ route('guru.student-histories.index') }}" class="mt-6 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
            <x-form-select name="student_id">
                <option value="">Pilih siswa</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}" @selected($student->id === $selectedStudent?->id)>
                        {{ $student->name }} — {{ $student->classModel?->name ?? 'Kelas belum diisi' }}
                    </option>
                @endforeach
            </x-form-select>
            <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white dark:bg-slate-100 dark:text-slate-900 transition hover:bg-slate-700">Lihat Riwayat</button>
        </form>
    </section>

    @if($selectedStudent)
        <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_280px]">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $selectedStudent->name }}</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Kelas: {{ $selectedStudent->classModel?->name ?? '-' }}</p>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Sekolah: {{ $selectedStudent->schoolModel?->name ?? $selectedStudent->school ?? '-' }}</p>
                </div>
                <div class="grid gap-3">
                    <div class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Layanan Individu</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ $individualHistories->count() }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Laporan Kelompok</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ $groupHistories->count() }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Layanan Individu</h3>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Riwayat konsultasi individu yang ditangani oleh Anda.</p>
                    </div>
                    <span class="rounded-full bg-blue-50 dark:bg-blue-950/40 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-300">{{ $individualHistories->count() }} kasus</span>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse($individualHistories as $history)
                        <article class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $history->subject ?? 'Konsultasi Individu' }}</p>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $history->caseCategoryLabel() }} • {{ $history->consultation_date?->format('d M Y') }}</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-700 dark:text-slate-300">{{ $history->status }}</span>
                            </div>
                            @if($history->notes)
                                <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $history->notes }}</p>
                            @endif
                        </article>
                    @empty
                        <x-empty-state title="Belum ada riwayat individu" description="Siswa ini belum memiliki layanan individu selesai oleh Anda." />
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Laporan Kelompok</h3>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Riwayat laporan konseling kelompok yang melibatkan siswa ini.</p>
                    </div>
                    <span class="rounded-full bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">{{ $groupHistories->count() }} laporan</span>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse($groupHistories as $history)
                        <article class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $history->rpl?->title ?? 'Laporan Kelompok' }}</p>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $history->caseCategoryLabel() }} • {{ $history->service_date?->format('d M Y') }}</p>
                                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Kelas: {{ $history->classRoom?->name ?? '-' }}</p>
                                </div>
                            </div>
                            <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($history->result, 220) }}</p>
                        </article>
                    @empty
                        <x-empty-state title="Belum ada laporan kelompok" description="Siswa ini belum terlibat dalam laporan kelompok yang selesai." />
                    @endforelse
                </div>
            </div>
        </section>
    @else
        <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
            <x-empty-state title="Siswa belum dipilih" description="Pilih siswa untuk melihat riwayat layanan dan perkembangan kasus." />
        </section>
    @endif
</div>
@endsection
