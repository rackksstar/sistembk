@extends('layouts.app')

@section('content')
@php
    $studentName = auth()->user()->name;
    $firstName = str($studentName)->before(' ');
    $actionCards = [
        [
            'title' => 'Konseling',
            'description' => 'Ajukan sesi bimbingan dan pantau status permintaan Anda.',
            'href' => route('siswa.consultations.index'),
            'cta' => 'Ajukan Konseling',
            'accent' => 'bg-indigo-600',
        ],
        [
            'title' => 'Penilaian Layanan',
            'description' => 'Nilai konseling yang sudah selesai untuk membantu peningkatan layanan BK.',
            'href' => route('siswa.penilaian.index'),
            'cta' => 'Nilai Layanan',
            'accent' => 'bg-amber-500',
        ],
        [
            'title' => 'Angket BK',
            'description' => 'Isi angket bimbingan konseling dari daftar pertanyaan aktif.',
            'href' => route('siswa.angket.index'),
            'cta' => 'Isi Angket',
            'accent' => 'bg-violet-600',
        ],
        [
            'title' => 'Tryout BK',
            'description' => 'Kerjakan tryout aktif untuk kelasmu dan lihat riwayat skor.',
            'href' => route('siswa.tryout.index'),
            'cta' => 'Kerjakan Tryout',
            'accent' => 'bg-blue-600',
        ],
        [
            'title' => 'Artikel BK',
            'description' => 'Baca informasi dan artikel bimbingan konseling terbaru.',
            'href' => route('siswa.postingan.index'),
            'cta' => 'Baca Artikel',
            'accent' => 'bg-emerald-600',
        ],
        [
            'title' => 'Instrumen Asesmen',
            'description' => 'Isi instrumen minat bakat, gaya belajar, dan masalah sesuai kebutuhan pendampingan.',
            'href' => route('siswa.instruments.index'),
            'cta' => 'Isi Instrumen',
            'accent' => 'bg-slate-600',
        ],
        [
            'title' => 'Profil Siswa SMK',
            'description' => 'Lihat kesiapan kerja, jurusan, dan keahlian untuk rekomendasi lowongan.',
            'href' => '#smk-profile',
            'cta' => 'Cek Profil',
            'accent' => 'bg-teal-600',
        ],
        [
            'title' => 'Kelas Bimbingan',
            'description' => 'Pantau kelas bimbingan yang sudah Anda ikuti dan kode kelas dari sekolah.',
            'href' => '#classes',
            'cta' => 'Cek Kelas',
            'accent' => 'bg-sky-600',
        ],
    ];
@endphp

