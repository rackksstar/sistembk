@extends('layouts.app')

@php
    $dominantCode = $submission->kode_minat ?: $result->kode_minat;
    $riasecRows = collect($result->rankedCategories)->map(function (array $category) {
        $code = $category['kode'] ?? '';
        return [
            'code' => $code,
            'label' => $category['nama'] ?? (\App\Models\InstrumentQuestion::RIASEC_CODES[$code] ?? $code),
            'percentage' => (float) ($category['persen'] ?? 0),
            'description' => $category['deskripsi'] ?? (config('riasec_results.descriptions.'.$code) ?? ''),
        ];
    });
    $codeClasses = [
        'R' => ['ring' => 'bg-orange-500', 'bar' => 'bg-orange-500', 'chip' => 'bg-orange-100 text-orange-700 dark:bg-orange-950/50 dark:text-orange-300'],
        'I' => ['ring' => 'bg-blue-500', 'bar' => 'bg-blue-500', 'chip' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300'],
        'A' => ['ring' => 'bg-purple-500', 'bar' => 'bg-purple-500', 'chip' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300'],
        'S' => ['ring' => 'bg-emerald-500', 'bar' => 'bg-emerald-500', 'chip' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'],
        'E' => ['ring' => 'bg-amber-500', 'bar' => 'bg-amber-500', 'chip' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300'],
        'C' => ['ring' => 'bg-cyan-500', 'bar' => 'bg-cyan-500', 'chip' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-300'],
    ];
    $tiedNames = collect($result->rankedCategories)->take(2)->pluck('nama')->filter()->values();
    $track = $track ?? ($submission->category === \App\Models\InstrumentQuestion::CATEGORY_MINAT_KERJA ? 'kerja' : 'kuliah');
    $isKerja = $track === 'kerja';
@endphp

@section('content')
<div class="space-y-6">
    <x-alert type="success" :message="session('success')" />

    @if($result->isInconclusive())
        <section class="rounded-3xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/90 dark:bg-amber-950/30 p-5 text-sm leading-6 text-amber-950 dark:text-amber-100">
            <p class="font-semibold">Hasil belum bisa dibaca sebagai minat dominan.</p>
            <p class="mt-2">
                Semua kategori skor <strong>0%</strong>. Kode Minat di bawah hanya urutan default, bukan kecenderungan nyata.
                Ulangi asesmen dan pilih tingkat kesukaan yang lebih akurat pada tiap soal.
            </p>
        </section>
    @endif

    <section class="overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700 shadow-xs">
        <div class="bg-linear-to-r {{ $isKerja ? 'from-emerald-500 to-teal-600' : 'from-indigo-500 to-blue-600' }} px-6 py-5 text-center">
            <h2 class="text-lg font-bold text-white">
                {{ $isKerja ? 'Hasil Minat Bakat Kerja — Talents Mapping' : 'Hasil Minat Bakat Kuliah — Talents Mapping' }}
            </h2>
        </div>
        <div class="bg-white dark:bg-slate-900 px-6 py-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <a
                        href="{{ $isKerja ? route('siswa.instruments.index', ['category' => 'minat_kerja']) : route('siswa.minat-bakat.index') }}"
                        class="text-xs font-semibold {{ $isKerja ? 'text-emerald-700 dark:text-emerald-300' : 'text-blue-600 dark:text-blue-400' }} hover:underline"
                    >
                        &larr; Kembali ke Asesmen Minat Bakat
                    </a>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Dikirim {{ $submission->submitted_at?->format('d M Y H:i') }}
                    </p>
                </div>
                @if($submission->jenjang)
                    <span class="rounded-full bg-slate-900 px-4 py-2 text-xs font-bold uppercase tracking-[0.16em] text-white dark:bg-slate-100 dark:text-slate-900">
                        {{ $submission->jenjang }}
                    </span>
                @endif
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-1">
                    @foreach(str_split((string) $dominantCode) as $letter)
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl text-xl font-bold text-white {{ $codeClasses[$letter]['ring'] ?? 'bg-slate-500' }}">
                            {{ $letter }}
                        </span>
                    @endforeach
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Kode Minat (Holland Code)</p>
                    <p class="text-lg font-bold text-slate-950 dark:text-white">
                        {{ $riasecRows->take(3)->pluck('label')->implode(' – ') }}
                    </p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        Kode Minat: <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $dominantCode }}</span>
                    </p>
                    @if(! $result->isInconclusive() && $result->is_tied && $tiedNames->count() >= 2)
                        <p class="mt-2 text-sm font-medium text-amber-700 dark:text-amber-300">
                            Minat utamamu seimbang: {{ $tiedNames[0] }} dan {{ $tiedNames[1] }}.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <x-section-title title="Rincian per Dimensi RIASEC" description="Semakin tinggi persentase, semakin kuat kecenderungan minatmu pada dimensi tersebut." />

        <div class="mt-6 space-y-5">
            @foreach($riasecRows as $row)
                @php
                    $classes = $codeClasses[$row['code']] ?? ['ring' => 'bg-slate-500', 'bar' => 'bg-slate-500', 'chip' => 'bg-slate-100 text-slate-700'];
                @endphp
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white {{ $classes['ring'] }}">
                                {{ $row['code'] }}
                            </span>
                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['label'] }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes['chip'] }}">
                            {{ number_format($row['percentage'], 1) }}%
                        </span>
                    </div>
                    <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full {{ $classes['bar'] }}" style="width: {{ min(100, max(0, $row['percentage'])) }}%"></div>
                    </div>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $row['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        @php $hasilJenjang = strtoupper((string) $submission->jenjang); @endphp
        @if($isKerja || $hasilJenjang === 'SMK')
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">Rekomendasi Karier</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $isKerja ? 'Bidang karier yang cocok untuk jalur kerja berdasarkan kode minatmu.' : 'Bidang karier yang cocok dengan kode minatmu.' }}
            </p>

            @if(($recommendations['items'] ?? []) === [])
                <p class="mt-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 text-sm text-slate-600 dark:text-slate-400">
                    {{ $recommendations['empty_message'] ?? 'Belum ada rekomendasi.' }}
                </p>
            @else
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach($recommendations['items'] as $item)
                        <article class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-slate-950 dark:text-white">{{ $item['nama'] }}</h3>
                                <span class="rounded-full bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 text-[11px] font-bold text-emerald-700 dark:text-emerald-300">
                                    Job Zone {{ $item['job_zone'] }}
                                </span>
                                <span class="rounded-full bg-blue-50 dark:bg-blue-950/40 px-2.5 py-1 text-[11px] font-bold text-blue-700 dark:text-blue-300">
                                    {{ $item['match_label'] }}
                                </span>
                            </div>
                            @if(!empty($item['kode_tag']))
                                <p class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-400">Tag minat: {{ $item['kode_tag'] }}</p>
                            @endif
                            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $item['deskripsi'] }}</p>
                            @if(!empty($item['contoh_pekerjaan']) && is_array($item['contoh_pekerjaan']))
                                <p class="mt-3 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                    Contoh: {{ implode(', ', $item['contoh_pekerjaan']) }}
                                </p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif

            <details class="mt-6 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4 text-sm text-slate-600 dark:text-slate-400">
                <summary class="cursor-pointer font-semibold text-slate-900 dark:text-slate-100">Apa itu Job Zone?</summary>
                <ul class="mt-3 list-disc space-y-1.5 pl-5 leading-6">
                    <li><strong>1</strong> — Persiapan minimal</li>
                    <li><strong>2</strong> — Dasar</li>
                    <li><strong>3</strong> — Menengah / vokasi atau D3</li>
                    <li><strong>4</strong> — Tinggi / setara S1</li>
                    <li><strong>5</strong> — Sangat tinggi</li>
                </ul>
            </details>
        @elseif($hasilJenjang === 'SMA')
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">Program Studi di Politeknik Caltex Riau</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Rekomendasi prodi PCR berdasarkan minat dominanmu.</p>

            @if(($recommendations['items'] ?? []) === [])
                <p class="mt-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 text-sm text-slate-600 dark:text-slate-400">
                    {{ $recommendations['empty_message'] ?? 'Belum ada rekomendasi.' }}
                </p>
            @else
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach($recommendations['items'] as $item)
                        <article class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-slate-950 dark:text-white">{{ $item['nama'] }}</h3>
                                <span class="rounded-full bg-slate-900 px-2.5 py-1 text-[11px] font-bold text-white dark:bg-slate-100 dark:text-slate-900">
                                    {{ $item['jenjang_pendidikan'] }}
                                </span>
                                <span class="rounded-full bg-blue-50 dark:bg-blue-950/40 px-2.5 py-1 text-[11px] font-bold text-blue-700 dark:text-blue-300">
                                    {{ $item['match_label'] }}
                                </span>
                            </div>
                            @if(!empty($item['kode_tag']))
                                <p class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-400">Tag minat: {{ $item['kode_tag'] }}</p>
                            @endif
                            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($item['deskripsi'] ?? '', 160) }}</p>
                            @if(!empty($item['website_url']))
                                <a href="{{ $item['website_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300">
                                    Lihat di situs PCR
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        @else
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">Rekomendasi</h2>
            <p class="mt-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 text-sm text-slate-600 dark:text-slate-400">
                {{ $recommendations['empty_message'] ?? 'Jenjang asesmen tidak diketahui, sehingga rekomendasi tidak dapat ditampilkan.' }}
            </p>
        @endif
    </section>

    <section class="rounded-3xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/80 dark:bg-amber-950/20 p-5 text-sm leading-6 text-amber-900 dark:text-amber-200">
        Hasil ini gambaran kecenderungan minat (Talents Mapping / RIASEC), bukan keputusan akhir. Diskusikan dengan Guru BK.
    </section>

    <section class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
        <a
            href="{{ $isKerja ? route('siswa.instruments.index', ['category' => 'minat_kerja']) : route('siswa.minat-bakat.index') }}"
            class="inline-flex items-center justify-center rounded-full {{ $isKerja ? 'bg-emerald-600 shadow-emerald-500/20 hover:bg-emerald-500' : 'bg-blue-600 shadow-blue-500/20 hover:bg-blue-700' }} px-6 py-3 text-sm font-semibold text-white shadow-lg transition"
        >
            Ulangi asesmen
        </a>
        @if(! $isKerja)
            <a href="{{ route('siswa.instruments.hasil.pdf', $submission) }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-6 py-3 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                Unduh laporan
            </a>
        @endif
        <a href="{{ route('siswa.dashboard') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 px-6 py-3 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60">
            Kembali ke dashboard
        </a>
    </section>
</div>
@endsection
