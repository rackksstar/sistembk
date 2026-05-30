@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title
            title="Penilaian Layanan BK"
            description="Nilai konseling yang sudah selesai untuk membantu peningkatan layanan."
        />
        <x-alert class="mt-5" type="success" :message="session('success')" />
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Konseling selesai" description="Satu penilaian per sesi konseling." />

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">No</th>
                            <th class="px-5 py-4">Tanggal</th>
                            <th class="px-5 py-4">Topik</th>
                            <th class="px-5 py-4">Status Penilaian</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($konseling as $item)
                            <tr>
                                <td class="px-5 py-4 text-slate-600">{{ $konseling->firstItem() + $loop->index }}</td>
                                <td class="px-5 py-4 text-slate-600">
                                    @if($item->scheduled_at)
                                        {{ $item->scheduled_at->format('d M Y') }}
                                    @elseif($item->consultation_date)
                                        {{ $item->consultation_date->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ $item->subject }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $item->counselor?->name ?? 'Guru BK' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if($item->penilaianPelayanan)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Sudah Dinilai</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Belum Dinilai</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($item->penilaianPelayanan)
                                        <span class="text-xs text-slate-500">Predikat: {{ $item->penilaianPelayanan->predikat }}</span>
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
