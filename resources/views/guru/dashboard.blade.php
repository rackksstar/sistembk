@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
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
        <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
            <div class="flex items-end justify-between gap-4">
                <x-section-title title="Jadwal minggu ini" description="Sesi konseling yang sudah dijadwalkan untuk Anda." />
                <a href="{{ route('guru.consultations.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-500">Kalender lengkap</a>
            </div>
            <div class="mt-5 space-y-3">
                @forelse($upcomingWeek as $schedule)
                    <article class="rounded-2xl border border-emerald-200 bg-emerald-50/70 dark:border-emerald-900/60 dark:bg-emerald-950/25 p-4 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900 dark:text-slate-100">{{ $schedule->student?->name }}</p>
                                <p class="mt-1 text-slate-600 dark:text-slate-400">{{ $schedule->student?->studentProfile?->kelas?->nama ?? '—' }}</p>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">{{ $schedule->caseCategoryLabel() }}</span>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-x-2 text-slate-600 dark:text-slate-400">
                            <span>{{ $schedule->consultation_date?->format('d M Y') }}</span>
                            <span aria-hidden="true">·</span>
                            <span>{{ $schedule->consultation_time ? substr($schedule->consultation_time, 0, 5) : '-' }}</span>
                        </div>
                        <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $schedule->subject }}</p>
                    </article>
                @empty
                    <x-empty-state title="Belum ada jadwal minggu ini" description="Jadwalkan sesi dari antrian konseling." />
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
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
                            <div class="h-2 rounded-full bg-blue-600" style="width: {{ $total > 0 ? min($total * 18, 100) : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs lg:col-span-2">
            <x-section-title title="Riwayat Siswa" description="Monitoring layanan konseling individu terbaru yang sudah selesai." />
            <div class="mt-5 grid gap-3 md:grid-cols-2">
                @forelse($recentStudentHistories as $history)
                    <article class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/40 p-4 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-950 dark:text-white">{{ $history->student?->name }}</p>
                                <p class="mt-1 text-slate-600 dark:text-slate-400">{{ $history->student?->studentProfile?->kelas?->nama ?? '-' }} · {{ $history->caseCategoryLabel() }}</p>
                            </div>
                            @if($history->consultation_date)
                                <span class="whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">{{ $history->consultation_date->format('d M Y') }}{{ $history->consultation_time ? ' · '.substr($history->consultation_time, 0, 5) : '' }}</span>
                            @endif
                        </div>
                        <p class="mt-2 line-clamp-1 text-slate-700 dark:text-slate-300"><span class="text-slate-500 dark:text-slate-400">Topik:</span> {{ $history->subject }}</p>
                        @if($history->result)
                            <p class="mt-1.5 line-clamp-2 text-slate-600 dark:text-slate-400"><span class="text-slate-500 dark:text-slate-400">Hasil:</span> {{ $history->result }}</p>
                        @endif
                        @if($history->follow_up)
                            <p class="mt-1 line-clamp-1 text-xs text-slate-500 dark:text-slate-400"><span class="text-slate-400">Tindak lanjut:</span> {{ $history->follow_up }}</p>
                        @endif
                    </article>
                @empty
                    <div class="md:col-span-2">
                        <x-empty-state title="Belum ada riwayat" description="Riwayat akan muncul setelah laporan konseling disimpan dengan status selesai." />
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    @if(isset($penilaianAggregate))
        <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-section-title
                    title="Rata-rata skor penilaian"
                    description="Evaluasi pelayanan konseling dari siswa yang sudah dinilai."
                />
                <a href="{{ route('guru.penilaian.index') }}" class="ui-btn-secondary">Kelola penilaian</a>
            </div>

            @if($penilaianAggregate['total'] > 0)
                <div class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,260px)_minmax(0,1fr)]">
                    <div class="ui-panel flex flex-col items-center justify-center px-6 py-6 text-center">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Skor keseluruhan</p>
                        <p class="mt-2 text-5xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($penilaianAggregate['overall'], 1) }}<span class="text-lg font-semibold text-slate-400">/5</span></p>
                        <span class="mt-3 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">{{ $penilaianAggregate['predikat'] }}</span>
                        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">{{ $penilaianAggregate['total'] }} penilaian terkumpul</p>
                    </div>

                    <div class="space-y-4">
                        @foreach([
                            ['label' => 'Materi', 'value' => $penilaianAggregate['materi'], 'persen' => $penilaianAggregate['persen']['materi'], 'bar' => 'bg-blue-600'],
                            ['label' => 'Cara menyampaikan', 'value' => $penilaianAggregate['cara'], 'persen' => $penilaianAggregate['persen']['cara'], 'bar' => 'bg-emerald-500'],
                            ['label' => 'Manfaat', 'value' => $penilaianAggregate['manfaat'], 'persen' => $penilaianAggregate['persen']['manfaat'], 'bar' => 'bg-violet-500'],
                        ] as $aspect)
                            <div>
                                <div class="flex items-center justify-between text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    <span>{{ $aspect['label'] }}</span>
                                    <span class="tabular-nums">{{ number_format($aspect['value'], 1) }}</span>
                                </div>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full {{ $aspect['bar'] }} transition-[width] duration-700" style="width: {{ $aspect['persen'] }}%"></div>
                                </div>
                            </div>
                        @endforeach

                        @if($penilaianAggregate['belum'] > 0)
                            <p class="rounded-2xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">
                                {{ $penilaianAggregate['belum'] }} sesi selesai belum dinilai siswa.
                            </p>
                        @endif
                    </div>
                </div>
            @else
                <div class="mt-5">
                    <x-empty-state
                        title="Belum ada penilaian"
                        description="Rata-rata skor muncul setelah siswa mengisi penilaian pelayanan konseling."
                    />
                </div>
            @endif
        </section>
    @endif

    @if(isset($angketAggregate))
        <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-section-title
                    title="Progres angket siswa"
                    description="Cakupan pengisian angket untuk siswa di sekolah Anda."
                />
                <a href="{{ route('guru.angket.index') }}" class="ui-btn-secondary">Kelola angket</a>
            </div>

            @if($angketAggregate['siswa'] > 0 && $angketAggregate['total_soal'] > 0)
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="ui-card-muted px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Sudah mengisi</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $angketAggregate['sudah'] }}</p>
                    </div>
                    <div class="ui-card-muted px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Belum mengisi</p>
                        <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $angketAggregate['belum'] }}</p>
                    </div>
                    <div class="ui-card-muted px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Total siswa</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $angketAggregate['siswa'] }}</p>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="flex items-center justify-between text-sm font-semibold text-slate-700 dark:text-slate-300">
                        <span>Cakupan pengisian</span>
                        <span class="tabular-nums">{{ $angketAggregate['persen'] }}%</span>
                    </div>
                    <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-emerald-500 transition-[width] duration-700" style="width: {{ $angketAggregate['persen'] }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $angketAggregate['total_soal'] }} soal angket aktif dipakai.</p>
                </div>
            @else
                <div class="mt-5">
                    <x-empty-state
                        title="Belum ada soal angket aktif"
                        description="Tambahkan soal berkategori angket dari menu Master Pertanyaan agar progres bisa diukur."
                    />
                </div>
            @endif
        </section>
    @endif

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
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
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $request->subject }}</p>
                                <span class="inline-flex items-center rounded-full bg-slate-200/70 px-2 py-0.5 text-xs font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-300">{{ $request->caseCategoryLabel() }}</span>
                            </div>
                            <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $request->student?->name }} · {{ $request->student?->studentProfile?->kelas?->nama ?? '—' }}</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Preferensi siswa: {{ $request->preferred_time ?? '-' }}{{ $request->preferred_date ? ' · '.$request->preferred_date->format('d M Y') : '' }}
                            </p>
                            @if($request->consultation_date)
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Jadwal sesi: {{ $request->consultation_date->format('d M Y') }}{{ $request->consultation_time ? ' · '.substr($request->consultation_time, 0, 5) : '' }}
                                </p>
                            @endif
                            @if($request->details)
                                <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $request->details }}</p>
                            @endif
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-2">
                            <x-status-badge :status="$request->status" />
                            <a href="{{ route('guru.consultations.index', ['status' => $request->status]) }}" class="inline-flex rounded-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-500">Tindaklanjuti</a>
                        </div>
                    </div>
                </article>
            @empty
                <x-empty-state title="Antrian masih kosong" description="Belum ada siswa yang mengirim permintaan konseling baru." />
            @endforelse
        </div>
    </section>
</div>
@endsection
