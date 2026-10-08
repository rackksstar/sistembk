@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Peta Sosiometri" description="Ringkasan pilihan teman untuk melihat siswa populer dan siswa yang belum mendapat pilihan." />
    </section>

    <section class="grid gap-5 md:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <x-section-title title="Siswa Populer" description="Urutan berdasarkan jumlah dipilih." />
            <div class="mt-5 space-y-3">
                @forelse($popular as $student)
                    <div class="flex items-center justify-between rounded-2xl bg-blue-50 dark:bg-blue-950/40 p-4">
                        <span class="font-semibold text-slate-950 dark:text-white">{{ $student->name }}</span>
                        <span class="rounded-full bg-blue-600 px-3 py-1 text-sm font-bold text-white">{{ $student->received_sociometry_choices_count }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada pilihan masuk.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <x-section-title title="Siswa Terisolasi" description="Siswa yang belum dipilih oleh teman lain." />
            <div class="mt-5 flex flex-wrap gap-2">
                @forelse($isolated as $student)
                    <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $student->name }}</span>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">Semua siswa sudah mendapat minimal satu pilihan.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm">
        <div class="border-b border-slate-100 dark:border-slate-800 p-6">
            <x-section-title title="Relasi Sosiometri" description="Daftar hubungan yang dikirim siswa." />
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-4">Pemilih</th>
                        <th class="px-5 py-4">Dipilih</th>
                        <th class="px-5 py-4">Relasi</th>
                        <th class="px-5 py-4">Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($responses as $response)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $response->student?->name }}</td>
                            <td class="px-5 py-4 text-slate-700 dark:text-slate-300">{{ $response->chosenStudent?->name }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ \App\Models\SociometryResponse::TYPES[$response->relation_type] ?? $response->relation_type }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $response->reason ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8"><x-empty-state title="Belum ada relasi" description="Data akan muncul setelah siswa mengisi sosiometri." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
