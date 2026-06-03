@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title
            title="Laporan Penilaian Layanan"
            description="Ringkasan penilaian siswa untuk konseling yang sudah selesai."
        />

        <form method="GET" class="mt-6 flex flex-wrap items-end gap-3">
            <div class="space-y-2">
                <x-input-label for="bulan" value="Bulan" />
                <select id="bulan" name="bulan" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected((int) $bulan === $m)>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <div class="space-y-2">
                <x-input-label for="tahun" value="Tahun" />
                <select id="tahun" name="tahun" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
                    @for($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" @selected((int) $tahun === $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Terapkan filter</button>
        </form>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-dashboard-card title="Rata-rata Materi" :value="number_format($summary['rata_materi'], 1)" description="Skor 1–5" />
        <x-dashboard-card title="Rata-rata Cara" :value="number_format($summary['rata_cara'], 1)" description="Skor 1–5" />
        <x-dashboard-card title="Rata-rata Manfaat" :value="number_format($summary['rata_manfaat'], 1)" description="Skor 1–5" />
        <x-dashboard-card title="Overall" :value="number_format($summary['rata_overall'], 1)" :description="$summary['total_dinilai'].' / '.$summary['total_konseling'].' dinilai'" />
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Detail penilaian" description="Per konseling pada periode filter." />

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Tanggal</th>
                            <th class="px-5 py-4">Nama Siswa</th>
                            <th class="px-5 py-4">Kelas</th>
                            <th class="px-5 py-4">Skor Materi</th>
                            <th class="px-5 py-4">Skor Cara</th>
                            <th class="px-5 py-4">Skor Manfaat</th>
                            <th class="px-5 py-4">Rata-rata</th>
                            <th class="px-5 py-4">Predikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($konseling as $item)
                            @php $penilaian = $item->penilaianPelayanan; @endphp
                            <tr>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $item->scheduled_at?->format('d M Y') ?? '—' }}
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $item->student?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $item->student?->studentProfile?->kelas?->nama ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $penilaian?->skor_materi ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $penilaian?->skor_cara ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $penilaian?->skor_manfaat ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $penilaian ? $penilaian->rata_rata : '—' }}</td>
                                <td class="px-5 py-4">
                                    @if($penilaian)
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $penilaian->predikat_class }}">
                                            {{ $penilaian->predikat }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-8">
                                    <x-empty-state
                                        title="Tidak ada data"
                                        description="Belum ada konseling selesai pada bulan dan tahun yang dipilih."
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
