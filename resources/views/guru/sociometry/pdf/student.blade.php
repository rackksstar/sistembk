<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sosiometri - {{ $student->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h2>Analisis Sosiometri - {{ $student->name }}</h2>
    <p>Total Masuk: {{ $totalInbound }}</p>
    <p>Total Keluar: {{ $totalOutbound }}</p>
    <p>Mutual: {{ $mutualCount }}</p>
    <p>Status: {{ $status }}</p>

    <h3>Pilihan Masuk</h3>
    <table>
        <thead><tr><th>Pemilih</th><th>Relasi</th><th>Alasan</th></tr></thead>
        <tbody>
            @foreach($inbound as $r)
                <tr>
                    <td>{{ $r->student->name ?? $r->student_id }}</td>
                    <td>{{ \App\Models\SociometryResponse::TYPES[$r->relation_type] ?? $r->relation_type }}</td>
                    <td>{{ $r->reason ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3 class="mt-4">Pilihan Keluar</h3>
    <table>
        <thead><tr><th>Dipilih</th><th>Relasi</th><th>Alasan</th></tr></thead>
        <tbody>
            @foreach($outbound as $r)
                <tr>
                    <td>{{ $r->chosenStudent->name ?? $r->chosen_student_id }}</td>
                    <td>{{ \App\Models\SociometryResponse::TYPES[$r->relation_type] ?? $r->relation_type }}</td>
                    <td>{{ $r->reason ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>