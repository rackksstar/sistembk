@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ gradeOpen: null }">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <x-section-title :title="$tryout->judul" :description="'Rata-rata keseluruhan: '.number_format($rataKeseluruhan, 1)" />
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $tryout->mulai_at->format('d M Y H:i') }} – {{ $tryout->selesai_at->format('d M Y H:i') }} · {{ $tryout->durasi_menit }} menit</p>
        @if($tryout->kelas->isNotEmpty())
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Kelas: <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $tryout->kelas->pluck('nama')->join(', ') }}</span>
            </p>
        @endif

        <div class="mt-4 flex flex-wrap items-center gap-3">
            @if($tryout->hasilSudahDipublikasikan())
                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                    Hasil dipublikasikan{{ $tryout->hasil_dipublikasikan_at ? ' · '.$tryout->hasil_dipublikasikan_at->format('d M Y H:i') : '' }}
                </span>
                <form method="POST" action="{{ route('guru.tryout.unpublish', $tryout) }}" onsubmit="return confirm('Sembunyikan hasil dari siswa?')">
                    @csrf
                    @method('PATCH')
                    <button class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">Batalkan publikasi</button>
                </form>
            @else
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">Hasil belum dipublikasikan</span>
                <form method="POST" action="{{ route('guru.tryout.publish', $tryout) }}" onsubmit="return confirm('Publikasikan skor ke siswa?')">
                    @csrf
                    @method('PATCH')
                    <button class="rounded-2xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-500">Publikasikan hasil</button>
                </form>
            @endif
        </div>

        <x-alert class="mt-4" type="success" :message="session('success')" />

        <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ route('guru.tryout.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">← Kembali</a>
            <a href="{{ route('guru.tryout.edit', $tryout) }}" class="rounded-2xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/40 px-4 py-2 text-xs font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-950/60">Edit tryout</a>
            <a href="{{ route('guru.master-pertanyaan.index', ['kategori' => 'tryout']) }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Master pertanyaan</a>
            <a href="{{ route('guru.tryout.create') }}" class="rounded-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-500">Buat tryout baru</a>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <x-section-title title="Hasil per siswa" description="Nilai skala/PG otomatis. Isian/esai bisa dinilai ulang sebelum dipublikasikan." />
        <div class="mt-6 overflow-x-auto rounded-3xl border border-slate-200 dark:border-slate-700">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-4 text-left">Siswa</th>
                        <th class="px-5 py-4 text-left">Kelas</th>
                        <th class="px-5 py-4 text-left">Rata skor</th>
                        <th class="px-5 py-4 text-left">Status nilai</th>
                        <th class="px-5 py-4 text-left">Dikumpulkan</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                    @forelse($tryout->details as $detail)
                        {{-- Bagian ini WAJIB memakai bentuk blok, bukan bentuk
                             inline singkat. BladeCompiler::storePhpBlocks()
                             memakai regex lazy yang mencocokkan pembuka php
                             pertama sampai penutup php pertama. Bentuk inline
                             tanpa penutup akan "melahap" segalanya sampai
                             penutup php berikutnya, sehingga if/foreach di
                             antaranya tidak terkompilasi dan halaman error 500. --}}
                        @php
                            $pending = $detail->belumDinilaiCount($soal->pluck('id'));
                        @endphp
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $detail->student->user?->name ?? $detail->student->name }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $detail->student->kelas?->nama ?? '—' }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ number_format($detail->rata_skor ?? 0, 1) }}</td>
                            <td class="px-5 py-4">
                                @if($pending > 0)
                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">{{ $pending }} soal belum dinilai</span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">Lengkap</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $detail->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td class="px-5 py-4 text-right">
                                <button type="button" x-on:click="gradeOpen = {{ $detail->id }}" class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-300">Nilai / lihat jawaban</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8"><x-empty-state title="Belum ada peserta" description="Hasil muncul setelah siswa mengumpulkan tryout." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @foreach($tryout->details as $detail)
        <div x-show="gradeOpen === {{ $detail->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="gradeOpen = null" class="max-h-[calc(100vh-2rem)] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900">
                <x-section-title
                    title="Penilaian {{ $detail->student->user?->name ?? $detail->student->name }}"
                    description="Edit nilai per soal (0–100). Isian/esai wajib dinilai Guru BK."
                />

                <form method="POST" action="{{ route('guru.tryout.scores', [$tryout, $detail]) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    @foreach($soal as $index => $item)
                        @php
                            $jawaban = $detail->jawaban[(string) $item->id] ?? ($detail->jawaban[$item->id] ?? '—');
                            $skor = $detail->skorUntuk($item->id);
                        @endphp
                        <article class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Soal {{ $index + 1 }} · {{ $item->tipeLabel() }}</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $item->teks_pertanyaan }}</p>
                            <p class="mt-3 rounded-xl bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
                                <span class="font-semibold">Jawaban:</span> {{ $jawaban }}
                            </p>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-300" for="skor-{{ $detail->id }}-{{ $item->id }}">Nilai</label>
                                <input
                                    id="skor-{{ $detail->id }}-{{ $item->id }}"
                                    type="number"
                                    name="skor[{{ $item->id }}]"
                                    min="0"
                                    max="100"
                                    step="0.1"
                                    value="{{ $skor }}"
                                    placeholder="{{ $item->tipe_input === \App\Models\MasterQuestion::TIPE_ISIAN ? 'Wajib isi' : 'Opsional' }}"
                                    class="w-32 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                                >
                                @if($skor === null)
                                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-300">Belum dinilai</span>
                                @endif
                            </div>
                        </article>
                    @endforeach

                    <div class="flex justify-end gap-3">
                        <button type="button" x-on:click="gradeOpen = null" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-300">Tutup</button>
                        <x-primary-button>Simpan nilai</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
