@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title
            title="Statistik Layanan Guru BK"
            description="Rekap layanan individu dan kelompok serta kategori kasus per tahun."
        />

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-700">Total Layanan</p>
                <p class="mt-4 text-3xl font-semibold text-slate-950">{{ $summary['total'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Gabungan layanan individu dan kelompok.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-700">Layanan Individu</p>
                <p class="mt-4 text-3xl font-semibold text-slate-950">{{ $summary['individual'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Kasus konseling individu selesai.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-700">Layanan Kelompok</p>
                <p class="mt-4 text-3xl font-semibold text-slate-950">{{ $summary['group'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Laporan konseling kelompok selesai.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-700">Kategori Dominan</p>
                <p class="mt-4 text-3xl font-semibold text-slate-950">{{ $summary['dominantCategory'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Kategori kasus paling banyak.</p>
            </div>
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title title="Distribusi Kasus per Kategori" description="Perbandingan kasus individu dan kelompok per kategori." />
            <div class="mt-6">
                <canvas id="categoryChart" width="400" height="300"></canvas>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title title="Perbandingan Layanan" description="Total layanan individu vs kelompok." />
            <div class="mt-6">
                <canvas id="serviceTypeChart" width="400" height="300"></canvas>
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
</div>

@section('scripts')
<script>
    const categoryLabels = @json($categoryStats->pluck('label'));
    const categoryIndividual = @json($categoryStats->pluck('individual'));
    const categoryGroup = @json($categoryStats->pluck('group'));
    const serviceTypeLabels = @json($serviceTypeStats->pluck('label'));
    const serviceTypeValues = @json($serviceTypeStats->pluck('value'));
    const serviceTypeColors = @json($serviceTypeStats->pluck('color'));

    document.addEventListener('DOMContentLoaded', function () {
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: categoryLabels,
                    datasets: [
                        {
                            label: 'Individu',
                            data: categoryIndividual,
                            backgroundColor: 'rgba(56, 189, 248, 0.7)',
                        },
                        {
                            label: 'Kelompok',
                            data: categoryGroup,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'Kasus per kategori' },
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                    },
                },
            });
        }

        const serviceTypeCtx = document.getElementById('serviceTypeChart');
        if (serviceTypeCtx) {
            new Chart(serviceTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: serviceTypeLabels,
                    datasets: [
                        {
                            data: serviceTypeValues,
                            backgroundColor: serviceTypeColors,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        title: { display: true, text: 'Perbandingan layanan individu vs kelompok' },
                    },
                },
            });
        }
    });
</script>
@endsection
