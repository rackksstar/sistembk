@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <x-section-title
                title="Dashboard Siswa"
                description="Masuk kelas bimbingan, ajukan konseling, pantau status, dan lihat riwayat sesi Anda."
            />
            <a href="#request-form" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">Ajukan konseling</a>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($metrics as $metric)
                <x-dashboard-card
                    :title="$metric['title']"
                    :description="$metric['description']"
                    :value="$metric['value']"
                    :color="$metric['color']"
                />
            @endforeach
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_420px]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title
                title="Kelas Bimbingan Saya"
                description="Gunakan kode kelas dari Guru BK atau admin untuk masuk ke kelas."
            />

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                @forelse($studentProfile?->guidanceClasses ?? [] as $class)
                    <div class="rounded-3xl border border-blue-100 bg-blue-50 p-5">
                        <p class="font-semibold text-slate-950">{{ $class->name }}</p>
                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-blue-700">Kode {{ $class->code }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $class->description ?? 'Tidak ada deskripsi.' }}</p>
                    </div>
                @empty
                    <x-empty-state class="sm:col-span-2" title="Belum masuk kelas" description="Masukkan kode kelas pada form di samping untuk bergabung." />
                @endforelse
            </div>
        </div>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title title="Masuk Kelas" description="Isi kode kelas bimbingan yang diberikan sekolah." />
            <x-alert class="mt-5" type="error" :message="session('error')" />
            <form action="{{ route('siswa.classes.join') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-900" for="code">Kode Kelas</label>
                    <input id="code" name="code" value="{{ old('code') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm uppercase tracking-[0.12em] text-slate-900 placeholder:normal-case placeholder:tracking-normal placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Contoh: BK-ABC123" />
                    @error('code')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Masuk kelas</button>
            </form>
        </aside>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_420px]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title
                title="Riwayat Pengajuan"
                description="Semua permintaan konseling yang pernah Anda kirim."
            />

            <div class="mt-6 space-y-4">
                @forelse($requests as $request)
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $request->subject }}</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Keluhan: {{ $request->details }}</p>
                                <p class="mt-1 text-sm leading-6 text-slate-500">Guru BK: {{ $request->counselor?->name ?? 'Belum ditugaskan' }}</p>
                            </div>
                            <span class="w-fit rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-700">{{ $request->status }}</span>
                        </div>
                    </article>
                @empty
                    <x-empty-state title="Belum ada pengajuan" description="Kirim permintaan pertama Anda melalui form di samping." />
                @endforelse
            </div>
        </section>

        <aside id="request-form" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title
                title="Permintaan Konseling"
                description="Tuliskan keluhan dan pilih Guru BK yang ingin dituju."
            />

            <x-alert class="mt-5" type="success" :message="session('success')" />

            @if($errors->any())
                <x-alert class="mt-5" type="error" message="Periksa kembali form. Ada data yang belum sesuai." />
            @endif

            <form action="{{ route('siswa.consultation-requests.store') }}" method="POST" class="mt-6 space-y-5" x-data="{ loading: false }" x-on:submit="loading = true">
                @csrf

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-900" for="complaint">Keluhan</label>
                    <textarea id="complaint" name="complaint" rows="6" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Ceritakan keluhan atau hal yang ingin dikonsultasikan...">{{ old('complaint') }}</textarea>
                    @error('complaint')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-900" for="counselor_id">Pilih Guru BK</label>
                    <select id="counselor_id" name="counselor_id" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Pilih guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) old('counselor_id') === (string) $teacher->id)>{{ $teacher->name }}{{ $teacher->school ? ' - '.$teacher->school : '' }}</option>
                        @endforeach
                    </select>
                    @error('counselor_id')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-70" x-bind:disabled="loading">
                    <span x-show="!loading">Kirim permintaan</span>
                    <span x-show="loading" x-cloak>Mengirim...</span>
                </button>
            </form>
        </aside>
    </div>
</div>
@endsection
