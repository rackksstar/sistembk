<?php

namespace Database\Seeders;

use App\Models\MasterQuestion;
use Illuminate\Database\Seeder;

/**
 * Soal angket & tryout agar modul tidak kosong setelah seed.
 * Mencakup skala, pilihan ganda, dan isian.
 */
class MasterQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            // --- Angket ---
            [
                'kategori' => MasterQuestion::KATEGORI_ANGKET,
                'teks_pertanyaan' => 'Seberapa puas kamu dengan layanan bimbingan konseling di sekolah sejauh ini?',
                'tipe_input' => MasterQuestion::TIPE_SKALA,
                'opsi' => null,
                'kunci_jawaban' => null,
            ],
            [
                'kategori' => MasterQuestion::KATEGORI_ANGKET,
                'teks_pertanyaan' => 'Apakah kamu merasa memiliki tempat yang aman untuk bercerita tentang masalah pribadi di sekolah?',
                'tipe_input' => MasterQuestion::TIPE_PILIHAN_GANDA,
                'opsi' => ['Ya, sudah cukup', 'Kadang-kadang', 'Belum sama sekali', 'Tidak tahu'],
                'kunci_jawaban' => null,
            ],
            [
                'kategori' => MasterQuestion::KATEGORI_ANGKET,
                'teks_pertanyaan' => 'Hal apa yang paling ingin kamu diskusikan dengan Guru BK saat ini?',
                'tipe_input' => MasterQuestion::TIPE_ISIAN,
                'opsi' => null,
                'kunci_jawaban' => null,
            ],
            [
                'kategori' => MasterQuestion::KATEGORI_ANGKET,
                'teks_pertanyaan' => 'Seberapa yakin kamu dengan rencana studi/karier setelah lulus?',
                'tipe_input' => MasterQuestion::TIPE_SKALA,
                'opsi' => null,
                'kunci_jawaban' => null,
            ],
            [
                'kategori' => MasterQuestion::KATEGORI_ANGKET,
                'teks_pertanyaan' => 'Menurutmu, topik layanan BK mana yang paling dibutuhkan siswa tahun ini?',
                'tipe_input' => MasterQuestion::TIPE_PILIHAN_GANDA,
                'opsi' => ['Belajar & motivasi', 'Karier & kuliah', 'Pribadi & sosial', 'Kedisiplinan'],
                'kunci_jawaban' => null,
            ],
            [
                'kategori' => MasterQuestion::KATEGORI_ANGKET,
                'teks_pertanyaan' => 'Sebutkan satu kebiasaan belajar yang ingin kamu perbaiki bulan ini.',
                'tipe_input' => MasterQuestion::TIPE_ISIAN,
                'opsi' => null,
                'kunci_jawaban' => null,
            ],

            // --- Tryout ---
            [
                'kategori' => MasterQuestion::KATEGORI_TRYOUT,
                'teks_pertanyaan' => 'Seberapa siap kamu menghadapi ujian/tryout minggu ini?',
                'tipe_input' => MasterQuestion::TIPE_SKALA,
                'opsi' => null,
                'kunci_jawaban' => null,
            ],
            [
                'kategori' => MasterQuestion::KATEGORI_TRYOUT,
                'teks_pertanyaan' => 'Ibukota Provinsi Riau adalah…',
                'tipe_input' => MasterQuestion::TIPE_PILIHAN_GANDA,
                'opsi' => ['Dumai', 'Pekanbaru', 'Bengkalis', 'Siak'],
                'kunci_jawaban' => 'Pekanbaru',
            ],
            [
                'kategori' => MasterQuestion::KATEGORI_TRYOUT,
                'teks_pertanyaan' => 'Politeknik Caltex Riau dikenal sebagai kampus vokasi. Apa arti pendidikan vokasi menurutmu?',
                'tipe_input' => MasterQuestion::TIPE_ISIAN,
                'opsi' => null,
                'kunci_jawaban' => null,
            ],
            [
                'kategori' => MasterQuestion::KATEGORI_TRYOUT,
                'teks_pertanyaan' => 'Pada jalur PSUD PCR, siswa IPS umumnya bisa memilih prodi…',
                'tipe_input' => MasterQuestion::TIPE_PILIHAN_GANDA,
                'opsi' => [
                    'Semua prodi PCR',
                    'Akuntansi Perpajakan dan Sistem Informasi',
                    'Hanya Teknik Informatika',
                    'Tidak boleh mendaftar',
                ],
                'kunci_jawaban' => 'Akuntansi Perpajakan dan Sistem Informasi',
            ],
            [
                'kategori' => MasterQuestion::KATEGORI_TRYOUT,
                'teks_pertanyaan' => 'Seberapa paham kamu tentang perbedaan jalur PSUD, UMPCR, dan Ujian Mandiri di PCR?',
                'tipe_input' => MasterQuestion::TIPE_SKALA,
                'opsi' => null,
                'kunci_jawaban' => null,
            ],
            [
                'kategori' => MasterQuestion::KATEGORI_TRYOUT,
                'teks_pertanyaan' => 'Tuliskan satu alasan singkat mengapa kamu tertarik (atau belum) melanjutkan kuliah di PCR.',
                'tipe_input' => MasterQuestion::TIPE_ISIAN,
                'opsi' => null,
                'kunci_jawaban' => null,
            ],
            [
                'kategori' => MasterQuestion::KATEGORI_TRYOUT,
                'teks_pertanyaan' => 'Nilai rata-rata rapor minimal untuk jalur PSUD PCR adalah…',
                'tipe_input' => MasterQuestion::TIPE_PILIHAN_GANDA,
                'opsi' => ['7,0 / 70', '7,5 / 75', '8,0 / 80', '9,0 / 90'],
                'kunci_jawaban' => '8,0 / 80',
            ],
        ];

        foreach ($questions as $question) {
            MasterQuestion::query()->updateOrCreate(
                [
                    'kategori' => $question['kategori'],
                    'teks_pertanyaan' => $question['teks_pertanyaan'],
                ],
                [
                    'tipe_input' => $question['tipe_input'],
                    'opsi' => $question['opsi'],
                    'kunci_jawaban' => $question['kunci_jawaban'],
                    'is_active' => true,
                ]
            );
        }
    }
}
