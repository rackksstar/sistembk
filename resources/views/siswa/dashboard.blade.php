@extends('layouts.app')

@section('content')
@php
    $studentName = auth()->user()->name;
    $firstName = str($studentName)->before(' ');
    $jenjang = strtoupper((string) (auth()->user()?->studentProfile?->kelas?->jenjang ?? ''));
    $actionCards = [
        [
            'title' => 'Konseling',
            'description' => 'Ajukan sesi bimbingan dan pantau status permintaan Anda.',
            'href' => route('siswa.consultations.index'),
            'cta' => 'Ajukan Konseling',
            'accent' => 'bg-indigo-600',
        ],
        [
            'title' => 'Chatbot Konseling',
            'description' => 'Mulai percakapan awal dengan asisten BK sebelum membuat pengajuan.',
            'href' => route('siswa.chatbot.index'),
            'cta' => 'Buka Chat',
            'accent' => 'bg-cyan-600',
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
        ...$jenjang === 'SMK' ? [[
            'title' => 'Minat Bakat Kerja',
            'description' => 'Talents Mapping (RIASEC) → Kode Minat Holland + rekomendasi bidang karier untuk jalur kerja.',
            'href' => route('siswa.instruments.index', ['category' => 'minat_kerja']),
            'cta' => 'Isi Minat Kerja',
            'accent' => 'bg-emerald-600',
        ]] : [],
        [
            'title' => 'Minat Bakat Kuliah',
            'description' => 'Talents Mapping yang sama + rekomendasi prodi PCR (SMA) atau bidang karier (SMK).',
            'href' => route('siswa.minat-bakat.index'),
            'cta' => 'Isi Minat Kuliah',
            'accent' => 'bg-blue-600',
        ],
        [
            'title' => 'Konsultasi Prodi',
            'description' => 'Diskusikan pilihan program studi PCR dengan Guru BK setelah asesmen minat bakat.',
            'href' => route('siswa.konsultasi-prodi.index'),
            'cta' => 'Ajukan Konsultasi',
            'accent' => 'bg-indigo-600',
        ],
        [
            'title' => 'Strategi Belajar',
            'description' => 'Asesmen gaya/strategi belajar untuk memahami cara belajar yang cocok.',
            'href' => route('siswa.instruments.index', ['category' => 'gaya_belajar']),
            'cta' => 'Isi Asesmen',
            'accent' => 'bg-sky-600',
        ],
        [
            'title' => 'Kepribadian',
            'description' => 'Isi instrumen kepribadian untuk mengenali karakter dan kecenderungan diri.',
            'href' => route('siswa.instruments.index', ['category' => 'kepribadian']),
            'cta' => 'Isi Kepribadian',
            'accent' => 'bg-fuchsia-600',
        ],
        [
            'title' => 'Angket Masalah',
            'description' => 'Identifikasi area masalah yang perlu dibahas bersama Guru BK.',
            'href' => route('siswa.instruments.index', ['category' => 'angket_masalah']),
            'cta' => 'Isi Angket Masalah',
            'accent' => 'bg-rose-600',
        ],
        [
            'title' => 'Sosiometri',
            'description' => 'Pilih teman sekelas sesuai instrumen sosiometri yang diaktifkan Guru BK.',
            'href' => route('siswa.sociometry.index'),
            'cta' => 'Buka Sosiometri',
            'accent' => 'bg-teal-600',
        ],
        [
            'title' => 'Info Karier',
            'description' => 'Baca informasi karier dan peluang yang dikelola Admin/Guru BK.',
            'href' => route('siswa.careers.index'),
            'cta' => 'Lihat Karier',
            'accent' => 'bg-orange-600',
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
    <x-alert type="error" :message="session('error')" />
    <x-alert type="success" :message="session('success')" />

    @unless($studentProfile)
        <section class="rounded-3xl border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/30 p-6 shadow-xs">
            <x-section-title
                title="Profil siswa belum terhubung"
                description="Akun Anda belum punya data NISN/kelas. Hubungi Guru BK agar modul penilaian, angket, dan tryout bisa dipakai."
            />
        </section>
    @endunless

    <section class="ui-hero">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-center">
            <div>
                <p class="ui-hero-badge">Dashboard Siswa</p>
                <h1 class="mt-5 max-w-3xl text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100 sm:text-4xl">
                    Selamat datang, {{ $firstName }}. Ruang BK Anda siap digunakan.
                </h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-400 sm:text-base">
                    Ajukan konseling, isi angket, kerjakan tryout, dan baca artikel BK dari satu dashboard. Modul asesmen lain tersedia di menu samping.
                </p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a href="{{ route('siswa.consultations.index') }}" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">
                        Ajukan Konseling
                    </a>
                    <a href="{{ route('siswa.chatbot.index') }}" class="ui-btn-outline">Buka Chatbot</a>
                    <a href="{{ route('siswa.angket.index') }}" class="ui-btn-outline">Isi Angket</a>
                    <a href="{{ route('siswa.penilaian.index') }}" class="ui-btn-outline">Nilai Layanan</a>
                    <a href="{{ route('siswa.tryout.index') }}" class="ui-btn-outline">Kerjakan Tryout</a>
                    <a href="{{ route('siswa.postingan.index') }}" class="ui-btn-outline">Baca Artikel</a>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-xs backdrop-blur dark:border-slate-700 dark:bg-slate-800/80">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Ringkasan Anda</p>
                <div class="mt-4 grid gap-3">
                    @foreach($metrics as $metric)
                        <div class="ui-hero-stat">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $metric['title'] }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $metric['description'] }}</p>
                                </div>
                                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-linear-to-br {{ $metric['color'] }} text-base font-bold text-white shadow-xs">
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

    <section id="smk-profile" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xs dark:border-slate-700 dark:bg-slate-900">
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
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-xs dark:bg-slate-900 dark:text-emerald-300">
                            <x-nav-icon name="graduation-cap" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-5 text-xl font-bold text-slate-950 dark:text-slate-100">{{ $siswaSmk?->sekolah ?? 'Data SMK' }}</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">Profil ini menjadi dasar pencocokan siswa dengan informasi karier dan lowongan kerja sesuai kompetensi.</p>
                    </div>
                    @if($siswaSmk)
                        <div class="rounded-2xl bg-white/80 p-4 text-sm shadow-xs dark:bg-slate-900/80">
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
            <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Kelas BK (profil)</p>
                <p class="mt-2 font-semibold text-slate-950 dark:text-slate-100">
                    {{ $studentProfile?->kelas?->nama ?? 'Belum ditetapkan' }}
                </p>
                @if($studentProfile?->kelas?->sekolah)
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $studentProfile->kelas->sekolah->nama }}</p>
                @endif
                @unless($studentProfile?->kelas_id)
                    <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">Hubungi admin/Guru BK agar tryout dan laporan kelas tampil.</p>
                @endunless
            </div>
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

