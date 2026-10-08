<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Instrumen Kepribadian (MBTI) format baru ala 16Personalities: setiap soal
 * adalah satu pernyataan yang dinilai dengan skala Likert persetujuan
 * (1-5, sama seperti instrumen minat bakat). Kolom ini menyimpan dimensi
 * (EI/SN/TF/JP) dan kutub yang didukung jawaban "Setuju".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instrument_questions', function (Blueprint $table) {
            if (! Schema::hasColumn('instrument_questions', 'mbti_axis')) {
                $table->string('mbti_axis', 4)->nullable()->after('talent_code');
            }

            if (! Schema::hasColumn('instrument_questions', 'mbti_pole')) {
                $table->string('mbti_pole', 1)->nullable()->after('mbti_axis');
            }
        });
    }

    public function down(): void
    {
        Schema::table('instrument_questions', function (Blueprint $table) {
            if (Schema::hasColumn('instrument_questions', 'mbti_pole')) {
                $table->dropColumn('mbti_pole');
            }

            if (Schema::hasColumn('instrument_questions', 'mbti_axis')) {
                $table->dropColumn('mbti_axis');
            }
        });
    }
};
