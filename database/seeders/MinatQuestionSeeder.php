<?php

namespace Database\Seeders;

use App\Models\InstrumentQuestion;
use App\Models\InterestCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class MinatQuestionSeeder extends Seeder
{
    /**
     * 99 item Talents Mapping (kerangka RIASEC) dari referensi Key / dospem.
     * Tiap item ditandai kode R/I/A/S/E/C + interest_category_id untuk rekomendasi PCR/karier.
     */
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
            ['label' => 'Sangat Tidak Suka', 'score' => 1],
            ['label' => 'Tidak Suka', 'score' => 2],
            ['label' => 'Netral', 'score' => 3],
            ['label' => 'Suka', 'score' => 4],
            ['label' => 'Sangat Suka', 'score' => 5],
        ];

        $items = [
            ['Menggambar ilustrasi sesuatu/barang', 'A'],
            ['Menjaga agar tetap teratur/berurutan', 'C'],
            ['Memberikan arahan', 'E'],
            ['Memandu turis', 'S'],
            ['Menjadi pemimpin untuk membuat perusahaan lebih besar', 'E'],
            ['Membacakan buku cerita kepada anak-anak', 'S'],
            ['Merakit furnitur', 'R'],
            ['Mengatur keuangan', 'C'],
            ['Memperbaiki peralatan yang rusak', 'R'],
            ['Mengumpulkan fakta dan data', 'I'],
            ['Latihan kepemimpinan', 'E'],
            ['Mengambil foto', 'A'],
            ['Menghasilkan karya seni', 'A'],
            ['Memikirkan suatu hal dengan sempurna/hingga selesai', 'I'],
            ['Mendokumentasikan/mencatat hasil rapat', 'C'],
            ['Menolong orang lain', 'S'],
            ['Mengembangkan teknologi baru yang ramah lingkungan', 'I'],
            ['Menjadi pemilik tim olahraga', 'E'],
            ['Mengendarai kereta dan bis', 'R'],
            ['Memecahkan masalah yang sulit', 'I'],
            ['Mengatur karyawan yang bekerja di sebuah perusahaan', 'E'],
            ['Menemukan hal yang tidak disadari orang lain', 'I'],
            ['Memikirkan perjalanan karier siswa', 'S'],
            ['Menjelaskan penyebab bencana alam', 'I'],
            ['Memperbaiki mesin', 'R'],
            ['Mengatur buku sesuai urutan di rak buku', 'C'],
            ['Memberi petunjuk kepada anggota tim', 'E'],
            ['Mengekspresikan diri dengan kata-kata, musik, gambar, dsb.', 'A'],
            ['Menampilkan lirik dan komposisi', 'A'],
            ['Mengelola beberapa tempat makan', 'E'],
            ['Membuat dan mengolah data', 'C'],
            ['Mengatur dan menjaga dokumen dengan benar', 'C'],
            ['Desain pakaian', 'A'],
            ['Menentukan tujuan dan kebijakan dalam tim dan organisasi', 'E'],
            ['Desain kostum panggung', 'A'],
            ['Mencari tahu asal usul dan evolusi kehidupan', 'I'],
            ['Merakit model bangunan', 'R'],
            ['Menerbangkan pesawat terbang', 'R'],
            ['Membantu orang yang membutuhkan', 'S'],
            ['Merawat orang yang terluka', 'S'],
            ['Mencek apakah komputer bekerja dengan benar', 'R'],
            ['Berpikir tentang topik yang sulit', 'I'],
            ['Berbisnis dengan hasil yang dibeli di luar negeri', 'E'],
            ['Membuat aturan dan menyusun langkah kerja dengan baik', 'C'],
            ['Mengajar orang-orang', 'S'],
            ['Mendesain ruangan', 'A'],
            ['Mengatur pembukuan keuangan dan mengecek isinya', 'C'],
            ['Menjadi manajer dan membuka toko', 'E'],
            ['Menjelaskan sesuatu dengan melihat karya seni', 'A'],
            ['Menemukan substansi/zat baru yang belum diketahui sebelumnya', 'I'],
            ['Membongkar dan merakit mesin', 'R'],
            ['Mengendarai mobil atau motor', 'R'],
            ['Mendengarkan keluh kesah orang lain', 'S'],
            ['Mengatur buku rekening rumah tangga', 'C'],
            ['Menemukan peraturan dan hukum untuk suatu fenomena atau kejadian', 'I'],
            ['Menjual barang mahal', 'E'],
            ['Menghargai seni dan karya seni', 'A'],
            ['Merawat dan memperbaiki mobil', 'R'],
            ['Mengambil alih perusahaan asing', 'E'],
            ['Berpikir tentang kombinasi warna dan pola', 'A'],
            ['Menyelamatkan orang yang terkena bencana', 'S'],
            ['Merawat peralatan', 'R'],
            ['Merancang pola/sirkuit digital untuk produk IT', 'R'],
            ['Membuat barang-barang kerajinan seperti piring dan kain', 'A'],
            ['Berpikir tentang masalah kesejahteraan sosial', 'S'],
            ['Penasehat direktur dalam suatu perusahaan', 'E'],
            ['Melakukan sesuatu sesuai dengan petunjuk dan peraturan', 'C'],
            ['Mempelajari asal usul alam semesta dan bumi', 'I'],
            ['Menggunakan berbagai macam peralatan', 'R'],
            ['Berdebat dalam sebuah diskusi', 'E'],
            ['Mengobati penyakit pasien', 'S'],
            ['Desain pola kain', 'A'],
            ['Membuat teori dan ide baru', 'I'],
            ['Mengarahkan orang lain untuk mengikuti pendapat pribadi dalam sebuah diskusi', 'E'],
            ['Memeriksa kesalahan data', 'C'],
            ['Menghasilkan sesuatu yang orisinil', 'A'],
            ['Mempelajari apa yang ingin saya ketahui di sebuah institut penelitian, seperti universitas', 'I'],
            ['Negosiasi keputusan-keputusan besar', 'E'],
            ['Menggunakan mesin dan peralatan', 'R'],
            ['Mendengarkan petuah orang tua', 'S'],
            ['Mengarahkan panggung dan karya visual', 'A'],
            ['Memproses formulir aplikasi sesuai prosedur', 'C'],
            ['Mencari tahu misteri otak manusia', 'I'],
            ['Investigasi eksistensi dan evolusi pada manusia dan peradaban kuno', 'I'],
            ['Mempromosikan produk untuk dibeli', 'E'],
            ['Menemukan zat baru yang berguna di dunia', 'I'],
            ['Merawat orang tua dan orang cacat dengan ketidakmampuan fisik', 'S'],
            ['Berinvestasi saham', 'E'],
            ['Membuat film', 'A'],
            ['Melakukan sesuatu sesuai peraturan adalah hal yang ingin saya lakukan', 'C'],
            ['Memikirkan program TV dan memproduksi programnya', 'A'],
            ['Membuat jadwal yang tepat', 'C'],
            ['Membantu orang yang membutuhkan dengan suka rela', 'S'],
            ['Membuat keramik', 'A'],
            ['Mendesain pola/sirkuit elektronik', 'R'],
            ['Memberikan saran terhadap sesuatu secara akurat tanpa kesalahan', 'C'],
            ['Menyembuhkan penyakit mental', 'S'],
            ['Membuat barang pecah belah', 'A'],
            ['Mengatasi masalah', 'I'],
        ];

        $activeTexts = [];

        foreach ($items as [$questionText, $talentCode]) {
            $category = $categories->get($talentCode);
            if (! $category) {
                continue;
            }

            $activeTexts[] = $questionText;

            $question = InstrumentQuestion::withTrashed()->updateOrCreate(
                [
                    'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
                    'question' => $questionText,
                ],
                [
                    'interest_category_id' => $category->id,
                    'talent_code' => $talentCode,
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

        // Nonaktifkan soal minat lama yang bukan bagian Talents Mapping 99 item.
        InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)
            ->where(function ($query) use ($activeTexts) {
                $query->whereNotIn('question', $activeTexts)
                    ->orWhereNull('interest_category_id')
                    ->orWhereNull('talent_code');
            })
            ->update(['is_active' => false]);
    }
}
