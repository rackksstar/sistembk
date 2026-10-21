<?php

// Skrip pembuat fixture .xlsx untuk skenario E2E import siswa.
// Path project dihitung dari lokasi skrip agar portable (Windows/Linux).
//
// Pemakaian: php make-student-import-fixture.php <path-berkas> [nama-sekolah]
// Kelas yang dipakai diambil dari sekolah tersebut supaya file valid bagi
// cakupan guru BK (kelas dibatasi per sekolah) maupun admin (semua sekolah).

$root = dirname(__DIR__, 3);

require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Kelas;
use App\Models\Sekolah;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$path = $argv[1] ?? '/tmp/siswa-import-fixture.xlsx';
$namaSekolah = $argv[2] ?? 'SMA Negeri 1 Contoh';

@mkdir(dirname($path), 0777, true);

$sekolah = Sekolah::query()->where('nama', $namaSekolah)->first();

if (! $sekolah) {
    fwrite(STDERR, "Sekolah '{$namaSekolah}' tidak ditemukan.\n");
    exit(1);
}

$kelas = Kelas::query()
    ->where('sekolah_id', $sekolah->id)
    ->orderBy('id')
    ->get();

if ($kelas->isEmpty()) {
    fwrite(STDERR, "Tidak ada kelas pada sekolah '{$namaSekolah}'.\n");
    exit(1);
}

$k = $kelas->first();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Siswa');
$sheet->fromArray(['nama', 'nisn', 'tanggal_lahir', 'sekolah', 'kelas'], null, 'A1');

$ts = time();
$sheet->setCellValue('A2', 'Siswa Impor Xlsx '.$ts);
$sheet->setCellValue('B2', '8'.str_pad(substr((string) $ts, -9), 9, '0', STR_PAD_LEFT));

// Tanggal disimpan sebagai angka serial + format tanggal, persis seperti
// yang ditulis Excel — bukan string — supaya jalur pembacaan tanggal pada
// berkas .xlsx ikut teruji.
$sheet->getStyle('C2:C3')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
$sheet->setCellValue('C2', ExcelDate::PHPToExcel(new DateTimeImmutable('2008-09-15')));
$sheet->setCellValue('D2', $sekolah->nama);
$sheet->setCellValue('E2', sprintf('%s (%d)', $k->nama, $k->id));

// Baris 3: tanpa kelas -> harus dilaporkan dan dilewati
$sheet->setCellValue('A3', 'Siswa Tanpa Kelas Xlsx '.$ts);
$sheet->setCellValue('B3', '9'.str_pad(substr((string) ($ts + 1), -9), 9, '0', STR_PAD_LEFT));
$sheet->setCellValue('C3', ExcelDate::PHPToExcel(new DateTimeImmutable('2009-01-10')));
$sheet->setCellValue('D3', $sekolah->nama);

$writer = new Xlsx($spreadsheet);
$writer->save($path);

echo "OK $path\n";
