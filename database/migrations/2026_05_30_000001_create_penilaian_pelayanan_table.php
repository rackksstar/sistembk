<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_pelayanan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consultation_request_id')
                ->constrained('consultation_requests')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('skor_materi');
            $table->unsignedTinyInteger('skor_cara');
            $table->unsignedTinyInteger('skor_manfaat');

            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(
                ['consultation_request_id', 'student_id'],
                'uniq_penilaian_per_konseling'
            );

            $table->index('consultation_request_id', 'idx_penilaian_konseling');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_pelayanan');
    }
};
