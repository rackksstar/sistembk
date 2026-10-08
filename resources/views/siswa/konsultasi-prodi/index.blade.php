@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ formOpen: {{ $errors->any() || $prefillProdiId ? 'true' : 'false' }} }">
    <section class="overflow-hidden rounded-3xl border border-slate-200 shadow-sm dark:border-slate-700">
        <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-6 py-5 text-center sm:text-left">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-indigo-100">Modul Key · Lanjut Kuliah</p>
                    <h1 class="mt-1 text-lg font-bold text-white sm:text-xl">Layanan Konsultasi Prodi Kuliah</h1>
                    <p class="mt-1 text-sm text-indigo-50/90">
                        Diskusikan pilihan program studi PCR dengan Guru BK — topik, prodi minat, dan jadwal bimbingan.
                    </p>
                </div>
                <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-white backdrop-blur-sm">
                    Prodi
                </span>
            </div>
        </div>

        <div class="bg-white p-6 dark:bg-slate-900">
            <x-alert type="success" :message="session('success')" />

            <div class="mt-1 flex flex-wrap gap-3">
                <button
                    type="button"
                    x-on:click="formOpen = !formOpen"
                    class="rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500"
                >
                    <span x-text="formOpen ? 'Tutup formulir' : 'Ajukan konsultasi prodi'"></span>
                </button>
                <a href="{{ route('siswa.minat-bakat.index') }}" class="rounded-full border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800/60">
                    Lihat hasil Minat Bakat Kuliah
                </a>
            </div>

            <div x-show="formOpen" x-cloak class="mt-6 rounded-3xl border border-blue-100 bg-blue-50/70 p-6 dark:border-blue-900/50 dark:bg-blue-950/30">
                <x-section-title title="Form konsultasi prodi" description="Pilih prodi PCR yang ingin dibahas, lalu ajukan ke Guru BK." />

                @if($programStudis->isEmpty())
                    <p class="mt-4 rounded-2xl bg-amber-50 p-4 text-sm text-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
                        Belum ada program studi PCR yang diverifikasi Admin. Minta Admin memverifikasi prodi di menu Program Studi.
                    </p>
                @else
                    <form action="{{ route('siswa.konsultasi-prodi.store') }}" method="POST" class="mt-6 grid gap-4 md:grid-cols-2">
                        @csrf
                        <div class="md:col-span-2 space-y-2">
                            <x-input-label for="subject" value="Topik / judul" />
                            <x-text-input
                                id="subject"
                                name="subject"
                                class="w-full"
                                :value="old('subject', $prefillSubject)"
                                required
                                placeholder="Contoh: Cocokkah saya masuk D4 Teknik Informatika?"
                            />
                            <x-input-error :messages="$errors->get('subject')" />
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <x-input-label for="program_studi_id" value="Program studi PCR" />
                            <x-form-select id="program_studi_id" name="program_studi_id" required>
                                <option value="">Pilih program studi</option>
                                @foreach($programStudis as $prodi)
                                    <option
                                        value="{{ $prodi->id }}"
                                        @selected((string) old('program_studi_id', $prefillProdiId) === (string) $prodi->id)
                                    >
                                        {{ $prodi->nama }} · {{ $prodi->jenjang_pendidikan }} · {{ $prodi->institusi }}
                                    </option>
                                @endforeach
                            </x-form-select>
                            <x-input-error :messages="$errors->get('program_studi_id')" />
                        </div>

                        <div class="space-y-2">
                            <x-input-label for="counselor_id" value="Guru BK" />
                            <x-form-select id="counselor_id" name="counselor_id" required>
                                <option value="">Pilih guru</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" @selected(old('counselor_id') == $teacher->id)>{{ $teacher->name }}</option>
                                @endforeach
                            </x-form-select>
                            <x-input-error :messages="$errors->get('counselor_id')" />
                        </div>

                        <div class="space-y-2">
                            <x-input-label for="preferred_date" value="Preferensi tanggal (opsional)" />
                            <x-text-input id="preferred_date" type="date" name="preferred_date" class="w-full" :value="old('preferred_date')" />
                            <x-input-error :messages="$errors->get('preferred_date')" />
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <x-input-label for="preferred_time" value="Preferensi waktu (opsional)" />
                            <x-text-input id="preferred_time" name="preferred_time" class="w-full" :value="old('preferred_time')" placeholder="Contoh: Senin pagi / 10:00" />
                            <x-input-error :messages="$errors->get('preferred_time')" />
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <x-input-label for="details" value="Pertanyaan / hal yang ingin dibahas" />
                            <textarea
                                id="details"
                                name="details"
                                rows="4"
                                required
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:focus:ring-blue-900/50"
                                placeholder="Ceritakan minatmu, hasil asesmen, atau keraguan tentang prodi tersebut."
                            >{{ old('details') }}</textarea>
                            <x-input-error :messages="$errors->get('details')" />
                        </div>

                        <div class="md:col-span-2">
                            <x-primary-button class="w-full sm:w-auto">Kirim pengajuan konsultasi prodi</x-primary-button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <x-section-title title="Riwayat konsultasi prodi" description="Pengajuan khusus program studi PCR." />
            <form method="GET" class="flex gap-2">
                <select name="status" class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800/60">
                    <option value="">Semua status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Filter</button>
                <x-filter-reset />
            </form>
        </div>

        <div class="mt-6 space-y-3">
            @forelse($consultations as $item)
                <article class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-800/50">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-950 dark:text-white">{{ $item->subject }}</p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Prodi:
                                <span class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $item->programStudi?->nama ?? '—' }}
                                    @if($item->programStudi?->jenjang_pendidikan)
                                        · {{ $item->programStudi->jenjang_pendidikan }}
                                    @endif
                                </span>
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Guru BK: {{ $item->counselor?->name ?? '—' }}
                                · {{ $item->created_at?->format('d M Y H:i') }}
                            </p>
                        </div>
                        <x-status-badge :status="$item->status" />
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $item->details }}</p>
                </article>
            @empty
                <x-empty-state title="Belum ada pengajuan" description="Ajukan konsultasi prodi untuk membahas pilihan kuliah dengan Guru BK." />
            @endforelse
        </div>

        <div class="mt-6">
            {{ $consultations->links() }}
        </div>
    </section>
</div>
@endsection
