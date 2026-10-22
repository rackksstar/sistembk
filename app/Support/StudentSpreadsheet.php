<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Template Excel untuk import siswa, lengkap dengan dropdown kelas.
 *
 * Kolom `kelas` berisi daftar pilihan berbentuk "Nama Kelas (ID)". Nilai yang
 * tersimpan di sel tetap berupa teks itu, lalu saat import ID-nya diambil dari
 * angka di dalam kurung. Dengan begitu guru melihat nama kelas sekaligus ID-nya
 * tanpa perlu menghafal, dan hasilnya tetap langsung valid sebagai `kelas_id`.
 */
final class StudentSpreadsheet
{
    public const SHEET_DATA = 'Data Siswa';

    public const SHEET_DAFTAR = 'Daftar Kelas';

    /**
     * Jumlah baris data yang diberi dropdown. Cukup longgar untuk satu angkatan
     * penuh tanpa membuat berkas membengkak.
     */
    public const BARIS_DIDROPDOWN = 1000;

    /** @var array<int, string> */
    public const KOLOM = ['nama', 'nisn', 'tanggal_lahir', 'sekolah', 'kelas'];

    /**
     * Bangun berkas template: baris judul, format kolom, dan dropdown kelas.
     *
     * @param  array<int, string>  $labelKelas  daftar label "Nama Kelas (ID)"
     */
    public static function template(array $labelKelas): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $data = $spreadsheet->getActiveSheet();
        $data->setTitle(self::SHEET_DATA);

        $data->fromArray(self::KOLOM, null, 'A1');
        $data->getStyle('A1:E1')->getFont()->setBold(true);

        // NISN diberi format teks supaya angka 0 di depan tidak hilang saat
        // diketik di Excel.
        $data->getStyle('B2:B'.self::BARIS_DIDROPDOWN)
            ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Tanggal lahir memakai format tanggal agar muncul date picker.
        $data->getStyle('C2:C'.self::BARIS_DIDROPDOWN)
            ->getNumberFormat()->setFormatCode('dd/mm/yyyy');

        $lebar = ['A' => 28, 'B' => 16, 'C' => 16, 'D' => 28, 'E' => 24];
        foreach ($lebar as $kolom => $ukuran) {
            $data->getColumnDimension($kolom)->setWidth($ukuran);
        }

        $daftar = $spreadsheet->createSheet();
        $daftar->setTitle(self::SHEET_DAFTAR);
        $daftar->fromArray(array_merge([['Kelas']], array_map(fn ($label) => [$label], $labelKelas)), null, 'A1');
        $daftar->getColumnDimension('A')->setWidth(28);

        // Daftar disimpan di sheet tersembunyi: batas 255 karakter pada daftar
        // inline membuatnya cepat penuh begitu jumlah kelas bertambah.
        $daftar->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $validasi = new DataValidation();
        $validasi->setType(DataValidation::TYPE_LIST)
            ->setFormula1(sprintf("='%s'!\$A\$2:\$A\$%d", self::SHEET_DAFTAR, count($labelKelas) + 1))
            ->setAllowBlank(true)
            // `showDropDown` justru berarti "sembunyikan panah" pada format
            // berkas Excel, sehingga false inilah yang membuat panah tampil.
            ->setShowDropDown(false)
            ->setShowInputMessage(true)
            ->setPromptTitle('Kelas')
            ->setPrompt('Pilih kelas dari daftar. Angka ID kelas juga diterima.')
            ->setShowErrorMessage(true)
            ->setErrorStyle(DataValidation::STYLE_WARNING)
            ->setErrorTitle('Kelas tidak dikenal')
            ->setError('Silakan pilih kelas dari daftar yang tersedia.');

