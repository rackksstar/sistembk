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
                <a href="{{ route('admin.students.index') }}" class="rounded-2xl border border-white/80 dark:border-slate-700/80 bg-white/80 dark:bg-slate-900/80 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 shadow-sm transition hover:text-blue-700 dark:text-blue-300">Kelola Siswa</a>
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
        <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
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

        <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
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

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
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
        <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <x-section-title title="Ringkasan layanan BK (Core)" description="Metrik modul tim inti." />
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                @foreach($coreSummary as $item)
                    <a href="{{ $item['href'] }}" class="rounded-2xl border border-blue-100 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/40 p-4 transition hover:bg-blue-50 dark:hover:bg-blue-950/40">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $item['value'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if(isset($postinganTerbaru) && $postinganTerbaru->isNotEmpty())
        <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
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
