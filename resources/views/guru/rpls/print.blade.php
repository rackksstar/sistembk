<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPL - {{ $rpl->title }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #0f172a; margin: 32px; }
        .toolbar { display: none; }
        .letterhead { display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center; margin-bottom: 24px; }
        .letterhead .school {
            text-align: left;
        }
        .letterhead .school h2 { margin: 0; font-size: 18px; letter-spacing: 0.02em; }
        .letterhead .school p { margin: 4px 0 0; font-size: 12px; color: #475569; }
        .letterhead .badge {
            border: 1px solid #cbd5e1;
            border-radius: 18px;
            padding: 10px 14px;
            text-align: right;
            font-size: 12px;
            color: #0f172a;
            background: #f8fafc;
        }
        .toolbar { display: none; }
        h1 { font-size: 22px; margin: 0 0 8px; text-align: center; }
        .meta { border: 1px solid #cbd5e1; border-collapse: collapse; margin: 24px 0; width: 100%; }
        .meta td { border: 1px solid #cbd5e1; padding: 10px; vertical-align: top; }
        .meta td:first-child { font-weight: 700; width: 180px; }
        section { border: 1px solid #cbd5e1; margin-top: 16px; padding: 16px; }
        h2 { font-size: 16px; margin: 0 0 10px; }
        p { line-height: 1.6; margin: 0; white-space: pre-line; }
        @media print {
            body { margin: 18mm; }
            .toolbar { display: none; }
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <div class="school">
            <h2>{{ $rpl->schoolName() }}</h2>
            <p>Bimbingan dan Konseling</p>
            <p>Jl. Sekolah No. 1 atau alamat resmi sekolah</p>
        </div>
        <div class="badge">
            <div><strong>No. Laporan</strong></div>
            <div>{{ $rpl->reportNumber() }}</div>
        </div>
    </div>
    <h1>RENCANA PELAKSANAAN LAYANAN BK</h1>
    <table class="meta">
        <tr><td>Judul</td><td>{{ $rpl->title }}</td></tr>
        <tr><td>Jenis Layanan</td><td>{{ $rpl->typeLabel() }}</td></tr>
        <tr><td>Status</td><td>{{ $rpl->statusLabel() }}</td></tr>
        <tr><td>Kelas</td><td>{{ $rpl->classRoom?->name ?? '-' }}</td></tr>
        <tr><td>Semester/Tahun</td><td>Semester {{ $rpl->semester ?? '-' }} / {{ $rpl->year ?? '-' }}</td></tr>
        <tr><td>Tanggal</td><td>{{ $rpl->service_date?->format('d M Y') ?: '-' }}</td></tr>
        <tr>
            <td>Sasaran</td>
            <td>
                @if($rpl->type === \App\Models\Rpl::TYPE_INDIVIDU)
                    {{ $rpl->student?->name ?: ($rpl->target ?: '-') }}
                @else
                    {{ $rpl->groupStudents->pluck('name')->join(', ') ?: ($rpl->target ?: '-') }}
                @endif
            </td>
        </tr>
        <tr><td>Guru BK</td><td>{{ $rpl->teacher?->name }}</td></tr>
        <tr><td>Sekolah</td><td>{{ $rpl->teacher?->schoolModel?->name ?? $rpl->teacher?->school ?? '-' }}</td></tr>
        <tr><td>Laporan Terkait</td><td>{{ $rpl->consultationReports->count() }} laporan konseling</td></tr>
    </table>

    <section><h2>Tujuan</h2><p>{{ $rpl->tujuan }}</p></section>
    <section><h2>Materi</h2><p>{{ $rpl->materi }}</p></section>
    <section><h2>Metode</h2><p>{{ $rpl->metode }}</p></section>
    <section><h2>Evaluasi</h2><p>{{ $rpl->evaluasi }}</p></section>
</body>
</html>
