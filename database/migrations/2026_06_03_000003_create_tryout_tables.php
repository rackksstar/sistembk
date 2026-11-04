<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('try_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->unsignedSmallInteger('durasi_menit')->default(60);
            $table->dateTime('mulai_at');
            $table->dateTime('selesai_at');
            $table->json('soal_ids');
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->index(['counselor_id', 'status'], 'idx_tryout_guru_status');
        });

        Schema::create('try_out_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('try_out_id')->constrained('try_outs')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['try_out_id', 'kelas_id'], 'uniq_tryout_kelas');
        });

        Schema::create('try_out_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('try_out_id')->constrained('try_outs')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->json('jawaban');
            $table->decimal('rata_skor', 5, 2)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['try_out_id', 'student_id'], 'uniq_tryout_siswa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('try_out_detail');
        Schema::dropIfExists('try_out_kelas');
        Schema::dropIfExists('try_outs');
    }
};
