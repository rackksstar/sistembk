<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_CATEGORY = 'gaya_belajar';

    private const NEW_CATEGORY = 'strategi_belajar';

    private const DEFAULT_OPTIONS = [
        ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
        ['label' => 'Tidak Sesuai', 'score' => 2],
        ['label' => 'Cukup Sesuai', 'score' => 3],
        ['label' => 'Sesuai', 'score' => 4],
        ['label' => 'Sangat Sesuai', 'score' => 5],
    ];

    private const SECTIONS = [
        1 => [ // Perencanaan Belajar
            'Saya mempunyai tujuan belajar yang ingin saya capai.',
            'Saya mempunyai cara atau metode belajar saya sendiri.',
            'Ketika belajar, saya membuat target yang ingin saya capai, misalnya menyelesaikan 10 soal latihan dalam 1 jam.',
            'Ketika belajar, saya membuat daftar (to-do list) apa saja yang harus saya kerjakan.',
            'Saya percaya bahwa saya mampu memperoleh nilai yang bagus di sekolah.',
            'Nilai yang saya dapatkan di sekolah biasanya sesuai dengan apa yang saya perkirakan.',
            'Saya tahu apa yang akan saya dapatkan di masa depan jika saya belajar dengan baik sekarang.',
            'Saya menyukai sebagian besar aktivitas pembelajaran dan tugas yang diberikan di sekolah.',
            'Tujuan saya belajar adalah untuk meningkatkan kemampuan saya, tidak hanya mengejar nilai.',
            'Bagi saya, memperoleh nilai yang lebih baik dari teman-teman saya tidak begitu penting.',
        ],
        2 => [ // Eksekusi Belajar
            'Saat mengerjakan soal Matematika, saya selalu mengikuti langkah-langkah penyelesainnya secara bertahap.',
            'Saat membaca sebuah bacaan, saya memastikan saya mengerti bacaan tersebut dengan menggarisbawahi bagian penting atau memastikan gagasan utamanya.',
            'Saya sering membuat mindmap, peta konsep, atau catatan/rangkuman untuk membantu memahami konsep yang dipelajari.',
            'Saya membuat jadwal belajar secara teratur untuk mengulang kembali materi dan mengerjakan tugas sekolah.',
            'Sebelum mulai belajar, saya memastikan lingkungan saya mendukung konsentrasi belajar saya.',
            'Ketika ada materi yang tidak saya mengerti, saya tahu harus bertanya apa dan kepada siapa.',
            'Ketika saya bosan, saya mencoba membuat tugas saya lebih menarik, misalnya dengan mengajak teman saya belajar bersama.',
            'Saya memberikan reward kepada diri saya saat belajar, misalnya saya boleh membuka media sosial setelah tugas saya selesai.',
            'Ketika saya mencoba sebuah cara belajar baru, saya memonitor seberapa tinggi nilai yang saya peroleh setelah menerapkan cara belajar tersebut.',
            'Saya membuat catatan konsep-konsep apa saja yang belum saya kuasai dan perlu saya pelajari kembali.',
        ],
        3 => [ // Refleksi Belajar
            'Menurut saya, pembelajaran saya berhasil jika nilai yang saya dapatkan bisa mencapai atau lebih dari target nilai yang sudah saya tentukan.',
            'Saya selalu memonitor perkembangan nilai saya dari waktu ke waktu.',
            'Ketika saya mendapat nilai yang kurang memuaskan, saya bisa mengidentifikasi apa penyebabnya.',
            'Ketika saya mendapat nilai yang kurang memuaskan, saya akan menurunkan target nilai saya untuk tugas/ulangan berikutnya.',
            'Ketika saya mendapat nilai yang kurang memuaskan, saya akan mengubah cara belajar saya.',
            'Saya merasa saya sebenarnya mampu, meskipun kadang nilai saya kurang memuaskan.',
            'Saya merasa puas dengan cara belajar saya saat ini.',
            'Ketika sebuah strategi belajar telah memberikan nilai yang memuaskan, maka saya akan melanjutkan strategi tersebut.',
            'Saya merasa jika saya belajar, maka nilai saya akan lebih bagus daripada jika saya tidak belajar.',
            'Saya jarang menunda-nunda belajar karena saya suka belajar.',
        ],
    ];

    public function up(): void
    {
        // Instrumen Gaya Belajar diganti total isinya oleh Strategi Belajar (revisi dospem),
        // jadi soal & submission lama untuk kategori ini dibuang, bukan diedit di tempat.
        $oldQuestionIds = DB::table('instrument_questions')->where('category', self::OLD_CATEGORY)->pluck('id');
        DB::table('instrument_answers')->whereIn('instrument_question_id', $oldQuestionIds)->delete();
        DB::table('instrument_questions')->where('category', self::OLD_CATEGORY)->delete();

        $oldSubmissionIds = DB::table('instrument_submissions')->where('category', self::OLD_CATEGORY)->pluck('id');
        DB::table('instrument_answers')->whereIn('instrument_submission_id', $oldSubmissionIds)->delete();
        DB::table('instrument_submissions')->where('category', self::OLD_CATEGORY)->delete();

        $now = now();

        foreach (self::SECTIONS as $section => $questions) {
            foreach ($questions as $questionText) {
                DB::table('instrument_questions')->insert([
                    'category' => self::NEW_CATEGORY,
                    'section' => $section,
                    'question' => $questionText,
                    'options' => json_encode(self::DEFAULT_OPTIONS),
                    'is_active' => true,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $newQuestionIds = DB::table('instrument_questions')->where('category', self::NEW_CATEGORY)->pluck('id');
        DB::table('instrument_answers')->whereIn('instrument_question_id', $newQuestionIds)->delete();
        DB::table('instrument_questions')->where('category', self::NEW_CATEGORY)->delete();

        $newSubmissionIds = DB::table('instrument_submissions')->where('category', self::NEW_CATEGORY)->pluck('id');
        DB::table('instrument_answers')->whereIn('instrument_submission_id', $newSubmissionIds)->delete();
        DB::table('instrument_submissions')->where('category', self::NEW_CATEGORY)->delete();
    }
};
