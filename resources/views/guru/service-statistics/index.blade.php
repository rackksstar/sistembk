@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <x-section-title
            title="Statistik Layanan Guru BK"
            description="Rekap layanan individu dan kelompok serta kategori kasus per tahun."
        />

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Total Layanan</p>
                <p class="mt-4 text-3xl font-semibold text-slate-950 dark:text-white">{{ $summary['total'] }}</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Gabungan layanan individu dan kelompok.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Layanan Individu</p>
                <p class="mt-4 text-3xl font-semibold text-slate-950 dark:text-white">{{ $summary['individual'] }}</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Kasus konseling individu selesai.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Layanan Kelompok</p>
                <p class="mt-4 text-3xl font-semibold text-slate-950 dark:text-white">{{ $summary['group'] }}</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Laporan konseling kelompok selesai.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Kategori Dominan</p>
                <p class="mt-4 text-3xl font-semibold text-slate-950 dark:text-white">{{ $summary['dominantCategory'] }}</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Kategori kasus paling banyak.</p>
            </div>
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <x-section-title title="Distribusi Kasus per Kategori" description="Perbandingan kasus individu dan kelompok per kategori." />
            <div class="mt-6">
                <canvas id="categoryChart" width="400" height="300"></canvas>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <x-section-title title="Perbandingan Layanan" description="Total layanan individu vs kelompok." />
            <div class="mt-6">
                <canvas id="serviceTypeChart" width="400" height="300"></canvas>
            </div>
            <div class="mt-6 rounded-3xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Proporsi Layanan</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach($serviceTypeStats as $stat)
                        <div class="rounded-2xl bg-white p-4 text-center shadow-sm dark:bg-slate-900">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $stat['label'] }}</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">{{ $stat['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const categoryLabels = @json($categoryStats->pluck('label'));
    const categoryIndividual = @json($categoryStats->pluck('individual'));
    const categoryGroup = @json($categoryStats->pluck('group'));
    const serviceTypeLabels = @json($serviceTypeStats->pluck('label'));
    const serviceTypeValues = @json($serviceTypeStats->pluck('value'));
    const serviceTypeColors = @json($serviceTypeStats->pluck('color'));
    const isDark = document.documentElement.classList.contains('dark');
    const tickColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(148, 163, 184, 0.2)';

    document.addEventListener('DOMContentLoaded', function () {
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx && window.Chart) {
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
                        legend: { position: 'top', labels: { color: tickColor } },
                        title: { display: true, text: 'Kasus per kategori', color: tickColor },
                    },
                    scales: {
                        x: { ticks: { color: tickColor }, grid: { color: gridColor } },
                        y: { beginAtZero: true, ticks: { precision: 0, color: tickColor }, grid: { color: gridColor } },
                    },
                },
            });
        }

        const serviceTypeCtx = document.getElementById('serviceTypeChart');
        if (serviceTypeCtx && window.Chart) {
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
                        legend: { position: 'bottom', labels: { color: tickColor } },
                        title: { display: true, text: 'Perbandingan layanan individu vs kelompok', color: tickColor },
                    },
                },
            });
        }
    });
</script>
@endpush
