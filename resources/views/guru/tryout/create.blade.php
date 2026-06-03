@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Buat Tryout" description="Pilih kelas, soal tryout dari master, dan jadwal pengerjaan." />

        <form method="POST" action="{{ route('guru.tryout.store') }}" class="mt-6 space-y-5">
            @csrf
            <div class="space-y-2">
                <x-input-label for="judul" value="Judul tryout" />
                <input type="text" id="judul" name="judul" value="{{ old('judul') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
            </div>
            <div class="space-y-2">
                <x-input-label for="deskripsi" value="Deskripsi" />
                <textarea id="deskripsi" name="deskripsi" rows="2" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">{{ old('deskripsi') }}</textarea>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="space-y-2">
                    <x-input-label for="durasi_menit" value="Durasi (menit)" />
                    <input type="number" id="durasi_menit" name="durasi_menit" value="{{ old('durasi_menit', 60) }}" min="5" max="180" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
                </div>
                <div class="space-y-2">
                    <x-input-label for="mulai_at" value="Mulai" />
                    <input type="datetime-local" id="mulai_at" name="mulai_at" value="{{ old('mulai_at') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
                </div>
                <div class="space-y-2">
                    <x-input-label for="selesai_at" value="Selesai" />
                    <input type="datetime-local" id="selesai_at" name="selesai_at" value="{{ old('selesai_at') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
                </div>
            </div>
            <div class="space-y-2">
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
                    @foreach(\App\Models\TryOut::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', \App\Models\TryOut::STATUS_AKTIF) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <x-input-label value="Kelas yang diikutkan" />
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($kelas as $k)
                        <label class="flex items-center gap-2 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-2 text-sm">
                            <input type="checkbox" name="kelas_ids[]" value="{{ $k->id }}" @checked(collect(old('kelas_ids', []))->contains($k->id))>
                            <span>{{ $k->nama }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="space-y-2">
                <x-input-label value="Soal tryout (master)" />
                <div class="max-h-64 space-y-2 overflow-y-auto rounded-2xl border border-slate-200 p-4">
                    @forelse($soal as $s)
                        <label class="flex gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="soal_ids[]" value="{{ $s->id }}" @checked(collect(old('soal_ids', []))->contains($s->id))>
                            <span>{{ $s->teks_pertanyaan }} <span class="text-xs text-slate-400">({{ $s->tipe_input }})</span></span>
                        </label>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada soal tryout aktif di master pertanyaan (admin).</p>
                    @endforelse
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="rounded-2xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white">Simpan tryout</button>
                <a href="{{ route('guru.tryout.index') }}" class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700">Batal</a>
            </div>
        </form>
    </section>
</div>
@endsection
