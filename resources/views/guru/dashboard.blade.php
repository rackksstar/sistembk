@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title
            title="Dashboard Guru BK"
            description="Kelola antrian konseling siswa dan pantau sesi yang perlu ditindaklanjuti."
        />

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
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title title="Statistik Kategori Kasus" description="Ringkasan layanan selesai berdasarkan kategori kasus." />
            <div class="mt-5 space-y-3">
                @foreach(\App\Models\ConsultationRequest::CASE_CATEGORIES as $value => $label)
                    @php($total = (int) ($caseStats[$value] ?? 0))
                    <div>
                        <div class="flex justify-between text-sm font-semibold text-slate-700">
                            <span>{{ $label }}</span>
                            <span>{{ $total }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-blue-600" style="width: {{ min($total * 18, 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title title="Riwayat Siswa" description="Monitoring layanan konseling individu terbaru." />
            <div class="mt-5 space-y-3">
                @forelse($recentStudentHistories as $history)
                    <article class="rounded-2xl bg-slate-50 p-4 text-sm">
                        <p class="font-semibold text-slate-950">{{ $history->student?->name }} - {{ $history->caseCategoryLabel() }}</p>
                        <p class="mt-1 text-slate-600">{{ $history->student?->classModel?->name ?? '-' }} | {{ $history->student?->schoolModel?->name ?? '-' }}</p>
                        <p class="mt-2 line-clamp-2 text-slate-500">{{ $history->result }}</p>
                    </article>
                @empty
                    <x-empty-state title="Belum ada riwayat" description="Riwayat akan muncul setelah laporan konseling disimpan." />
                @endforelse
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title
            title="Antrian Konseling"
            description="Permintaan tanpa Guru BK dan permintaan yang sudah ditugaskan ke Anda."
        />

        <div class="mt-6 space-y-4">
            @forelse($requests as $request)
                <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $request->subject }}</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $request->student?->name }} - pilihan waktu: {{ $request->preferred_time }}</p>
                            @if($request->details)
                                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $request->details }}</p>
                            @endif
                        </div>
                        <span class="w-fit rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-700">{{ $request->status }}</span>
                    </div>
                </article>
            @empty
                <x-empty-state title="Antrian masih kosong" description="Belum ada siswa yang mengirim permintaan konseling baru." />
            @endforelse
        </div>
    </section>
</div>
@endsection
