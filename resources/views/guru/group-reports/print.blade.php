<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Konseling Kelompok - {{ $groupReport->title }}</title>
    <style>
        body { color: #0f172a; font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; line-height: 1.55; margin: 32px; }
        h1 { font-size: 20px; margin: 0; text-align: center; }
        h2 { font-size: 14px; margin: 0 0 8px; }
        .muted { color: #475569; }
        .meta { border-collapse: collapse; margin: 24px 0; width: 100%; }
        .meta td { border: 1px solid #cbd5e1; padding: 8px; vertical-align: top; }
        .meta td:first-child { font-weight: 700; width: 165px; }
        section { border: 1px solid #cbd5e1; margin-top: 14px; padding: 12px; }
        p { margin: 0; white-space: pre-line; }
    </style>
</head>
<body>
    <h1>LAPORAN KONSELING KELOMPOK</h1>
    <p class="muted" style="text-align:center;">Sistem Informasi Administrasi BK</p>

    <table class="meta">
        <tr><td>Judul Laporan</td><td>{{ $groupReport->title }}</td></tr>
        <tr><td>RPL Terkait</td><td>{{ $groupReport->rpl?->title ?? '-' }}</td></tr>
        <tr><td>Kelas</td><td>{{ $groupReport->classRoom?->name ?? '-' }}</td></tr>
        <tr><td>Anggota Kelompok</td><td>{{ $groupReport->rpl?->groupStudents?->pluck('name')->join(', ') ?: '-' }}</td></tr>
        <tr><td>Guru BK</td><td>{{ $groupReport->teacher?->name }}</td></tr>
        <tr><td>Sekolah</td><td>{{ $groupReport->teacher?->schoolModel?->name ?? $groupReport->teacher?->school ?? '-' }}</td></tr>
        <tr><td>Kategori Kasus</td><td>{{ $groupReport->caseCategoryLabel() }}</td></tr>
        <tr><td>Tanggal Layanan</td><td>{{ $groupReport->service_date?->format('d M Y') }}</td></tr>
        <tr><td>Durasi</td><td>{{ $groupReport->duration_minutes ? $groupReport->duration_minutes.' menit' : '-' }}</td></tr>
        <tr><td>Tempat</td><td>{{ $groupReport->location ?: '-' }}</td></tr>
    </table>

    <section><h2>Hasil Konseling Kelompok</h2><p>{{ $groupReport->result }}</p></section>
    <section><h2>Evaluasi</h2><p>{{ $groupReport->evaluation }}</p></section>
    <section><h2>Tindak Lanjut</h2><p>{{ $groupReport->follow_up ?: '-' }}</p></section>
</body>
</html>
