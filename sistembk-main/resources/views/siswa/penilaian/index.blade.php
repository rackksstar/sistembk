@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @php($konselingBelumDinilai = $konseling->first(fn ($item) => ! $item->penilaianPelayanan))

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title
                title="Penilaian Layanan BK"
                description="Nilai konseling yang sudah selesai untuk membantu peningkatan layanan."
            />
            @if($konselingBelumDinilai)
                <a
                    href="{{ route('siswa.penilaian.create', ['consultation' => $konselingBelumDinilai->id]) }}"
                    class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
                >
                    Nilai konseling terbaru
                </a>
            @endif
        </div>
        <x-alert class="mt-5" type="success" :message="session('success')" />
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Konseling selesai" description="Satu penilaian per sesi konseling." />

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">No</th>
                            <th class="px-5 py-4">Tanggal</th>
                            <th class="px-5 py-4">Topik</th>
                            <th class="px-5 py-4">Status Penilaian</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($konseling as $item)
                            <tr>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $konseling->firstItem() + $loop->index }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                    @if($item->scheduled_at)
                                        {{ $item->scheduled_at->format('d M Y') }}
                                    @elseif($item->consultation_date)
                                        {{ $item->consultation_date->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $item->subject }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $item->counselor?->name ?? 'Guru BK' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if($item->penilaianPelayanan)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">Sudah Dinilai</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-400">Belum Dinilai</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($item->penilaianPelayanan)
                                        <span class="text-xs text-slate-500 dark:text-slate-400">Predikat: {{ $item->penilaianPelayanan->predikat }}</span>
                                    @else
                                        <a
                                            href="{{ route('siswa.penilaian.create', ['consultation' => $item->id]) }}"
                                            class="inline-flex rounded-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-500"
                                        >
                                            Nilai Sekarang
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8">
                                    <x-empty-state
                                        title="Belum ada konseling selesai"
                                        description="Penilaian akan tersedia setelah sesi konseling Anda ditandai selesai oleh Guru BK."
                                    />
                                    <div class="mt-4 text-center">
                                        <a href="{{ route('siswa.consultations.index') }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Lihat pengajuan konseling</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-5">{{ $konseling->links() }}</div>
    </section>
</div>
@endsection
