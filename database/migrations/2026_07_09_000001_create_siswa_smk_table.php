<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa_smk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('nisn')->nullable()->index();
            $table->string('sekolah')->nullable();
            $table->string('jurusan');
            $table->string('kelas')->nullable();
            $table->year('tahun_lulus')->nullable();
            $table->string('nomor_hp')->nullable();
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->json('keahlian')->nullable();
            $table->text('pengalaman')->nullable();
            $table->enum('status_kerja', ['mencari_kerja', 'magang', 'bekerja', 'kuliah', 'belum_siap'])
                ->default('mencari_kerja')
                ->index();
            $table->boolean('siap_dihubungi')->default(true);
            $table->timestamps();

            $table->unique(['student_id']);
            $table->unique(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa_smk');
    }
};
