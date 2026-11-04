<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sosiometri - Rekap Kelas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h2>Sosiometri - Rekap {{ $kelas?->nama ?? 'Semua Kelas' }}</h2>

    <h3>Ringkasan Siswa</h3>
    <table>
        <thead>
            <tr><th>Nama</th><th>Status Isi</th></tr>
        </thead>
        <tbody>
            @foreach($students as $s)
                @php $uid = $s->user_id ?? $s->id; $filled = in_array($uid, $filledUserIds); @endphp
                <tr>
                    <td>{{ $s->name ?? ($s->user->name ?? '-') }}</td>
                    <td>{{ $filled ? 'Sudah Mengisi' : 'Belum Mengisi' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3 class="mt-4">Daftar Relasi</h3>
    <table>
        <thead>
            <tr><th>Pemilih</th><th>Dipilih</th><th>Relasi</th><th>Alasan</th></tr>
        </thead>
        <tbody>
            @foreach($responses as $r)
                <tr>
                    <td>{{ $r->student->name ?? $r->student_id }}</td>
                    <td>{{ $r->chosenStudent->name ?? $r->chosen_student_id }}</td>
                    <td>{{ \App\Models\SociometryResponse::TYPES[$r->relation_type] ?? $r->relation_type }}</td>
                    <td>{{ $r->reason ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>