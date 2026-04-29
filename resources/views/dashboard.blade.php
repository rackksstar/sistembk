@extends('layouts.app')

@section('content')
<div class="space-y-10">
    <section class="space-y-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="space-y-2">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-sky-600">Dasbor</p>
                <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Ringkasan bimbingan konseling</h1>
                <p class="max-w-2xl text-sm leading-6 text-slate-600">Sistem bagi siswa, guru BK, dan admin untuk melihat status sesi, permintaan, dan rekomendasi secara cepat.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <a href="#konseling" class="inline-flex items-center justify-center rounded-2xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500">Minta konseling</a>
                <a href="#riwayat" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Lihat riwayat</a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($metrics as $metric)
                <x-dashboard-card
                    :title="$metric['title']"
                    :description="$metric['description']"
                    :value="$metric['value']"
                    :icon="$metric['icon']"
                    :color="$metric['color']"
                />
            @endforeach
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.45fr_1fr]">
        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Jadwal Konseling Berikutnya</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Lihat sesi yang sudah disiapkan untuk Anda.</p>
                    </div>
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-600">Terdepan</span>
                </div>

                <div class="mt-6 space-y-4">
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Sesi Konseling Akademik</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">12 Mei 2026 · 10:00 WIB</p>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Dikonfirmasi</span>
                        </div>
                    </article>
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Sesi Konseling Karier</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">18 Mei 2026 · 13:00 WIB</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-700">Menunggu</span>
                        </div>
                    </article>
                </div>
            </div>

            <div id="riwayat" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Ringkasan Riwayat</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Semua sesi konseling Anda tersedia di sini.</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-500">4 sesi</span>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-semibold text-slate-900">Persiapan ujian akhir</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">7 April 2026 · Guru BK Ibu Rina</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-semibold text-slate-900">Manajemen stress</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">15 Maret 2026 · Guru BK Pak Budi</p>
                    </div>
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div id="konseling" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Permintaan Konseling</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Isi detail agar guru BK dapat mempersiapkan sesi terbaik.</p>
                    </div>
                </div>

                @if(session('success'))
                    <div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('dashboard.request') }}" method="POST" class="mt-6 space-y-5" onsubmit="const btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerText = 'Mengirim...';">
                    @csrf

                    <label class="block text-sm font-semibold text-slate-900" for="subject">Topik Konseling</label>
                    <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required autocomplete="off" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-200" placeholder="Misalnya: stress akademik" aria-describedby="subject-error" />
                    @error('subject')
                        <p id="subject-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <label class="block text-sm font-semibold text-slate-900" for="preferred_time">Waktu Pilihan</label>
                    <input id="preferred_time" name="preferred_time" type="text" value="{{ old('preferred_time') }}" required autocomplete="off" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-200" placeholder="Contoh: Senin pagi atau Rabu siang" aria-describedby="time-error" />
                    @error('preferred_time')
                        <p id="time-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <label class="block text-sm font-semibold text-slate-900" for="details">Catatan tambahan</label>
                    <textarea id="details" name="details" rows="4" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-200" placeholder="Tuliskan hal yang ingin dibahas...">{{ old('details') }}</textarea>
                    @error('details')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-3xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500">Kirim permintaan</button>
                </form>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-600">Rekomendasi</h2>
                <ul class="mt-5 space-y-4">
                    @foreach($recommendations as $item)
                        <li class="rounded-3xl border border-slate-200 bg-white p-4">
                            <p class="font-semibold text-slate-900">{{ $item['title'] }}</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $item['description'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </section>
</div>
@endsection
