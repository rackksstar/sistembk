<?php

namespace App\Services;

use App\Models\Student;
use App\Support\StudentSpreadsheet;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Proses impor data siswa dari CSV/XLSX untuk peran guru maupun admin.
 *
 * Perbedaan keduanya hanya pada daftar kelas yang diizinkan, sehingga logika
 * baris demi baris cukup ditulis sekali di sini.
 */
final class StudentImporter
{
    /**
     * @param  array<int, int>  $kelasDiizinkan  ID kelas yang boleh dipakai
     * @return array{created:int, updated:int, errors:array<int, string>}
     *
     * @throws ValidationException bila berkas tidak bisa dibaca sama sekali
     */
    public function run(string $path, array $kelasDiizinkan): array
    {
        try {
            $baris = StudentSpreadsheet::rows($path);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'import_file' => 'File tidak bisa dibaca. Pastikan berformat .csv atau .xlsx.',
            ]);
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        // Baris 1 adalah judul, jadi data pertama berada di baris 2.
        $nomorBaris = 1;

        foreach ($baris as $data) {
            $nomorBaris++;

            $nama = trim(StudentSpreadsheet::teks($data['nama'] ?? $data['name'] ?? null));
            $nisn = trim(StudentSpreadsheet::teks($data['nisn'] ?? null));
            $birthDate = $this->tanggal($data['tanggal_lahir'] ?? $data['birth_date'] ?? null);
            $school = trim(StudentSpreadsheet::teks($data['sekolah'] ?? $data['school'] ?? null)) ?: null;
            $kelasId = StudentSpreadsheet::kelasId($data['kelas'] ?? $data['kelas_id'] ?? null);

            if ($nama === '' || $nisn === '' || $birthDate === null) {
                $errors[] = "Baris {$nomorBaris}: nama, NISN, dan tanggal lahir wajib diisi.";
                continue;
            }

            if ($kelasId === null) {
                $errors[] = "Baris {$nomorBaris}: kelas_id wajib diisi.";
                continue;
            }

            if (! in_array($kelasId, $kelasDiizinkan, true)) {
                $errors[] = "Baris {$nomorBaris}: kelas {$kelasId} bukan kelas yang tersedia bagi Anda.";
                continue;
            }

            $student = Student::updateOrCreate(
                ['nisn' => $nisn],
                [
                    'name' => $nama,
                    'birth_date' => $birthDate,
                    'school' => $school,
                    'kelas_id' => $kelasId,
                ]
            );

            $student->wasRecentlyCreated ? $created++ : $updated++;
        }

        return ['created' => $created, 'updated' => $updated, 'errors' => $errors];
    }

    /**
     * Terima tanggal sebagai teks (dari CSV) maupun sebagai DateTime (dari
     * sel tanggal pada XLSX), kembalikan `Y-m-d` atau null bila tidak valid.
     */
    private function tanggal(mixed $nilai): ?string
    {
        if ($nilai instanceof \DateTimeInterface) {
            return Carbon::instance($nilai)->toDateString();
        }

        $teks = trim(StudentSpreadsheet::teks($nilai));

        if ($teks === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $teks)->toDateString();
            } catch (Throwable) {
                // Coba format berikutnya.
            }
        }

        return null;
    }
}
