<?php

namespace Database\Seeders;

use App\Models\SociometryQuestion;
use Illuminate\Database\Seeder;

class SociometryQuestionSeeder extends Seeder
{
    /**
     * 8 soal sosiometri (4 positif "dipilih" + 4 negatif "ditolak").
     * Setiap soal punya aspek: pertemanan / organisasi / belajar / karier.
     */
    public function run(): void
    {
        $questions = [
            [SociometryQuestion::JENIS_POSITIF, SociometryQuestion::ASPEK_PERTEMANAN, 'Siapa teman di kelas yang paling dekat dengan Anda, seperti sahabat atau teman baik/akrab?'],
            [SociometryQuestion::JENIS_POSITIF, SociometryQuestion::ASPEK_ORGANISASI, 'Siapa teman yang Anda pilih untuk mengikuti kegiatan organisasi, ekstrakurikuler, atau kegiatan sosial bersama?'],
            [SociometryQuestion::JENIS_POSITIF, SociometryQuestion::ASPEK_BELAJAR, 'Siapa teman kelas yang paling Anda sukai untuk belajar bersama?'],
            [SociometryQuestion::JENIS_POSITIF, SociometryQuestion::ASPEK_KARIER, 'Siapa teman yang Anda pilih untuk berdiskusi tentang karier atau masa depan, seperti melanjutkan sekolah atau kuliah bersama?'],
            [SociometryQuestion::JENIS_NEGATIF, SociometryQuestion::ASPEK_PERTEMANAN, 'Siapa teman di kelas yang paling ingin Anda hindari atau jauhi dalam hubungan pertemanan?'],
            [SociometryQuestion::JENIS_NEGATIF, SociometryQuestion::ASPEK_ORGANISASI, 'Siapa teman yang paling ingin Anda hindari ketika mengikuti kegiatan organisasi, ekstrakurikuler, atau kegiatan sosial bersama?'],
            [SociometryQuestion::JENIS_NEGATIF, SociometryQuestion::ASPEK_BELAJAR, 'Siapa teman kelas yang paling tidak ingin Anda pilih untuk belajar bersama?'],
            [SociometryQuestion::JENIS_NEGATIF, SociometryQuestion::ASPEK_KARIER, 'Siapa teman yang paling tidak ingin Anda pilih untuk berdiskusi tentang karier atau masa depan, seperti melanjutkan sekolah atau kuliah bersama?'],
        ];

        foreach ($questions as $index => [$jenis, $aspek, $pertanyaan]) {
            SociometryQuestion::query()->updateOrCreate(
                ['pertanyaan' => $pertanyaan],
                [
                    'jenis' => $jenis,
                    'aspek' => $aspek,
                    'urutan' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
