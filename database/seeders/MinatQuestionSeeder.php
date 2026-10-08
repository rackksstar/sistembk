<?php

namespace Database\Seeders;

use App\Models\InstrumentQuestion;
use App\Models\InterestCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class MinatQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(InterestCategorySeeder::class);

        $categories = InterestCategory::query()
            ->whereIn('kode', ['R', 'I', 'A', 'S', 'E', 'C'])
            ->get()
            ->keyBy('kode');

        $createdBy = User::query()
            ->where('role', User::ROLE_GURU)
            ->where('status', User::STATUS_APPROVED)
            ->value('id')
            ?? User::query()->where('role', User::ROLE_ADMIN)->value('id');

        $options = [
            ['label' => 'Sangat tidak tertarik', 'score' => 0],
            ['label' => 'Tidak tertarik', 'score' => 1],
            ['label' => 'Netral', 'score' => 2],
            ['label' => 'Tertarik', 'score' => 3],
            ['label' => 'Sangat tertarik', 'score' => 4],
        ];

        $questionsByKode = [
            'R' => [
                'Seberapa tertarik kamu pada kegiatan membongkar atau merakit alat?',
                'Seberapa tertarik kamu pada kegiatan merangkai listrik atau robot?',
                'Seberapa tertarik kamu pada praktik kerja di bengkel atau laboratorium?',
                'Seberapa tertarik kamu pada kegiatan memperbaiki barang yang rusak?',
                'Seberapa tertarik kamu memahami cara kerja kendaraan, mesin, atau instalasi?',
            ],
            'I' => [
                'Seberapa tertarik kamu pada kegiatan mengutak-atik komputer atau gawai?',
                'Seberapa tertarik kamu membuat program atau website?',
                'Seberapa tertarik kamu mengolah data menjadi informasi yang berguna?',
                'Seberapa tertarik kamu mempelajari teknologi baru?',
                'Seberapa tertarik kamu mencari solusi masalah dengan analisis?',
            ],
            'A' => [
                'Seberapa tertarik kamu pada kegiatan menggambar atau mendesain?',
                'Seberapa tertarik kamu membuat video, foto, atau konten kreatif?',
                'Seberapa tertarik kamu mengembangkan ide orisinal?',
                'Seberapa tertarik kamu menulis cerita atau naskah?',
                'Seberapa tertarik kamu memperhatikan estetika warna dan tampilan?',
            ],
            'S' => [
                'Seberapa tertarik kamu mendengarkan dan membantu teman?',
                'Seberapa tertarik kamu pada kegiatan mengajar atau menjelaskan materi?',
                'Seberapa tertarik kamu terlibat dalam organisasi?',
                'Seberapa tertarik kamu mengikuti kegiatan sosial?',
                'Seberapa tertarik kamu memahami perasaan orang lain?',
            ],
            'E' => [
                'Seberapa tertarik kamu berjualan atau menjalankan usaha kecil?',
                'Seberapa tertarik kamu memimpin orang dan menyusun strategi?',
                'Seberapa tertarik kamu pada kegiatan negosiasi?',
                'Seberapa tertarik kamu mengikuti tren pasar?',
                'Seberapa tertarik kamu mengambil peluang dan risiko?',
            ],
            'C' => [
                'Seberapa tertarik kamu merapikan data atau arsip?',
                'Seberapa tertarik kamu mengikuti prosedur secara tertib?',
                'Seberapa tertarik kamu mengecek angka atau laporan?',
                'Seberapa tertarik kamu menyusun jadwal secara rapi?',
                'Seberapa tertarik kamu mengelola spreadsheet atau data tabular?',
            ],
        ];

        foreach ($questionsByKode as $kode => $questions) {
            $category = $categories->get($kode);

            if (! $category) {
                continue;
            }

            foreach ($questions as $questionText) {
                $question = InstrumentQuestion::withTrashed()->updateOrCreate(
                    [
                        'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
                        'question' => $questionText,
                    ],
                    [
                        'interest_category_id' => $category->id,
                        'jenjang_target' => 'semua',
                        'bobot' => 1,
                        'options' => $options,
                        'is_active' => true,
                        'created_by' => $createdBy,
                    ]
                );

                if ($question->trashed()) {
                    $question->restore();
                }
            }
        }

        // Soal minat lama (tanpa kategori RIASEC) jangan ikut tampil di asesmen siswa.
        InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)
            ->whereNull('interest_category_id')
            ->update(['is_active' => false]);
    }
}
