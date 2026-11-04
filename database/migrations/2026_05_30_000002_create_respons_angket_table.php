<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respons_angket', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('master_question_id')
                ->constrained('master_questions')
                ->cascadeOnDelete();

            $table->string('jawaban', 500);

            $table->timestamps();

            $table->unique(
                ['student_id', 'master_question_id'],
                'uniq_respons_per_soal'
            );

            $table->index('student_id', 'idx_respons_student');
            $table->index('master_question_id', 'idx_respons_soal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respons_angket');
    }
};
