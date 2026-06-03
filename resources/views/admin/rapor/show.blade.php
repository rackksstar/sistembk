@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title
            :title="'Rapor BK — '.($rapor->student->user?->name ?? $rapor->student->name)"
            :description="$rapor->semesterLabel().' · '.$rapor->tahun_ajaran.' · '.$rapor->statusLabel()"
        />

        <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
            <div><dt class="font-semibold text-slate-500">Guru BK</dt><dd class="mt-1 text-slate-900">{{ $rapor->counselor?->name ?? '-' }}</dd></div>
            <div><dt class="font-semibold text-slate-500">Kelas</dt><dd class="mt-1 text-slate-900">{{ $rapor->student->kelas?->nama ?? '-' }}</dd></div>
            <div><dt class="font-semibold text-slate-500">NISN</dt><dd class="mt-1 text-slate-900">{{ $rapor->student->nisn ?? '-' }}</dd></div>
            <div><dt class="font-semibold text-slate-500">Diperbarui</dt><dd class="mt-1 text-slate-900">{{ $rapor->updated_at?->format('d M Y H:i') }}</dd></div>
        </dl>

        <div class="mt-8 space-y-6 text-sm leading-relaxed text-slate-700">
            <div>
                <h3 class="font-semibold text-slate-900">Perkembangan akademik</h3>
                <p class="mt-2 whitespace-pre-wrap">{{ $rapor->perkembangan_akademik ?: '-' }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-slate-900">Perkembangan sosial</h3>
                <p class="mt-2 whitespace-pre-wrap">{{ $rapor->perkembangan_sosial ?: '-' }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-slate-900">Perkembangan psikologis</h3>
                <p class="mt-2 whitespace-pre-wrap">{{ $rapor->perkembangan_psikologis ?: '-' }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-slate-900">Saran & tindak lanjut</h3>
                <p class="mt-2 whitespace-pre-wrap">{{ $rapor->saran_tindak_lanjut ?: '-' }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-slate-900">Catatan guru</h3>
                <p class="mt-2 whitespace-pre-wrap">{{ $rapor->catatan_guru ?: '-' }}</p>
            </div>
        </div>

        <div class="mt-8">
            <a href="{{ route('admin.rapor.index') }}" class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kembali ke daftar</a>
        </div>
    </section>
</div>
@endsection
