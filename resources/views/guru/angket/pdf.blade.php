<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Angket BK - {{ $student->user?->name ?? $student->name }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #333; margin: 32px; line-height: 1.55; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin: 0; }
        .header h2 { font-size: 13px; margin: 4px 0 0; font-weight: normal; }
        .info-table { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .info-table td { padding: 3px 8px; vertical-align: top; }
        .info-table td:first-child { width: 140px; font-weight: bold; }
        table.soal { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.soal th { background: #4a7c59; color: white; padding: 6px 8px; text-align: left; font-size: 11px; }
        table.soal td { padding: 5px 8px; border-bottom: 1px solid #ddd; vertical-align: top; font-size: 11px; }
        table.soal tr:nth-child(even) td { background: #f9f9f9; }
        .predikat { margin-top: 20px; padding: 8px 12px; background: #e8f5e9; border-left: 4px solid #4a7c59; font-weight: bold; }
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ANGKET BIMBINGAN KONSELING</h1>
        <h2>{{ config('app.name') }}</h2>
    </div>

    <table class="info-table">
        <tr><td>Nama Siswa</td><td>: {{ $student->user?->name ?? $student->name ?? '-' }}</td></tr>
        <tr><td>Kelas</td><td>: {{ $student->kelas?->nama ?? '-' }}</td></tr>
        <tr><td>Tanggal Cetak</td><td>: {{ $tanggalCetak }}</td></tr>
        <tr><td>Total Soal</td><td>: {{ $totalSoalAktif }}</td></tr>
        <tr><td>Dijawab</td><td>: {{ $student->responsAngket->count() }}</td></tr>
    </table>

    <table class="soal">
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th style="width:50%">Pertanyaan</th>
                <th>Jawaban</th>
            </tr>
        </thead>
        <tbody>
            @foreach($student->responsAngket as $i => $respons)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $respons->masterQuestion?->teks_pertanyaan ?? '-' }}</td>
                <td>{{ $respons->jawaban }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="predikat">Predikat: {{ $predikat }}</div>

    <div class="footer">Dicetak pada {{ $tanggalCetak }}</div>
</body>
</html>
