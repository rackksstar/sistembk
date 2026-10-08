<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Jurnal bulanan tidak lagi "satu baris = satu bulan", tapi dicatat per
 * entri layanan (satu hari, satu jenis kegiatan, satu sasaran).
 *
 * Karena itu unique (teacher_id, month, year) harus dilepas — satu bulan
 * boleh punya banyak entri. month/year tetap disimpan sebagai ringkasan
 * periode supaya filter arsip per tahun tetap jalan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_journals', function (Blueprint $table) {
            $table->index('teacher_id', 'monthly_journals_teacher_id_index');
            $table->dropUnique('monthly_journals_teacher_id_month_year_unique');

            $table->date('entry_date')->nullable()->after('teacher_id');
            $table->string('service_type')->nullable()->after('year');
            $table->string('case_category')->nullable()->after('service_type');
            $table->string('target_type')->nullable()->after('case_category');
            $table->string('target_name')->nullable()->after('target_type');
            $table->text('outcome')->nullable()->after('summary');
        });

        // Baris lama (sebelum ada kolom entry_date) diisi dari created_at
        // supaya daftar & cetak tetap menampilkan tanggal yang wajar.
        DB::table('monthly_journals')
            ->whereNull('entry_date')
            ->update(['entry_date' => DB::raw('date(created_at)')]);
    }

    public function down(): void
    {
        Schema::table('monthly_journals', function (Blueprint $table) {
            $table->dropColumn([
                'entry_date',
                'service_type',
                'case_category',
                'target_type',
                'target_name',
                'outcome',
            ]);

            $table->dropIndex('monthly_journals_teacher_id_index');
            $table->unique(['teacher_id', 'month', 'year']);
        });
    }
};
