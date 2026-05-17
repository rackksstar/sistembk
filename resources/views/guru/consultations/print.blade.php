<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Konseling - {{ $consultation->student?->name }}</title>
    <style>
        body { color: #0f172a; font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; line-height: 1.55; margin: 32px; }
        h1 { font-size: 20px; margin: 0; text-align: center; }
        h2 { font-size: 14px; margin: 0 0 8px; }
        .muted { color: #475569; }
        .meta { border-collapse: collapse; margin: 24px 0; width: 100%; }
        .meta td { border: 1px solid #cbd5e1; padding: 8px; vertical-align: top; }
        .meta td:first-child { font-weight: 700; width: 155px; }
        section { border: 1px solid #cbd5e1; margin-top: 14px; padding: 12px; }
        p { margin: 0; white-space: pre-line; }
    </style>
</head>
<body>
    <h1>LAPORAN KONSELING INDIVIDU</h1>
    <p class="muted" style="text-align:center;">Sistem Informasi Administrasi BK</p>

    <table class="meta">
        <tr><td>Nama Siswa</td><td>{{ $consultation->student?->name }}</td></tr>
        <tr><td>Kelas</td><td>{{ $consultation->student?->classModel?->name ?? '-' }}</td></tr>
        <tr><td>Sekolah</td><td>{{ $consultation->student?->schoolModel?->name ?? $consultation->student?->school ?? '-' }}</td></tr>
        <tr><td>Guru BK</td><td>{{ $consultation->counselor?->name }}</td></tr>
        <tr><td>Topik</td><td>{{ $consultation->subject }}</td></tr>
        <tr><td>Kategori Kasus</td><td>{{ $consultation->caseCategoryLabel() }}</td></tr>
        <tr><td>Jadwal</td><td>{{ $consultation->consultation_date?->format('d M Y') }} {{ $consultation->consultation_time ? substr($consultation->consultation_time, 0, 5) : '' }}</td></tr>
    </table>

    <section><h2>Hasil Konseling</h2><p>{{ $consultation->result }}</p></section>
    <section><h2>Evaluasi</h2><p>{{ $consultation->evaluation }}</p></section>
    <section><h2>Tindak Lanjut</h2><p>{{ $consultation->follow_up ?: '-' }}</p></section>
</body>
</html>
