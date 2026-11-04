@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="ui-hero">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="ui-hero-badge">Admin Panel</p>
                <h1 class="mt-5 text-3xl font-semibold tracking-tight text-slate-900 dark:text-slate-100 sm:text-4xl">Pusat kendali Sistem BK sekolah.</h1>
                <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400">Admin dapat mengatur semua role, termasuk siswa, serta memantau approval, kelas, konseling, laporan, dan informasi karier.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <a href="{{ route('admin.users.index') }}" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Kelola Semua Role</a>
                <a href="{{ route('admin.students.index') }}" class="rounded-2xl border border-white/80 dark:border-slate-700/80 bg-white/80 dark:bg-slate-900/80 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 shadow-xs transition hover:text-blue-700 dark:text-blue-300">Kelola Siswa</a>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($metrics as $metric)
            <x-dashboard-card
                :title="$metric['title']"
                :description="$metric['description']"
                :value="$metric['value']"
                :color="$metric['color']"
            />
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
        <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
            <x-section-title title="Ringkasan Role" description="Jumlah akun berdasarkan role aktif di sistem." />
            <div class="mt-6 space-y-4">
                @foreach($roleSummary as $item)
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 dark:bg-slate-800/60 px-4 py-4">
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full {{ $item['color'] }}"></span>
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $item['label'] }}</p>
                        </div>
                        <p class="text-lg font-semibold text-slate-950 dark:text-white">{{ $item['count'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
            <x-section-title title="Modul Admin" description="Semua fitur utama sesuai kebutuhan sistem BK." />
            <div class="mt-6 grid gap-3 md:grid-cols-2">
                @foreach($modules as $module)
                    <a href="{{ $module['href'] }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 p-4 transition hover:border-blue-200 dark:hover:border-blue-800 hover:bg-blue-50 dark:hover:bg-blue-950/40">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-950 dark:text-white">{{ $module['title'] }}</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $module['description'] }}</p>
                            </div>
                            <span class="rounded-full bg-white dark:bg-slate-900 px-3 py-1 text-xs font-semibold text-blue-700 dark:text-blue-300 ring-1 ring-blue-100 dark:ring-blue-900/50">{{ $module['count'] }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <x-section-title
                title="Aktivitas Konseling Terbaru"
                description="Pengajuan, jadwal, dan laporan konseling terbaru dari siswa dan Guru BK."
            />
            <a href="{{ route('admin.consultations.index') }}" class="rounded-full bg-blue-50 dark:bg-blue-950/40 px-4 py-2 text-sm font-semibold text-blue-700 dark:text-blue-300 transition hover:bg-blue-100">Lihat semua</a>
        </div>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            @forelse($recentRequests as $request)
                <article class="border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 last:border-b-0">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $request->subject }}</p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $request->student?->name }} · {{ $request->student?->studentProfile?->kelas?->nama ?? '—' }} · Guru BK: {{ $request->counselor?->name ?? 'Belum dipilih' }}</p>
                        </div>
                        <x-status-badge :status="$request->status" />
                    </div>
                </article>
            @empty
                <x-empty-state title="Belum ada permintaan" description="Saat siswa mengirim permintaan konseling, data terbaru akan muncul di sini." />
            @endforelse
        </div>
    </section>

    @if(isset($coreSummary))
        <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
            <x-section-title title="Ringkasan layanan BK (Core)" description="Metrik modul tim inti." />
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($coreSummary as $item)
                    <a href="{{ $item['href'] }}" class="group rounded-2xl border border-blue-100 dark:border-blue-900/50 bg-blue-50 p-4 transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:bg-blue-950/40 dark:hover:border-blue-700 dark:hover:bg-blue-950/60 dark:focus-visible:ring-offset-slate-900">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $item['value'] }}</p>
                        <span class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-blue-600 opacity-0 transition group-hover:opacity-100 group-focus-visible:opacity-100 dark:text-blue-300">Kelola <span aria-hidden="true">&rarr;</span></span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if(isset($sekolahStats))
        <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-section-title title="Sekolah aktif" description="Pantau sekolah MOU, paket aktivasi, dan status aktif." />
                <a href="{{ route('admin.sekolah.index') }}" class="ui-btn-secondary">Kelola sekolah</a>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                @foreach([
                    ['label' => 'Total sekolah', 'value' => $sekolahStats['total'], 'tone' => 'text-slate-900 dark:text-slate-100'],
                    ['label' => 'Aktif', 'value' => $sekolahStats['aktif'], 'tone' => 'text-emerald-600 dark:text-emerald-400'],
                    ['label' => 'Nonaktif', 'value' => $sekolahStats['nonaktif'], 'tone' => 'text-slate-500 dark:text-slate-400'],
                    ['label' => 'Sudah MOU', 'value' => $sekolahStats['mou'], 'tone' => 'text-blue-600 dark:text-blue-400'],
                    ['label' => 'Paket aktif', 'value' => $sekolahStats['paket'], 'tone' => 'text-violet-600 dark:text-violet-400'],
                ] as $stat)
                    <div class="ui-card-muted px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold {{ $stat['tone'] }}">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>

            @if($sekolahTerbaru->isNotEmpty())
                <ul class="mt-5 divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($sekolahTerbaru as $sekolah)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900 dark:text-slate-100">{{ $sekolah->nama }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">NPSN {{ $sekolah->npsn ?: '—' }}@if($sekolah->paket_aktif) · Paket {{ $sekolah->paket_aktif }}@endif</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                @if($sekolah->is_mou)
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">MOU</span>
                                @endif
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $sekolah->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ $sekolah->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="mt-5">
                    <x-empty-state title="Belum ada sekolah" description="Tambahkan sekolah MOU untuk mulai memantau aktivitas." />
                </div>
            @endif
        </section>
    @endif

    @if(isset($penggunaanPerSekolah) && $penggunaanPerSekolah->isNotEmpty())
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-section-title
                    title="Penggunaan per sekolah"
                    description="Verifikasi uji sistem di minimal 3 sekolah (siswa, guru BK, konseling, konsultasi prodi)."
                />
                <a href="{{ route('admin.consultations.index') }}" class="ui-btn-secondary">Filter konseling per sekolah</a>
            </div>

            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-100 dark:border-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Sekolah</th>
                                <th class="px-4 py-3">Siswa</th>
                                <th class="px-4 py-3">Guru BK</th>
                                <th class="px-4 py-3">Konseling</th>
                                <th class="px-4 py-3">Konsultasi Prodi</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($penggunaanPerSekolah as $row)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $row['nama'] }}
                                        @if($row['is_mou'])
                                            <span class="ml-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">MOU</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $row['siswa'] }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $row['guru'] }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $row['konseling'] }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $row['konsultasi_prodi'] }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a
                                            href="{{ route('admin.consultations.index', ['sekolah_id' => $row['id']]) }}"
                                            class="text-xs font-semibold text-blue-600 hover:underline dark:text-blue-300"
                                        >
                                            Lihat →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif

    @if(isset($postinganTerbaru) && $postinganTerbaru->isNotEmpty())
        <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
            <div class="flex items-end justify-between gap-4">
                <x-section-title title="Postingan terbaru" description="Artikel BK yang baru disimpan." />
                <a href="{{ route('admin.postingan.index') }}" class="text-sm font-semibold text-blue-600">Kelola postingan</a>
            </div>
            <ul class="mt-4 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                @foreach($postinganTerbaru as $artikel)
                    <li class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 px-4 py-3">
                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $artikel->judul }}</span>
                        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $artikel->kategori?->name }} · {{ $artikel->statusLabel() }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
@endsection
