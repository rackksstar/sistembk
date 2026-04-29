<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Konseling - {{ $consultation->student?->name }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-white text-slate-900">
    <main class="mx-auto max-w-3xl p-8">
        <div class="flex items-start justify-between gap-4 print:hidden">
            <a href="{{ route('guru.consultations.index') }}" class="text-sm font-semibold text-blue-600">Kembali</a>
            <button onclick="window.print()" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Print / Export PDF</button>
        </div>

        <section class="mt-8 border-b border-slate-200 pb-6 print:mt-0">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600">Laporan Konseling</p>
            <h1 class="mt-2 text-3xl font-semibold">Sistem Informasi BK</h1>
            <p class="mt-2 text-sm text-slate-500">{{ now()->format('d M Y H:i') }}</p>
        </section>

        <dl class="mt-8 grid gap-4 text-sm sm:grid-cols-2">
            <div><dt class="font-semibold">Siswa</dt><dd class="mt-1">{{ $consultation->student?->name }}</dd></div>
            <div><dt class="font-semibold">Guru BK</dt><dd class="mt-1">{{ $consultation->counselor?->name }}</dd></div>
            <div><dt class="font-semibold">Topik</dt><dd class="mt-1">{{ $consultation->subject }}</dd></div>
            <div><dt class="font-semibold">Jadwal</dt><dd class="mt-1">{{ $consultation->consultation_date?->format('d M Y') }} {{ $consultation->consultation_time ? substr($consultation->consultation_time, 0, 5) : '' }}</dd></div>
        </dl>

        <section class="mt-8 space-y-6">
            <div>
                <h2 class="font-semibold">Hasil Konseling</h2>
                <p class="mt-2 whitespace-pre-line leading-7 text-slate-700">{{ $consultation->result }}</p>
            </div>
            <div>
                <h2 class="font-semibold">Evaluasi</h2>
                <p class="mt-2 whitespace-pre-line leading-7 text-slate-700">{{ $consultation->evaluation }}</p>
            </div>
        </section>
    </main>
</body>
</html>
