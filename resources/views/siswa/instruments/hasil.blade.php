@extends('layouts.app')

@section('content')
@php
    $topCodes = collect($topCategories)->pluck('kode')->filter()->values();
    $tiedNames = collect($result->rankedCategories)->take(2)->pluck('nama')->filter()->values();
@endphp
<div class="space-y-6">
    <x-alert type="success" :message="session('success')" />

    @if($result->isInconclusive())
        <section class="rounded-3xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/90 dark:bg-amber-950/30 p-5 text-sm leading-6 text-amber-950 dark:text-amber-100">
            <p class="font-semibold">Hasil belum bisa dibaca sebagai minat dominan.</p>
            <p class="mt-2">
                Semua kategori skor <strong>0%</strong> (semua jawaban setara “Sangat tidak tertarik”).
                Kode Minat di bawah hanya urutan default, bukan kecenderungan nyata.
                Ulangi asesmen dan pilih tingkat ketertarikan yang lebih akurat pada tiap soal.
            </p>
        </section>
    @endif

    {{-- 1. Kartu utama --}}
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-300">Hasil Asesmen Minat Bakat</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-4xl">
                    Kode Minat: {{ $submission->kode_minat ?: $result->kode_minat }}
                </h1>
                <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400">
                    Tiga minat dominan kamu: {{ $topCodes->implode(', ') }}.
                </p>
                @if(! $result->isInconclusive() && $result->is_tied && $tiedNames->count() >= 2)
                    <p class="mt-2 text-sm font-medium text-amber-700 dark:text-amber-300">
                        Minat utamamu seimbang: {{ $tiedNames[0] }} dan {{ $tiedNames[1] }}.
                    </p>
                @endif
            </div>
            @if($submission->jenjang)
                <span class="rounded-full bg-slate-900 px-4 py-2 text-xs font-bold uppercase tracking-[0.16em] text-white dark:bg-slate-100 dark:text-slate-900">
                    {{ $submission->jenjang }}
                </span>
            @endif
        </div>
        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
            Dikirim {{ $submission->submitted_at?->format('d M Y H:i') }}
        </p>
    </section>

    {{-- 2. Tiga kartu minat teratas --}}
    <section class="grid gap-4 md:grid-cols-3">
        @foreach($topCategories as $category)
            <article class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-3xl font-bold text-blue-700 dark:text-blue-300">{{ $category['kode'] }}</p>
                        <h2 class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">{{ $category['nama'] }}</h2>
                    </div>
                    <span class="rounded-full bg-blue-50 dark:bg-blue-950/40 px-3 py-1 text-xs font-bold text-blue-700 dark:text-blue-300">
                        {{ number_format((float) $category['persen'], 1) }}%
                    </span>
                </div>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                    {{ \Illuminate\Support\Str::limit($category['deskripsi'] ?? 'Tidak ada deskripsi.', 140) }}
                </p>
            </article>
        @endforeach
    </section>

    {{-- 3. Distribusi skor --}}
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-950 dark:text-white">Distribusi skor minat</h2>
        <div class="mt-5 space-y-4">
            @foreach($result->rankedCategories as $category)
                <div>
                    <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                        <span class="font-semibold text-slate-800 dark:text-slate-200">
                            {{ $category['kode'] }} · {{ $category['nama'] }}
                        </span>
                        <span class="font-bold text-slate-600 dark:text-slate-400">{{ number_format((float) $category['persen'], 1) }}%</span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-blue-600" style="width: {{ min(100, max(0, (float) $category['persen'])) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 4. Rekomendasi --}}
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        @php $hasilJenjang = strtoupper((string) $submission->jenjang); @endphp
        @if($hasilJenjang === 'SMK')
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">Rekomendasi Karier</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Bidang karier yang cocok dengan kode minatmu.</p>

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

    {{-- 5. Disclaimer --}}
    <section class="rounded-3xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/80 dark:bg-amber-950/20 p-5 text-sm leading-6 text-amber-900 dark:text-amber-200">
        Hasil ini gambaran kecenderungan minat, bukan keputusan akhir. Diskusikan dengan Guru BK.
    </section>

    {{-- 6. Tombol aksi --}}
    <section class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
        <a href="{{ route('siswa.minat-bakat.index') }}" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700">
            Ulangi asesmen
        </a>
        <a href="{{ route('siswa.instruments.hasil.pdf', $submission) }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-6 py-3 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60">
            Unduh laporan
        </a>
        <a href="{{ route('siswa.dashboard') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 px-6 py-3 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60">
            Kembali ke dashboard
        </a>
    </section>
</div>
@endsection
