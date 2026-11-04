<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aditif: tautan opsional ke prodi PCR untuk Layanan Konsultasi Prodi Kuliah.
     */
    public function up(): void
    {
        if (! Schema::hasTable('consultation_requests')) {
            return;
        }

        Schema::table('consultation_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('consultation_requests', 'program_studi_id')) {
                $table->foreignId('program_studi_id')
                    ->nullable()
                    ->after('case_category')
                    ->constrained('program_studis')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('consultation_requests')) {
            return;
        }

        Schema::table('consultation_requests', function (Blueprint $table) {
            if (Schema::hasColumn('consultation_requests', 'program_studi_id')) {
                $table->dropConstrainedForeignId('program_studi_id');
            }
        });
    }
};
