@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title
            :title="'Rapor BK — '.($student->user?->name ?? $student->name)"
            :description="'Periode '.(\App\Models\RaporBk::SEMESTERS[$semester] ?? $semester).' · '.$tahunAjaran"
        />

        <div class="mt-4 rounded-2xl border border-blue-100 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/40 p-4 text-sm text-slate-700 dark:text-slate-300">
            <p><span class="font-semibold">Ringkasan konseling (Phase 3–4):</span>
                {{ $ringkasanKonseling['total_konseling'] }} sesi selesai,
                {{ $ringkasanKonseling['total_dinilai'] }} penilaian siswa,
                rata-rata {{ number_format($ringkasanKonseling['rata_penilaian'], 1) }}/5.
            </p>
        </div>

        <form method="POST" action="{{ route('guru.rapor.update', $student) }}" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <input type="hidden" name="semester" value="{{ $semester }}">
            <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">

            <div class="space-y-2">
                <x-input-label for="perkembangan_akademik" value="Perkembangan akademik" />
                <textarea id="perkembangan_akademik" name="perkembangan_akademik" rows="4"
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">{{ old('perkembangan_akademik', $rapor?->perkembangan_akademik) }}</textarea>
                <x-input-error :messages="$errors->get('perkembangan_akademik')" />
            </div>

            <div class="space-y-2">
                <x-input-label for="perkembangan_sosial" value="Perkembangan sosial" />
                <textarea id="perkembangan_sosial" name="perkembangan_sosial" rows="4"
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">{{ old('perkembangan_sosial', $rapor?->perkembangan_sosial) }}</textarea>
                <x-input-error :messages="$errors->get('perkembangan_sosial')" />
            </div>

            <div class="space-y-2">
                <x-input-label for="perkembangan_psikologis" value="Perkembangan psikologis" />
                <textarea id="perkembangan_psikologis" name="perkembangan_psikologis" rows="4"
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">{{ old('perkembangan_psikologis', $rapor?->perkembangan_psikologis) }}</textarea>
                <x-input-error :messages="$errors->get('perkembangan_psikologis')" />
            </div>

            <div class="space-y-2">
                <x-input-label for="saran_tindak_lanjut" value="Saran & tindak lanjut" />
                <textarea id="saran_tindak_lanjut" name="saran_tindak_lanjut" rows="3"
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">{{ old('saran_tindak_lanjut', $rapor?->saran_tindak_lanjut) }}</textarea>
                <x-input-error :messages="$errors->get('saran_tindak_lanjut')" />
            </div>

            <div class="space-y-2">
                <x-input-label for="catatan_guru" value="Catatan guru BK" />
                <textarea id="catatan_guru" name="catatan_guru" rows="2"
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">{{ old('catatan_guru', $rapor?->catatan_guru) }}</textarea>
                <x-input-error :messages="$errors->get('catatan_guru')" />
            </div>

            <div class="space-y-2">
                <x-input-label for="status" value="Status rapor" />
                <select id="status" name="status" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm">
                    @foreach(\App\Models\RaporBk::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $rapor?->status ?? \App\Models\RaporBk::STATUS_DRAFT) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('status')" />
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-2xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">
                    Simpan rapor
                </button>
                <a href="{{ route('guru.rapor.index', ['semester' => $semester, 'tahun_ajaran' => $tahunAjaran]) }}"
                    class="rounded-2xl border border-slate-200 dark:border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                    Kembali
                </a>
                @if($rapor)
                    <a href="{{ route('guru.rapor.pdf', $rapor) }}"
                        class="rounded-2xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/40 px-5 py-2.5 text-sm font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-100">
                        Unduh PDF
                    </a>
                @endif
            </div>
        </form>
    </section>
</div>
@endsection
