@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title
                title="Dashboard Guru BK"
                description="Kelola antrian konseling siswa dan pantau sesi yang perlu ditindaklanjuti."
            />
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('guru.consultations.index') }}" class="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Kelola konseling</a>
                <a href="{{ route('guru.penilaian.index') }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Laporan penilaian</a>
                <a href="{{ route('guru.angket.index') }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Laporan angket</a>
                <a href="{{ route('guru.tryout.create') }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Buat tryout</a>
                <a href="{{ route('guru.rapor.index') }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Kelola rapor</a>
            </div>
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

    <section class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <x-section-title title="Statistik Kategori Kasus" description="Ringkasan layanan selesai berdasarkan kategori kasus." />
            <div class="mt-5 space-y-3">
                @foreach(\App\Models\ConsultationRequest::CASE_CATEGORIES as $value => $label)
                    @php($total = (int) ($caseStats[$value] ?? 0))
                    <div>
                        <div class="flex justify-between text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <span>{{ $label }}</span>
                            <span>{{ $total }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-2 rounded-full bg-blue-600" style="width: {{ min($total * 18, 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <x-section-title title="Riwayat Siswa" description="Monitoring layanan konseling individu terbaru." />
            <div class="mt-5 space-y-3">
                @forelse($recentStudentHistories as $history)
                    <article class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 text-sm">
                        <p class="font-semibold text-slate-950 dark:text-white">{{ $history->student?->name }} - {{ $history->caseCategoryLabel() }}</p>
                        <p class="mt-1 text-slate-600 dark:text-slate-400">{{ $history->student?->studentProfile?->kelas?->nama ?? '-' }}</p>
                        <p class="mt-2 line-clamp-2 text-slate-500 dark:text-slate-400">{{ $history->result }}</p>
                    </article>
                @empty
                    <x-empty-state title="Belum ada riwayat" description="Riwayat akan muncul setelah laporan konseling disimpan." />
                @endforelse
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <x-section-title
                title="Antrian Konseling"
                description="Permintaan tanpa Guru BK dan permintaan yang sudah ditugaskan ke Anda."
            />
            <a href="{{ route('guru.consultations.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-500">Buka halaman konseling</a>
        </div>

        <div class="mt-6 space-y-4">
            @forelse($requests as $request)
                <article class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $request->subject }}</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $request->student?->name }} - pilihan waktu: {{ $request->preferred_time }}</p>
                            @if($request->details)
                                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $request->details }}</p>
                            @endif
                        </div>
                        <span class="w-fit rounded-full bg-white dark:bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-700 dark:text-slate-300">{{ $request->status }}</span>
                    </div>
                    <a href="{{ route('guru.consultations.index', ['status' => $request->status]) }}" class="mt-3 inline-flex rounded-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-500">Tindaklanjuti</a>
                </article>
            @empty
                <x-empty-state title="Antrian masih kosong" description="Belum ada siswa yang mengirim permintaan konseling baru." />
            @endforelse
        </div>
    </section>
</div>
@endsection
