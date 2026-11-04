<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rapor BK - {{ $rapor->student->user?->name ?? $rapor->student->name }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #333; margin: 32px; line-height: 1.55; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin: 0; }
        .header h2 { font-size: 13px; margin: 4px 0 0; font-weight: normal; }
        .info-table { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .info-table td { padding: 3px 8px; vertical-align: top; }
        .info-table td:first-child { width: 160px; font-weight: bold; }
        .section { margin-top: 14px; }
        .section h3 { font-size: 12px; margin: 0 0 6px; color: #1e3a5f; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        .section p { margin: 0; white-space: pre-wrap; }
        .ringkasan { margin: 12px 0; padding: 8px 12px; background: #f0f4f8; font-size: 11px; }
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RAPOR BIMBINGAN KONSELING</h1>
        <h2>{{ config('app.name') }}</h2>
    </div>

    <table class="info-table">
        <tr><td>Nama Siswa</td><td>: {{ $rapor->student->user?->name ?? $rapor->student->name ?? '-' }}</td></tr>
        <tr><td>NISN</td><td>: {{ $rapor->student->nisn ?? '-' }}</td></tr>
        <tr><td>Kelas</td><td>: {{ $rapor->student->kelas?->nama ?? '-' }}</td></tr>
        <tr><td>Sekolah</td><td>: {{ $rapor->student->kelas?->sekolah?->nama ?? $rapor->student->school ?? '-' }}</td></tr>
        <tr><td>Semester</td><td>: {{ $rapor->semesterLabel() }}</td></tr>
        <tr><td>Tahun Ajaran</td><td>: {{ $rapor->tahun_ajaran }}</td></tr>
        <tr><td>Guru BK</td><td>: {{ $rapor->counselor?->name ?? '-' }}</td></tr>
        <tr><td>Status</td><td>: {{ $rapor->statusLabel() }}</td></tr>
        <tr><td>Tanggal Cetak</td><td>: {{ $tanggalCetak }}</td></tr>
    </table>

    <div class="ringkasan">
        Konseling selesai: {{ $ringkasanKonseling['total_konseling'] }} ·
        Penilaian layanan: {{ $ringkasanKonseling['total_dinilai'] }} ·
        Rata-rata skor: {{ number_format($ringkasanKonseling['rata_penilaian'], 1) }}/5
    </div>

    <div class="section">
        <h3>Perkembangan Akademik</h3>
        <p>{{ $rapor->perkembangan_akademik ?: '-' }}</p>
    </div>
    <div class="section">
        <h3>Perkembangan Sosial</h3>
        <p>{{ $rapor->perkembangan_sosial ?: '-' }}</p>
    </div>
    <div class="section">
        <h3>Perkembangan Psikologis</h3>
        <p>{{ $rapor->perkembangan_psikologis ?: '-' }}</p>
    </div>
    <div class="section">
        <h3>Saran & Tindak Lanjut</h3>
        <p>{{ $rapor->saran_tindak_lanjut ?: '-' }}</p>
    </div>
    <div class="section">
        <h3>Catatan Guru BK</h3>
        <p>{{ $rapor->catatan_guru ?: '-' }}</p>
    </div>

    <div class="footer">Dicetak pada {{ $tanggalCetak }}</div>
</body>
</html>
