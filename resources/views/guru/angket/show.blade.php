@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <x-section-title
                    title="Detail Angket Siswa"
                    description="Jawaban angket BK per pertanyaan."
                />
                <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-600">
                    <span class="rounded-full bg-slate-100 px-3 py-1">Nama: <strong class="text-slate-900">{{ $student->user?->name ?? $student->name }}</strong></span>
                    <span class="rounded-full bg-slate-100 px-3 py-1">Kelas: <strong class="text-slate-900">{{ $student->kelas?->nama ?? '-' }}</strong></span>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 font-semibold text-emerald-800">Predikat: {{ $predikat }}</span>
                </div>
            </div>
            <a href="{{ route('guru.angket.pdf', $student) }}" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">
                Download PDF
            </a>
        </div>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">No</th>
                            <th class="px-5 py-4">Pertanyaan</th>
                            <th class="px-5 py-4">Jawaban</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($student->responsAngket as $index => $respons)
                            <tr>
                                <td class="px-5 py-4 text-slate-600">{{ $index + 1 }}</td>
                                <td class="px-5 py-4 font-medium text-slate-900">{{ $respons->masterQuestion?->teks_pertanyaan ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $respons->jawaban }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8">
                                    <x-empty-state title="Belum ada jawaban" description="Siswa belum mengisi angket BK." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            <a href="{{ route('guru.angket.index') }}" class="inline-flex rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali ke daftar
            </a>
        </div>
    </section>
</div>
@endsection
