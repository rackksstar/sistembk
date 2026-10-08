<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapor_bk', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('counselor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('semester', 10);
            $table->string('tahun_ajaran', 9);

            $table->text('perkembangan_akademik')->nullable();
            $table->text('perkembangan_sosial')->nullable();
            $table->text('perkembangan_psikologis')->nullable();
            $table->text('saran_tindak_lanjut')->nullable();
            $table->text('catatan_guru')->nullable();

            $table->string('status', 20)->default('draft');

            $table->timestamps();

            $table->unique(
                ['student_id', 'counselor_id', 'semester', 'tahun_ajaran'],
                'uniq_rapor_per_siswa_semester'
            );

            $table->index(['counselor_id', 'semester', 'tahun_ajaran'], 'idx_rapor_guru_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapor_bk');
    }
};
