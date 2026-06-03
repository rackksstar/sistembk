@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Tryout BK" description="Tryout aktif untuk kelasmu dan riwayat pengerjaan." />
        <x-alert class="mt-4" type="success" :message="session('success')" />
        @if($belumPunyaKelas ?? false)
            <x-alert class="mt-4" type="warning" message="Profil siswa belum memiliki kelas. Hubungi admin agar tryout dapat ditampilkan." />
        @endif
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Tryout aktif</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @forelse($tryouts as $tryout)
                <article class="rounded-2xl border border-blue-100 bg-blue-50/50 p-5">
                    <h3 class="font-semibold text-slate-900">{{ $tryout->judul }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $tryout->durasi_menit }} menit · hingga {{ $tryout->selesai_at->format('d M Y H:i') }}</p>
                    <a href="{{ route('siswa.tryout.show', $tryout) }}" class="mt-4 inline-flex rounded-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white">Kerjakan</a>
                </article>
            @empty
                <x-empty-state title="Tidak ada tryout aktif" description="Coba lagi nanti atau hubungi Guru BK." />
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Riwayat tryout</h2>
        <ul class="mt-4 space-y-2 text-sm">
            @forelse($riwayat as $item)
                <li class="flex justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <span class="font-medium text-slate-900">{{ $item->tryOut->judul }}</span>
                    <span class="text-slate-600">Skor {{ number_format($item->rata_skor ?? 0, 1) }} · {{ $item->submitted_at?->format('d M Y') }}</span>
                </li>
            @empty
                <p class="text-slate-500">Belum ada riwayat.</p>
            @endforelse
        </ul>
    </section>
</div>
@endsection