<div class="space-y-8">
    <section class="ui-hero">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-center">
            <div>
                <p class="ui-hero-badge">Dashboard Siswa</p>
                <h1 class="mt-5 max-w-3xl text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100 sm:text-4xl">
                    Selamat datang, {{ $firstName }}. Ruang BK Anda siap digunakan.
                </h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-400 sm:text-base">
                    Mulai dari instrumen minat bakat, gaya belajar, masalah, sosiometri, permintaan konseling, hingga informasi karier tersedia dalam satu tempat yang aman.
                </p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a href="{{ route('siswa.instruments.index') }}" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">
                        Isi Instrumen Asesmen
                    </a>
                    <a href="{{ route('siswa.consultations.index') }}" class="ui-btn-outline">Ajukan Konseling</a>
                    <a href="{{ route('siswa.angket.index') }}" class="ui-btn-outline">Isi Angket</a>
                    <a href="{{ route('siswa.penilaian.index') }}" class="ui-btn-outline">Nilai Layanan</a>
                    <a href="{{ route('siswa.tryout.index') }}" class="ui-btn-outline">Kerjakan Tryout</a>
                    <a href="{{ route('siswa.postingan.index') }}" class="ui-btn-outline">Baca Artikel</a>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-800/80">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Ringkasan Anda</p>
                <div class="mt-4 grid gap-3">
                    @foreach($metrics as $metric)
                        <div class="ui-hero-stat">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $metric['title'] }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $metric['description'] }}</p>
                                </div>
                                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $metric['color'] }} text-base font-bold text-white shadow-sm">
                                    {{ $metric['value'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section aria-labelledby="student-actions-title">
        <div class="mb-5 flex flex-col gap-2">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600 dark:text-blue-400">Layanan BK (Core)</p>
            <h2 id="student-actions-title" class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Apa yang ingin Anda lakukan?</h2>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($actionCards as $card)
                <x-action-card
                    :title="$card['title']"
                    :description="$card['description']"
                    :href="$card['href']"
                    :cta="$card['cta']"
                    :accent="$card['accent']"
                />
            @endforeach
        </div>
    </section>

    <section id="smk-profile" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <x-section-title
                        title="Profil Siswa SMK"
                        description="Data kesiapan kerja untuk kebutuhan rekomendasi lowongan pekerjaan."
                    />
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                        <x-nav-icon name="briefcase" class="h-4 w-4" />
                        Lowongan
                    </span>
                </div>

                @if($siswaSmk)
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Jurusan</p>
                            <p class="mt-2 font-semibold text-slate-950 dark:text-slate-100">{{ $siswaSmk->jurusan }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Kelas</p>
                            <p class="mt-2 font-semibold text-slate-950 dark:text-slate-100">{{ $siswaSmk->kelas ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Tahun Lulus</p>
                            <p class="mt-2 font-semibold text-slate-950 dark:text-slate-100">{{ $siswaSmk->tahun_lulus ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Status</p>
                            <p class="mt-2 font-semibold capitalize text-slate-950 dark:text-slate-100">{{ str_replace('_', ' ', $siswaSmk->status_kerja) }}</p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Keahlian</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @forelse($siswaSmk->keahlian ?? [] as $skill)
                                <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">{{ $skill }}</span>
                            @empty
                                <span class="text-sm text-slate-500 dark:text-slate-400">Belum ada keahlian yang dicatat.</span>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                        <p class="font-semibold text-slate-950 dark:text-slate-100">Profil SMK belum tersedia</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">Data jurusan, tahun lulus, kontak, dan keahlian bisa ditambahkan oleh admin/guru saat fitur lowongan pekerjaan mulai diaktifkan.</p>
                    </div>
                @endif
            </div>

            <div class="border-t border-slate-100 bg-[linear-gradient(135deg,#eff6ff_0%,#ecfdf5_100%)] p-6 dark:border-slate-700 dark:bg-none dark:bg-slate-800/70 lg:border-l lg:border-t-0">
                <div class="flex h-full flex-col justify-between gap-6">
                    <div>
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm dark:bg-slate-900 dark:text-emerald-300">
                            <x-nav-icon name="graduation-cap" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-5 text-xl font-bold text-slate-950 dark:text-slate-100">{{ $siswaSmk?->sekolah ?? 'Data SMK' }}</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">Profil ini menjadi dasar pencocokan siswa dengan informasi karier dan lowongan kerja sesuai kompetensi.</p>
                    </div>
                    @if($siswaSmk)
                        <div class="rounded-2xl bg-white/80 p-4 text-sm shadow-sm dark:bg-slate-900/80">
                            <p class="font-semibold text-slate-950 dark:text-slate-100">{{ $siswaSmk->siap_dihubungi ? 'Siap dihubungi' : 'Belum siap dihubungi' }}</p>
                            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $siswaSmk->nomor_hp ?? $siswaSmk->email ?? 'Kontak belum dicatat.' }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section id="classes" class="ui-panel">
            <x-section-title
                title="Kelas Bimbingan Saya"
                description="Gunakan kode kelas dari Guru BK untuk bergabung."
            />
            <div class="mt-6 grid gap-4">
                @forelse($studentProfile?->guidanceClasses ?? [] as $class)
                    <div class="ui-surface-blue p-5">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $class->name }}</p>
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300">Kode: {{ $class->code }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $class->description ?? 'Tidak ada deskripsi.' }}</p>
                    </div>
                @empty
                    <x-empty-state title="Belum masuk kelas" description="Masukkan kode kelas di bawah untuk bergabung." />
                @endforelse

                <form action="{{ route('siswa.classes.join') }}" method="POST" class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-700">
                    @csrf
                    <div class="flex gap-2">
                        <x-text-input name="code" required class="flex-1 uppercase" placeholder="KODE-KELAS" />
                        <x-primary-button>Gabung</x-primary-button>
                    </div>
                </form>
            </div>
        </section>

        <section id="request-form" class="ui-panel">
            <x-section-title
                title="Permintaan Konseling"
                description="Ajukan sesi baru atau lihat riwayat lengkap."
            />
            <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-400">Form memuat topik, kategori masalah, dan preferensi waktu.</p>
            <a href="{{ route('siswa.consultations.index') }}" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-500">
                Buka halaman konseling
            </a>
        </section>
    </div>

    <section class="ui-panel">
        <div class="flex items-end justify-between gap-4">
            <x-section-title title="Riwayat Pengajuan" description="Ringkasan permintaan konseling terbaru." />
            <a href="{{ route('siswa.consultations.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">Lihat semua</a>
        </div>
        <div class="mt-6 space-y-3">
            @forelse($requests->take(5) as $request)
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                    <div>
                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ $request->subject }}</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Guru: {{ $request->counselor?->name ?? 'Menunggu' }}</p>
                    </div>
                    <x-status-badge :status="$request->status" />
                </div>
            @empty
                <p class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada riwayat pengajuan.</p>
            @endforelse
        </div>
    </section>

    @if(isset($upcoming) && $upcoming->isNotEmpty())
        <section class="ui-panel">
            <div class="flex items-end justify-between gap-4">
                <x-section-title title="Jadwal mendatang" description="Sesi konseling yang sudah dikonfirmasi Guru BK." />
                <a href="{{ route('siswa.consultations.index') }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400">Detail</a>
            </div>
            <ul class="mt-4 space-y-2">
                @foreach($upcoming as $item)
                    <li class="ui-surface-blue flex flex-col gap-1 px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <span class="font-medium text-slate-900 dark:text-slate-100">{{ $item->subject }}</span>
                            <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $item->counselor?->name ?? 'Guru BK' }}</span>
                        </div>
                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">{{ $item->consultation_date?->format('d M Y') }} · {{ $item->consultation_time ?? '-' }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if(isset($tryoutAktif) && $tryoutAktif->isNotEmpty())
        <section class="ui-panel">
            <div class="flex items-end justify-between gap-4">
                <x-section-title title="Tryout aktif" description="Kerjakan sebelum batas waktu berakhir." />
                <a href="{{ route('siswa.tryout.index') }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400">Lihat semua</a>
            </div>
            <ul class="mt-4 space-y-2">
                @foreach($tryoutAktif as $tryout)
                    <li class="ui-surface-blue flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $tryout->judul }}</span>
                        <a href="{{ route('siswa.tryout.show', $tryout) }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400">Kerjakan</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if(isset($postinganTerbaru) && $postinganTerbaru->isNotEmpty())
        <section class="ui-panel">
            <div class="flex items-end justify-between gap-4">
                <x-section-title title="Artikel terbaru" description="Informasi BK yang baru dipublikasikan." />
                <a href="{{ route('siswa.postingan.index') }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400">Lihat semua</a>
            </div>
            <ul class="mt-4 space-y-2">
                @foreach($postinganTerbaru as $artikel)
                    <li class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-800/60">
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $artikel->judul }}</span>
                        <a href="{{ route('siswa.postingan.show', $artikel) }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400">Baca</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
@endsection
