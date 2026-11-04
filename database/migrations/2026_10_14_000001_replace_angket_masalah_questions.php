<?php

use App\Models\InstrumentQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bank soal Asesmen Masalah: 50 pernyataan, 4 bidang.
     * 1-20 Pribadi, 21-31 Sosial, 32-43 Belajar, 44-50 Karir.
     */
    public const QUESTIONS = [
        // Bidang 1 — Pribadi (1-20)
        ['section' => 1, 'question' => 'Saya merasa belum disiplin dalam beribadah pada Tuhan YME.'],
        ['section' => 1, 'question' => 'Saya kadang-kadang berperilaku dan bertutur kata tidak jujur.'],
        ['section' => 1, 'question' => 'Saya merasa bersalah bila meninggalkan ibadah.'],
        ['section' => 1, 'question' => 'Saya merasa belum bisa mengendalikan emosi dengan baik.'],
        ['section' => 1, 'question' => 'Saya belum paham tentang sikap dan perilaku asertif.'],
        ['section' => 1, 'question' => 'Saya belum tahu cara mengenal dan memahami diri sendiri.'],
        ['section' => 1, 'question' => 'Saya belum bisa menerima kritikan.'],
        ['section' => 1, 'question' => 'Saya tidak mengakui kesalahan yang saya perbuat.'],
        ['section' => 1, 'question' => 'Saya tidak bisa mengatasi masalah yang saya alami.'],
        ['section' => 1, 'question' => 'Saya kurang memiliki rasa percaya diri.'],
        ['section' => 1, 'question' => 'Saya kadang kurang menjaga kesehatan diri.'],
        ['section' => 1, 'question' => 'Saya belum mengetahui gejala-gejala gangguan mental.'],
        ['section' => 1, 'question' => 'Saya merasa kurang memiliki tanggung jawab pada diri sendiri.'],
        ['section' => 1, 'question' => 'Saya kesulitan mengatur waktu belajar dan bermain.'],
        ['section' => 1, 'question' => 'Kondisi orang tua saya sedang tidak harmonis.'],
        ['section' => 1, 'question' => 'Saya merasa tidak betah tinggal di rumah sendiri.'],
        ['section' => 1, 'question' => 'Saya mempunyai masalah dengan anggota keluarga di rumah.'],
        ['section' => 1, 'question' => 'Saya belum bisa menjadi pribadi yang mandiri.'],
        ['section' => 1, 'question' => 'Saya sedang memiliki konflik pribadi.'],
        ['section' => 1, 'question' => 'Saya belum memahami norma cara membangun keluarga.'],
        // Bidang 2 — Sosial (21-31)
        ['section' => 2, 'question' => 'Saya belum mengenali lingkungan sekolah baru.'],
        ['section' => 2, 'question' => 'Saya belum mengetahui tentang dampak atau bahaya rokok.'],
        ['section' => 2, 'question' => 'Saya belum banyak mengenal tentang perilaku sosial yang bertanggung jawab.'],
        ['section' => 2, 'question' => 'Saya membutuhkan edukasi tentang seks, pacaran dan perkawinan.'],
        ['section' => 2, 'question' => 'Saya belum tahu tentang bullying dan cara menyikapinya.'],
        ['section' => 2, 'question' => 'Saya sukar bergaul dengan teman-teman di sekolah.'],
        ['section' => 2, 'question' => 'Saya dianggap tidak sopan pada orang lain.'],
        ['section' => 2, 'question' => 'Saya kurang memahami dampak dan etika sosial.'],
        ['section' => 2, 'question' => 'Saya jarang bermain/berteman di lingkungan tempat saya tinggal.'],
        ['section' => 2, 'question' => 'Saya lebih banyak berinteraksi di media sosial dibanding interaksi langsung.'],
        ['section' => 2, 'question' => 'Saya kurang suka berkomunikasi dengan teman lawan jenis.'],
        // Bidang 3 — Belajar (32-43)
        ['section' => 3, 'question' => 'Saya belum tahu cara belajar yang baik dan benar.'],
        ['section' => 3, 'question' => 'Saya lebih sering menggunakan HP untuk bermain daripada belajar.'],
        ['section' => 3, 'question' => 'Saya belum paham tentang gaya belajar dan strategi yang sesuai dengan saya.'],
        ['section' => 3, 'question' => 'Orang tua saya tidak peduli dengan kegiatan belajar saya.'],
        ['section' => 3, 'question' => 'Saya masih sering menunda-nunda tugas sekolah/pekerjaan rumah (PR).'],
        ['section' => 3, 'question' => 'Saya merasa kesulitan dalam memahami pelajaran tertentu.'],
        ['section' => 3, 'question' => 'Saya belum tahu cara memanfaatkan sumber belajar.'],
        ['section' => 3, 'question' => 'Saya belajarnya jika akan ada tes atau ujian saja.'],
        ['section' => 3, 'question' => 'Saya malas belajar karena fasilitas tidak memadai.'],
        ['section' => 3, 'question' => 'Saya sering mengantuk saat belajar.'],
        ['section' => 3, 'question' => 'Saya belum terbiasa belajar bersama atau belajar kelompok.'],
        ['section' => 3, 'question' => 'Saya belum dapat memanfaatkan teknologi informasi untuk belajar.'],
        // Bidang 4 — Karir (44-50)
        ['section' => 4, 'question' => 'Saya menyukai kegiatan yang berhubungan dengan kewirausahaan.'],
        ['section' => 4, 'question' => 'Saya belum tahu cara memperoleh bantuan pendidikan (beasiswa).'],
        ['section' => 4, 'question' => 'Saya terpaksa harus bekerja untuk mencukupi kebutuhan hidup.'],
        ['section' => 4, 'question' => 'Saya merasa bingung memilih kegiatan ekstrakurikuler di sekolah.'],
        ['section' => 4, 'question' => 'Saya merasa belum mantap pada pilihan peminatan yang diambil.'],
        ['section' => 4, 'question' => 'Saya merasa belum paham hubungan antara hobi, bakat, minat, kemampuan dan karir.'],
        ['section' => 4, 'question' => 'Saya merasa belum mantap pada pilihan jurusan yang diambil.'],
    ];

    public function up(): void
    {
        $defaultOptions = [
            ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
            ['label' => 'Tidak Sesuai', 'score' => 2],
            ['label' => 'Cukup Sesuai', 'score' => 3],
            ['label' => 'Sesuai', 'score' => 4],
            ['label' => 'Sangat Sesuai', 'score' => 5],
        ];

        // Nonaktifkan soal lama yang tidak ada di bank baru (aman: soft-delete
        // bila sudah pernah dijawab, force-delete bila belum).
        $newTexts = collect(self::QUESTIONS)->pluck('question')->all();
        $legacy = InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_ANGKET_MASALAH)
            ->whereNotIn('question', $newTexts)
            ->get();

        foreach ($legacy as $question) {
            if ($question->answers()->exists()) {
                $question->update(['is_active' => false]);
            } else {
                $question->forceDelete();
            }
        }

        // Masukkan 50 soal baru secara idempotent.
        foreach (self::QUESTIONS as $item) {
            InstrumentQuestion::query()->updateOrCreate(
                [
                    'category' => InstrumentQuestion::CATEGORY_ANGKET_MASALAH,
                    'question' => $item['question'],
                ],
                [
                    'section' => $item['section'],
                    'options' => $defaultOptions,
                    'is_active' => true,
                    'jenjang_target' => 'semua',
                    'deleted_at' => null,
                ]
            );
        }

        // Pastikan urutan tampil = nomor 1-50 (id berurutan per section).
        // Tidak mengubah id; tampilan diurutkan section + id di controller.
        DB::table('instrument_questions')
            ->where('category', InstrumentQuestion::CATEGORY_ANGKET_MASALAH)
            ->whereNull('section')
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        // Tidak menghapus jawaban siswa; hanya nonaktifkan bank 50 soal.
        InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_ANGKET_MASALAH)
            ->whereIn('question', collect(self::QUESTIONS)->pluck('question')->all())
            ->update(['is_active' => false]);
    }
};
