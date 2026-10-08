<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Hasil Minat Bakat - {{ $user?->name ?? 'Siswa' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #333; margin: 32px; line-height: 1.55; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin: 0; }
        .header h2 { font-size: 13px; margin: 4px 0 0; font-weight: normal; }
        .info-table { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .info-table td { padding: 3px 8px; vertical-align: top; }
        .info-table td:first-child { width: 160px; font-weight: bold; }
        .section { margin-top: 16px; }
        .section h3 { font-size: 12px; margin: 0 0 8px; color: #1e3a5f; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        table.scores { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.scores th, table.scores td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; font-size: 11px; }
        table.scores th { background: #f0f4f8; }
        .kode { font-size: 20px; font-weight: bold; margin: 8px 0; }
        .rec-item { margin-bottom: 8px; padding: 6px 0; border-bottom: 1px dotted #ddd; }
        .rec-item strong { display: block; margin-bottom: 2px; }
        .disclaimer { margin-top: 18px; padding: 10px 12px; background: #fff8e7; border: 1px solid #f0d78c; font-size: 11px; }
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ASESMEN MINAT BAKAT</h1>
        <h2>{{ config('app.name') }}</h2>
    </div>

    <table class="info-table">
        <tr><td>Nama Siswa</td><td>: {{ $user?->name ?? $student?->name ?? '-' }}</td></tr>
        <tr><td>NISN</td><td>: {{ $student?->nisn ?? '-' }}</td></tr>
        <tr><td>Kelas</td><td>: {{ $student?->kelas?->nama ?? '-' }}</td></tr>
        <tr><td>Sekolah</td><td>: {{ $student?->kelas?->sekolah?->nama ?? $student?->school ?? '-' }}</td></tr>
        <tr><td>Jenjang asesmen</td><td>: {{ $submission->jenjang ?? '-' }}</td></tr>
        <tr><td>Tanggal asesmen</td><td>: {{ $submission->submitted_at?->format('d M Y H:i') ?? '-' }}</td></tr>
        <tr><td>Tanggal cetak</td><td>: {{ $tanggalCetak }}</td></tr>
    </table>

    <div class="section">
        <h3>Kode Minat</h3>
        <div class="kode">{{ $submission->kode_minat ?: $result->kode_minat }}</div>
        <p>
            Minat utama: {{ $result->result_label ?: '-' }}
            @if($result->is_tied)
                (skor seimbang dengan minat peringkat kedua)
            @endif
        </p>
    </div>

    <div class="section">
        <h3>Skor per Kategori Minat</h3>
        <table class="scores">
            <thead>
                <tr>
                    <th>Peringkat</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Skor</th>
                    <th>Maks</th>
                    <th>Persen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($result->rankedCategories as $index => $category)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $category['kode'] }}</td>
                        <td>{{ $category['nama'] }}</td>
                        <td>{{ $category['raw'] }}</td>
                        <td>{{ $category['max'] }}</td>
                        <td>{{ number_format((float) $category['persen'], 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>
            @if(strtoupper((string) $submission->jenjang) === 'SMK')
                Rekomendasi Bidang Karier
            @elseif(strtoupper((string) $submission->jenjang) === 'SMA')
                Rekomendasi Program Studi PCR
            @else
                Rekomendasi
            @endif
        </h3>

        @if(($recommendations['items'] ?? []) === [])
            <p>{{ $recommendations['empty_message'] ?? 'Belum ada rekomendasi.' }}</p>
        @else
            @foreach($recommendations['items'] as $item)
                <div class="rec-item">
                    <strong>
                        {{ $item['nama'] }}
                        @if(!empty($item['jenjang_pendidikan']))
                            ({{ $item['jenjang_pendidikan'] }})
                        @endif
                        @if(isset($item['job_zone']))
                            — Job Zone {{ $item['job_zone'] }}
                        @endif
                        — {{ $item['match_label'] }}
                    </strong>
                    @if(!empty($item['kode_tag']))
                        Tag minat: {{ $item['kode_tag'] }}<br>
                    @endif
                    {{ $item['deskripsi'] ?? '' }}
                </div>
            @endforeach
        @endif
    </div>

    <div class="disclaimer">
        Hasil ini gambaran kecenderungan minat, bukan keputusan akhir. Diskusikan dengan Guru BK.
    </div>

    <div class="footer">Dicetak pada {{ $tanggalCetak }}</div>
</body>
</html>
