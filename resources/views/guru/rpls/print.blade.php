{{--
    Template cetak PDF untuk RPL (dirender lewat dompdf, lihat RplController@print).
    Data ($rpl, $schoolName, $schoolAddress, $logoDataUri) dikirim dari controller.

    Formulir baku di bawah (judul, "Nama konseli" disamarkan, "Pertemuan ke-", dst)
    mengikuti format dokumen resmi RPL kelompok. Untuk RPL individu beberapa field
    tampil "-"/inisial karena memang tidak relevan. Bagian Tujuan/Materi/Metode/
    Evaluasi ditambahkan setelah tabel supaya isi RPL tetap tercetak lengkap.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RPL - {{ $rpl->title }}</title>
    <style>
        @page {
            margin: 12mm 10mm 12mm;
        }

        body {
            color: #000;
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            line-height: 1.28;
            margin: 0;
        }

        .sheet {
            border: 1px solid #333;
            padding: 14px 14px 16px;
        }

        .letterhead {
            text-align: center;
            padding-bottom: 6px;
        }

        .letterhead img {
            display: block;
            height: 44px;
            width: auto;
            margin: 0 auto 6px;
        }

        .school-name {
            font-size: 14px;
            font-weight: 700;
            text-align: center;
        }

        .school-address {
            font-size: 11px;
            margin-top: 2px;
            text-align: center;
        }

        .header-line {
            border-top: 1px solid #333;
            margin-top: 4px;
        }

        .title {
            text-align: center;
            margin: 4px 0 12px;
        }

        .title .main {
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .title .sub {
            margin-top: 2px;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .title .meta {
            margin-top: 2px;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .fields {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .fields td {
            padding: 1px 0;
            vertical-align: top;
        }

        td.no {
            width: 20px;
            padding-right: 2px;
        }

        td.label {
            width: 160px;
            padding-right: 6px;
        }

        td.sep {
            width: 10px;
            text-align: center;
        }

        td.fill {
            padding-left: 2px;
            padding-right: 2px;
        }

        .line {
            display: inline-block;
            width: 100%;
            border-bottom: 1px dotted #000;
            min-height: 14px;
            line-height: 14px;
            padding-bottom: 1px;
            word-break: break-word;
            overflow-wrap: anywhere;
            max-width: 100%;
        }

        .line.short {
            width: 180px;
        }

        .line.medium {
            width: 220px;
        }

        .line.long {
            width: 100%;
        }

        td.note {
            padding-left: 4px;
            font-size: 11px;
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
            max-width: 240px;
        }

        .narrative {
            margin-top: 12px;
        }

        .narrative h2 {
            font-size: 12px;
            font-weight: 700;
            margin: 0 0 2px;
            text-transform: uppercase;
        }

        .narrative p {
            margin: 0 0 8px;
            white-space: pre-line;
            text-align: justify;
        }

        .date-line {
            width: 100%;
            margin-top: 16px;
            text-align: right;
        }

        .date-line span {
            display: inline-block;
            min-width: 280px;
            padding-top: 2px;
            border-top: 1px dotted #000;
        }

        table.signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 6px;
        }

        .signature-space {
            height: 52px;
        }

        .signature-line {
            margin-top: 10px;
            border-top: 1px dotted #000;
            padding-top: 4px;
            min-height: 16px;
        }

        .confidential {
            margin-top: 10px;
            font-size: 12px;
        }

        .confidential .label {
            font-weight: 700;
        }

        .confidential .value {
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="letterhead">
            {{-- Logo dikirim sebagai data URI base64 (bukan URL file) supaya bisa dirender dompdf. --}}
            @if (! empty($logoDataUri))
                <img src="{{ $logoDataUri }}" alt="Logo Sekolah">
            @endif
            <div class="school-name">{{ $schoolName }}</div>
            <div class="school-address">{{ $schoolAddress }}</div>
            <div class="header-line"></div>
        </div>

        <div class="title">
            <div class="main">RENCANA PELAKSANAAN LAYANAN</div>
            <div class="sub">{{ $rpl->type === \App\Models\Rpl::TYPE_INDIVIDU ? 'BIMBINGAN KONSELING INDIVIDU' : 'KONSELING KELOMPOK' }}</div>
            <div class="meta">
                @php
                    $semester = (int) ($rpl->semester ?? 1);
                    $year = (int) ($rpl->year ?? now()->year);
                @endphp
                SEMESTER {{ $semester }}
                @if ($semester === 1)
                    (GANJIL)
                @elseif ($semester === 2)
                    (GENAP)
                @endif
                TAHUN PELAJARAN {{ $year }}
            </div>
        </div>

        <table class="fields">
            <tr>
                <td class="no">1.</td>
                <td class="label">Nama konseli</td>
                <td class="sep">:</td>
                <td class="fill"><span class="line long">{{ $rpl->printableClientNames() }}</span></td>
                <td class="note">(nama anggota konseling disamarkan)</td>
            </tr>
            <tr>
                <td class="no">2.</td>
                <td class="label">Hari, tanggal</td>
                <td class="sep">:</td>
                <td class="fill" colspan="2"><span class="line long">{{ $rpl->dayDateLabel() }}</span></td>
            </tr>
            <tr>
                <td class="no">3.</td>
                <td class="label">Pertemuan ke-</td>
                <td class="sep">:</td>
                <td class="fill" colspan="2"><span class="line short">{{ $rpl->meeting_number ?? '-' }}</span></td>
            </tr>
            <tr>
                <td class="no">4.</td>
                <td class="label">Waktu</td>
                <td class="sep">:</td>
                <td class="fill"><span class="line short">{{ $rpl->duration_minutes ? $rpl->duration_minutes.' menit' : '-' }}</span></td>
                <td class="note">(ditulis berapa menit waktu yang dipergunakan)</td>
            </tr>
            <tr>
                <td class="no">5.</td>
                <td class="label">Tempat</td>
                <td class="sep">:</td>
                <td class="fill"><span class="line medium">{{ $rpl->location ?: '-' }}</span></td>
                <td class="note">(ditulis lokasi pelaksanaannya)</td>
            </tr>
            <tr>
                <td class="no">6.</td>
                <td class="label">Topik permasalahan</td>
                <td class="sep">:</td>
                <td class="fill" colspan="2"><span class="line long">{{ $rpl->topik_permasalahan ?: ($rpl->type === \App\Models\Rpl::TYPE_KELOMPOK ? $rpl->title : '-') }}</span></td>
            </tr>
            <tr>
                <td class="no">7.</td>
                <td class="label">Media yang diperlukan</td>
                <td class="sep">:</td>
                <td class="fill" colspan="2"><span class="line long">{{ $rpl->media ?: '-' }}</span></td>
            </tr>
        </table>

        <div class="narrative">
            <h2>Tujuan</h2>
            <p>{{ $rpl->tujuan }}</p>
            <h2>Materi</h2>
            <p>{{ $rpl->materi }}</p>
            <h2>Metode</h2>
            <p>{{ $rpl->metode }}</p>
            <h2>Evaluasi</h2>
            <p>{{ $rpl->evaluasi }}</p>
        </div>

        <div class="date-line">
            <span>.......................,..........................</span>
        </div>

        <table class="signatures">
            <tr>
                <td>
                    <div>Mengetahui</div>
                    <div>Kepala Sekolah,</div>
                    <div class="signature-space"></div>
                    <div class="signature-line">.......................................</div>
                </td>
                <td>
                    <div>&nbsp;</div>
                    <div>Guru BK/ Konselor</div>
                    <div class="signature-space"></div>
                    <div class="signature-line">.......................................</div>
                </td>
            </tr>
        </table>

        <div class="confidential">
            <div class="label">Keterangan ;</div>
            <div class="value">Dokumen ini bersifat rahasia</div>
        </div>
    </div>
</body>
</html>