@php
    $floatingChatConfig = [
        'endpoint' => route('siswa.chatbot.store'),
        'csrf' => csrf_token(),
        'floating' => true,
        'open' => false,
        'messages' => [
            [
                'role' => 'assistant',
                'text' => "Hai {$firstName}! Mau cerita soal sekolah, belajar, pertemanan, atau rencana karier? Aku bantu pelan-pelan ya. Boleh juga tanya info PCR/PMB.",
            ],
        ],
        'quickMessages' => [
            'Aku lagi stres belajar',
            'Apa itu bimbingan konseling?',
            'Aku bingung pilih karier',
            'Jalur masuk PCR apa saja?',
            'Prodi apa saja di PCR?',
        ],
    ];
@endphp

<div
    x-data="siswaChatbot(@js($floatingChatConfig))"
    x-on:keydown.escape.window="open = false"
    class="fixed bottom-5 right-5 z-40 sm:bottom-6 sm:right-6"
>
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-3 scale-95"
        class="mb-4 flex h-[min(620px,calc(100vh-7rem))] w-[calc(100vw-2.5rem)] max-w-sm flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/40 sm:w-96"
        role="dialog"
        aria-label="Chatbot Konseling"
    >
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-950 px-4 py-3 text-white dark:border-slate-700">
            <div class="flex min-w-0 items-center gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-500 text-white shadow-xs shadow-blue-500/40">
                    <x-nav-icon name="chat" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold">Teman BK</p>
                    <p class="flex items-center gap-1.5 truncate text-xs text-slate-300">
                        <span class="chat-status-dot" aria-hidden="true"></span>
                        <span x-text="busy ? (sending ? 'Membalas…' : 'Menulis…') : 'Siap bantu ngobrol'"></span>
                    </p>
                </div>
            </div>
            <button
                type="button"
                x-on:click="open = false"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-300 transition hover:bg-white/10 hover:text-white focus:outline-hidden focus:ring-2 focus:ring-blue-300"
                aria-label="Tutup chatbot"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        <div x-ref="chatBody" class="min-h-0 flex-1 space-y-3 overflow-y-auto scroll-smooth bg-slate-50 p-4 dark:bg-slate-950/50">
            <template x-for="item in messages" :key="item.id">
                <div class="chat-bubble-in flex" :class="item.role === 'user' ? 'justify-end' : 'justify-start'">
                    <div
                        class="max-w-[86%] rounded-2xl px-4 py-3 text-sm leading-6 shadow-xs"
                        :class="item.role === 'user'
                            ? 'rounded-br-md bg-blue-600 text-white shadow-blue-500/20'
                            : 'rounded-bl-md border border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200'"
                    >
                        <div class="chat-md break-words" x-html="formatChatHtml(item.text)"></div>
                        <span x-show="item.streaming" x-cloak class="chat-caret" aria-hidden="true"></span>
                    </div>
                </div>
            </template>

            <div x-show="sending" x-cloak class="chat-bubble-in flex justify-start" role="status" aria-live="polite">
                <div class="inline-flex items-center gap-1.5 rounded-2xl rounded-bl-md border border-slate-200 bg-white px-4 py-3 shadow-xs dark:border-slate-700 dark:bg-slate-900">
                    <span class="chat-typing-dot"></span>
                    <span class="chat-typing-dot"></span>
                    <span class="chat-typing-dot"></span>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
            <div class="mb-3 flex gap-2 overflow-x-auto pb-1">
                <template x-for="text in quickMessages" :key="text">
                    <button
                        type="button"
                        x-on:click="useQuickMessage(text)"
                        class="shrink-0 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-blue-300 hover:text-blue-700 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-blue-500 dark:hover:text-blue-300"
                        x-bind:disabled="busy"
                        x-text="text"
                    ></button>
                </template>
            </div>

            <form x-on:submit.prevent="send()" class="flex gap-2">
                <input
                    x-ref="chatInput"
                    x-model="message"
                    type="text"
                    class="ui-input min-w-0 flex-1 rounded-2xl py-3"
                    placeholder="Tulis pesan…"
                    maxlength="2000"
                    autocomplete="off"
                    x-bind:disabled="busy"
                >
                <button
                    type="submit"
                    class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
                    x-bind:disabled="busy || !message.trim()"
                    aria-label="Kirim pesan"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M3.48 20.1 21.2 12 3.48 3.9 3 10.2l10.2 1.8L3 13.8l.48 6.3Z" />
                    </svg>
                </button>
            </form>

            <a href="{{ route('siswa.chatbot.index') }}" class="mt-3 inline-flex w-full items-center justify-center text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                Buka halaman chat penuh
            </a>
        </div>
    </div>

    <button
        type="button"
        x-on:click="toggle"
        class="group relative inline-flex h-16 w-16 items-center justify-center rounded-full bg-blue-600 text-white shadow-2xl shadow-blue-500/30 transition hover:-translate-y-0.5 hover:bg-blue-500 focus:outline-hidden focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900/50"
        aria-label="Buka chatbot konseling"
        x-bind:aria-expanded="open.toString()"
    >
        <span class="absolute -right-0.5 -top-0.5 h-4 w-4 rounded-full border-2 border-white bg-emerald-400 dark:border-slate-900"></span>
        <x-nav-icon name="chat" class="h-7 w-7 transition group-hover:scale-110" />
    </button>
</div>
@endsection
