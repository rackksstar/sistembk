<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal Bulanan BK - {{ $journal->periodLabel() }}</title>
    <style>
        body { color: #0f172a; font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; line-height: 1.55; margin: 32px; }
        h1 { font-size: 20px; margin: 0; text-align: center; }
        h2 { font-size: 14px; margin: 0 0 8px; }
        table { border-collapse: collapse; margin: 24px 0; width: 100%; }
        td, th { border: 1px solid #cbd5e1; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; }
        section { border: 1px solid #cbd5e1; margin-top: 14px; padding: 12px; }
        p { margin: 0; white-space: pre-line; }
    </style>
</head>
<body>
    <h1>JURNAL BULANAN BIMBINGAN KONSELING</h1>
    <table>
        <tr><td>Periode</td><td>{{ $journal->periodLabel() }}</td></tr>
        <tr><td>Judul</td><td>{{ $journal->title }}</td></tr>
        <tr><td>Guru BK</td><td>{{ $journal->teacher?->name }}</td></tr>
        <tr><td>Sekolah</td><td>{{ $journal->teacher?->schoolModel?->name ?? $journal->teacher?->school ?? '-' }}</td></tr>
    </table>

    <table>
        <thead><tr><th>Jenis Layanan</th><th>Jumlah</th></tr></thead>
        <tbody>
            <tr><td>Konseling Individu</td><td>{{ $journal->individual_services }}</td></tr>
            <tr><td>Konseling Kelompok</td><td>{{ $journal->group_services }}</td></tr>
            <tr><td>Layanan Klasikal</td><td>{{ $journal->classical_services }}</td></tr>
        </tbody>
    </table>

    <section><h2>Ringkasan Kegiatan</h2><p>{{ $journal->summary }}</p></section>
    <section><h2>Evaluasi</h2><p>{{ $journal->evaluation ?: '-' }}</p></section>
    <section><h2>Tindak Lanjut</h2><p>{{ $journal->follow_up ?: '-' }}</p></section>
</body>
</html>
