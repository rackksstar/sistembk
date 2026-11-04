<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Konseling Individu - {{ $consultationRequest->report_title ?: $consultationRequest->subject }}</title>
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
    <h1>LAPORAN KONSELING INDIVIDU</h1>
    <p class="muted" style="text-align:center;">Sistem Informasi Administrasi BK</p>

    <table class="meta">
        <tr><td>Siswa</td><td>{{ $consultationRequest->student?->name ?? '-' }}</td></tr>
        <tr><td>NISN</td><td>{{ $consultationRequest->student?->studentProfile?->nisn ?? '-' }}</td></tr>
        <tr><td>Kelas</td><td>{{ $consultationRequest->student?->studentProfile?->kelas?->nama ?? '-' }}</td></tr>
        <tr><td>Sekolah</td><td>{{ $consultationRequest->student?->studentProfile?->kelas?->sekolah?->nama ?? '-' }}</td></tr>
        <tr><td>Guru BK</td><td>{{ $consultationRequest->counselor?->name ?? '-' }}</td></tr>
        <tr><td>Judul Laporan</td><td>{{ $consultationRequest->report_title ?: $consultationRequest->subject }}</td></tr>
        <tr><td>Topik/Keluhan</td><td>{{ $consultationRequest->subject }}</td></tr>
        <tr><td>Pendekatan &amp; Teknik</td><td>{{ $consultationRequest->approach_technique ?: '-' }}</td></tr>
        <tr><td>Kategori Kasus</td><td>{{ $consultationRequest->caseCategoryLabel() }}</td></tr>
        <tr><td>Tanggal Konseling</td><td>{{ $consultationRequest->consultation_date?->format('d M Y') ?? '-' }} {{ $consultationRequest->consultation_time ? substr($consultationRequest->consultation_time, 0, 5) : '' }}</td></tr>
        <tr><td>Tempat</td><td>{{ $consultationRequest->location ?: '-' }}</td></tr>
        <tr><td>Pertemuan ke</td><td>{{ $consultationRequest->meeting_number ?: '-' }}</td></tr>
        <tr><td>Durasi (menit)</td><td>{{ $consultationRequest->duration_minutes ?: '-' }}</td></tr>
        <tr><td>Semester/Tahun</td><td>{{ $consultationRequest->semesterLabel() }} / {{ $consultationRequest->year ?: '-' }}</td></tr>
    </table>

    <section><h2>Uraian Masalah</h2><p>{{ $consultationRequest->details ?: '-' }}</p></section>
    <section><h2>Hasil Konseling</h2><p>{{ $consultationRequest->result ?: '-' }}</p></section>
    <section><h2>Evaluasi</h2><p>{{ $consultationRequest->evaluation ?: '-' }}</p></section>
    <section><h2>Tindak Lanjut</h2><p>{{ $consultationRequest->follow_up ?: '-' }}</p></section>
</body>
</html>