        $data->setDataValidation('E2:E'.self::BARIS_DIDROPDOWN, $validasi);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * @param  array<int, string>  $labelKelas
     */
    public static function download(array $labelKelas, string $namaBerkas): StreamedResponse
    {
        $writer = new Xlsx(self::template($labelKelas));

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            $namaBerkas,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Pragma' => 'public',
                'Cache-Control' => 'max-age=0',
            ],
        );
    }

    /**
     * Baca baris data dari CSV maupun XLSX. Baris pertama dianggap judul.
     *
     * Nilai sel dikembalikan apa adanya (string, angka, atau objek tanggal).
     * Sel tanggal pada XLSX tersimpan sebagai angka serial Excel, jadi
     * diterjemahkan lebih dulu menjadi objek tanggal.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function rows(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        $jumlahKolom = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $jumlahBaris = $sheet->getHighestRow();

        // Baris pertama berisi judul kolom.
        $judul = [];

        for ($kolom = 1; $kolom <= $jumlahKolom; $kolom++) {
            $judul[] = self::teks($sheet->getCell([$kolom, 1])->getValue());
        }

        $judul = array_map(
            fn ($nilai) => Str::of($nilai)->lower()->trim()->replace(' ', '_')->toString(),
            $judul
        );

        $lebar = count($judul);
        $baris = [];

        for ($barisKe = 2; $barisKe <= $jumlahBaris; $barisKe++) {
            $sel = [];

            for ($kolom = 1; $kolom <= $lebar; $kolom++) {
                $sel[] = self::nilaiSel($sheet->getCell([$kolom, $barisKe]));
            }

            // Excel kerap menyisakan baris kosong di akhir berkas hasil simpan.
            if (array_filter($sel, fn ($isi) => self::teks($isi) !== '') === []) {
                continue;
            }

            $baris[] = array_combine($judul, $sel);
        }

        return $baris;
    }

    /**
     * Nilai sel dinormalisasi.
     *
     * Sel bertanggal pada XLSX menyimpan angka serial (mis. 39706 untuk
     * 15 September 2008). Angka seperti itu hanya dibaca sebagai tanggal bila
     * format kolomnya memang format tanggal, sehingga NISN bertipe angka
     * tidak ikut diterjemahkan.
     */
    private static function nilaiSel(Cell $cell): mixed
    {
        $nilai = $cell->getValue();

        if ($nilai === null || is_bool($nilai) || ! is_numeric($nilai)) {
            return $nilai;
        }

        try {
            if (! ExcelDate::isDateTime($cell, $nilai)) {
                return $nilai;
            }

            return ExcelDate::excelToDateTimeObject($nilai);
        } catch (Throwable) {
            return $nilai;
        }
    }

    /**
     * Ambil ID kelas dari isi sel: menerima "12", "XII IPA 1 (12)", atau "XII IPA 1 - 12".
     */
    public static function kelasId(mixed $nilai): ?int
    {
        $teks = trim(self::teks($nilai));

        if ($teks === '') {
            return null;
        }

        if (preg_match('/\((\d+)\)\s*$/', $teks, $cocok) === 1) {
            return (int) $cocok[1];
        }

        if (preg_match('/^\d+$/', $teks) === 1) {
            return (int) $teks;
        }

        return null;
    }

    /**
     * @param  iterable<int, object{id:int, nama:string}>  $kelas
     * @return array<int, string>
     */
    public static function label(iterable $kelas): array
    {
        $label = [];

        foreach ($kelas as $item) {
            $label[] = sprintf('%s (%d)', $item->nama, $item->id);
        }

        return $label;
    }

    /**
     * Ubah nilai sel apa pun menjadi string yang aman diproses.
     */
    public static function teks(mixed $nilai): string
    {
        if ($nilai instanceof DateTimeInterface) {
            return $nilai->format('Y-m-d');
        }

        if (is_bool($nilai)) {
            return $nilai ? '1' : '';
        }

        if (is_float($nilai) && floor($nilai) === $nilai) {
            return (string) (int) $nilai;
        }

        return (string) ($nilai ?? '');
    }
}
