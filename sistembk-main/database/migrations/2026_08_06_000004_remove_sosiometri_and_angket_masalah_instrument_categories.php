<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sosiometri dan Angket Masalah sudah punya fitur tersendiri di luar
     * Instrumen Asesmen (menu Sosiometri & Angket BK), jadi soal/hasil
     * instrumen lama untuk dua kategori ini dibuang dari modul ini.
     */
    private const CATEGORIES = ['sosiometri', 'angket_masalah'];

    public function up(): void
    {
        $this->purge(self::CATEGORIES);
    }

    public function down(): void
    {
        // Konten soal lama tidak disimpan di migration ini (lihat DatabaseSeeder
        // sebelum revisi ini kalau perlu contoh soal), jadi down() hanya
        // membersihkan submission/jawaban yang mungkin tercipta setelah up().
        $this->purge(self::CATEGORIES);
    }

    private function purge(array $categories): void
    {
        $questionIds = DB::table('instrument_questions')->whereIn('category', $categories)->pluck('id');
        DB::table('instrument_answers')->whereIn('instrument_question_id', $questionIds)->delete();
        DB::table('instrument_questions')->whereIn('category', $categories)->delete();

        $submissionIds = DB::table('instrument_submissions')->whereIn('category', $categories)->pluck('id');
        DB::table('instrument_answers')->whereIn('instrument_submission_id', $submissionIds)->delete();
        DB::table('instrument_submissions')->whereIn('category', $categories)->delete();
    }
};
