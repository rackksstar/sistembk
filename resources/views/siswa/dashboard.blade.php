@extends('layouts.app')

@section('content')
@php
    $studentName = auth()->user()->name;
    $firstName = str($studentName)->before(' ');
    $actionCards = [
        [
            'title' => 'Asesmen Mandiri',
            'description' => 'Isi minat bakat dan sosiometri agar Guru BK dapat memahami kebutuhan pendampingan Anda dengan lebih tepat.',
            'href' => route('siswa.assessments.index'),
            'cta' => 'Mulai Isi Sekarang',
            'color' => 'from-blue-50 via-white to-emerald-50',
            'accent' => 'bg-blue-600',
            'featured' => true,
        ],
        [
            'title' => 'Permintaan Konseling',
            'description' => 'Sampaikan hal yang ingin dibahas bersama Guru BK secara aman dan tertata.',
            'href' => '#request-form',
            'cta' => 'Ajukan Konseling',
            'color' => 'from-indigo-50 via-white to-sky-50',
            'accent' => 'bg-indigo-500',
            'featured' => false,
        ],
        [
            'title' => 'Informasi Karier',
            'description' => 'Temukan referensi jurusan, karier, dan rencana belajar sesuai minat Anda.',
            'href' => route('siswa.careers.index'),
            'cta' => 'Lihat Info',
            'color' => 'from-emerald-50 via-white to-teal-50',
            'accent' => 'bg-emerald-500',
            'featured' => false,
        ],
        [
            'title' => 'Kelas Bimbingan',
            'description' => 'Pantau kelas bimbingan yang sudah Anda ikuti dan kode kelas dari sekolah.',
            'href' => '#classes',
            'cta' => 'Cek Kelas',
            'color' => 'from-sky-50 via-white to-violet-50',
            'accent' => 'bg-sky-500',
            'featured' => false,
        ],
    ];
@endphp

<div class="space-y-8">
    {{-- Hero Section --}}
    <section class="overflow-hidden rounded-3xl border border-white bg-[linear-gradient(135deg,#f8fbff_0%,#edf5ff_45%,#dbe7f5_100%)] p-6 shadow-lg shadow-blue-100/60 sm:p-8">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-center">
            <div>
                <p class="inline-flex rounded-full border border-blue-100 bg-white/85 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-blue-600 shadow-sm">
                    Dashboard Siswa
                </p>
                <h1 class="mt-5 max-w-3xl text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">
                    Selamat datang, {{ $firstName }}. Ruang BK Anda siap digunakan.
                </h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Mulai dari asesmen mandiri, permintaan konseling, kelas bimbingan, hingga informasi karier tersedia dalam satu tempat yang aman.
                </p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('siswa.assessments.index') }}" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">
                        Mulai Isi Sekarang
                    </a>
                    <a href="#request-form" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white/85 px-6 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700">
                        Ajukan Konseling
                    </a>
                </div>
            </div>

            <div class="rounded-3xl border border-white/80 bg-white/70 p-5 shadow-md shadow-blue-100/70 backdrop-blur">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Ringkasan Anda</p>
                <div class="mt-4 grid gap-3">
                    @foreach($metrics as $metric)
                        <div class="rounded-2xl border border-white bg-white/85 p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-950">{{ $metric['title'] }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $metric['description'] }}</p>
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

    {{-- Action Cards Section - 4 Columns Layout --}}
    <section aria-labelledby="student-actions-title">
        <div class="flex flex-col gap-2 mb-5">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Pilihan Aksi</p>
            <h2 id="student-actions-title" class="text-2xl font-bold tracking-tight text-slate-950">Apa yang ingin Anda lakukan?</h2>
        </div>

        {{-- Grid diatur menjadi xl:grid-cols-4 untuk 1 baris di desktop --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($actionCards as $card)
                <a href="{{ $card['href'] }}" class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-white/90 bg-gradient-to-br {{ $card['color'] }} p-6 shadow-md shadow-blue-100/60 ring-1 ring-white/80 transition hover:-translate-y-1 hover:shadow-xl">
                    <div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-700 shadow-sm ring-1 ring-slate-100">
                                <span class="h-5 w-5 rounded-full {{ $card['accent'] }}"></span>
                            </span>
                            <span class="h-3 w-3 rounded-full border-2 border-slate-300 bg-white/70"></span>
                        </div>
                        <h3 class="mt-8 text-xl font-bold tracking-tight text-slate-950">{{ $card['title'] }}</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $card['description'] }}</p>
                    </div>

                    <div class="mt-8">
                        <span class="inline-flex items-center rounded-full bg-slate-900/70 px-4 py-2 text-xs font-semibold text-white transition group-hover:bg-blue-600">
                            {{ $card['cta'] }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Classes & Requests Section --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Kelas Bimbingan --}}
        <section id="classes" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title
                title="Kelas Bimbingan Saya"
                description="Gunakan kode kelas dari Guru BK untuk bergabung."
            />
            <div class="mt-6 grid gap-4">
                @forelse($studentProfile?->guidanceClasses ?? [] as $class)
                    <div class="rounded-2xl border border-blue-100 bg-blue-50/50 p-5">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-slate-950">{{ $class->name }}</p>
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Kode: {{ $class->code }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">{{ $class->description ?? 'Tidak ada deskripsi.' }}</p>
                    </div>
                @empty
                    <x-empty-state title="Belum masuk kelas" description="Masukkan kode kelas di bawah untuk bergabung." />
                @endforelse

                <form action="{{ route('siswa.classes.join') }}" method="POST" class="mt-4 pt-4 border-t border-slate-100">
                    @csrf
                    <div class="flex gap-2">
                        <x-text-input name="code" required class="flex-1 uppercase" placeholder="KODE-KELAS" />
                        <x-primary-button>Gabung</x-primary-button>
                    </div>
                </form>
            </div>
        </section>

        {{-- Form Permintaan Konseling --}}
        <section id="request-form" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title
                title="Permintaan Konseling"
                description="Kirim keluhan Anda kepada Guru BK."
            />
            <form action="{{ route('siswa.consultation-requests.store') }}" method="POST" class="mt-6 space-y-4" x-data="{ loading: false }" x-on:submit="loading = true">
                @csrf
                <div class="space-y-2">
                    <x-input-label for="complaint" value="Keluhan" />
                    <textarea id="complaint" name="complaint" rows="3" required class="w-full rounded-2xl border-slate-200 bg-slate-50 text-sm focus:ring-blue-500" placeholder="Apa yang ingin Anda ceritakan?"></textarea>
                </div>
                <div class="space-y-2">
                    <x-input-label for="counselor_id" value="Pilih Guru BK" />
                    <x-form-select id="counselor_id" name="counselor_id" required>
                        <option value="">Pilih guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </x-form-select>
                </div>
                <x-primary-button class="w-full py-3" x-bind:disabled="loading">
                    <span x-show="!loading">Kirim Permintaan</span>
                    <span x-show="loading">Mengirim...</span>
                </x-primary-button>
            </form>
        </section>
    </div>

    {{-- Riwayat Section --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Riwayat Pengajuan" description="Status permintaan konseling Anda." />
        <div class="mt-6 space-y-3">
            @forelse($requests as $request)
                <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div>
                        <p class="font-medium text-slate-900">{{ Str::limit($request->details, 60) }}</p>
                        <p class="text-xs text-slate-500 mt-1">Guru: {{ $request->counselor?->name ?? 'Menunggu' }}</p>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-wider shadow-sm">{{ $request->status }}</span>
                </div>
            @empty
                <p class="text-center py-6 text-sm text-slate-500">Belum ada riwayat pengajuan.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
