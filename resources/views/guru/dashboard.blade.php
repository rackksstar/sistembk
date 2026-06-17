@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title
            title="Dashboard Guru BK"
            description="Kelola antrian konseling siswa dan pantau sesi yang perlu ditindaklanjuti."
        />

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
            <div class="mt-5 space-y-4">
                @foreach($caseCategoryStats as $stat)
                    <div>
                        <div class="flex justify-between text-sm font-semibold text-slate-700">
                            <span>{{ $stat['label'] }}</span>
                            <span>{{ $stat['total'] }}</span>
                        </div>
                        <div class="mt-2 space-y-2">
                            <div class="h-3 rounded-full bg-slate-100">
                                <div class="h-3 rounded-full bg-blue-600" style="width: {{ $stat['total'] ? ($stat['total'] / max($caseCategoryStats->max('total'), 1) * 100) : 0 }}%"></div>
                            </div>
                            <div class="grid gap-2 text-[11px] text-slate-500 sm:grid-cols-2">
                                <div>Individu: {{ $stat['individual'] }}</div>
                                <div>Kelompok: {{ $stat['group'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title title="Perbandingan Layanan" description="Total layanan individu dan kelompok." />
            <div class="mt-6 space-y-4">
                @foreach($serviceTypeStats as $stat)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-700">{{ $stat['label'] }}</p>
                                <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $stat['value'] }}</p>
                            </div>
                            <div class="h-10 w-10 rounded-full {{ $stat['color'] }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 rounded-3xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-700">Proporsi Layanan</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach($serviceTypeStats as $stat)
                        <div class="rounded-2xl bg-white p-4 text-center shadow-sm">
                            <p class="text-sm font-semibold text-slate-800">{{ $stat['label'] }}</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $stat['value'] }}</p>
                        </div>
                    @endforeach
                </div>
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
