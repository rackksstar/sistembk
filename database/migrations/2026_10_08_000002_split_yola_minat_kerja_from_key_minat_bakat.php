<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Soal/hasil klasik Yola (tanpa RIASEC) dipindah ke kategori minat_kerja
     * agar tidak bentrok dengan Minat Bakat Kuliah (minat_bakat + talent_code).
     */
    public function up(): void
    {
        if (! Schema::hasTable('instrument_questions')) {
            return;
        }

        $classicQuestionIds = DB::table('instrument_questions')
            ->where('category', 'minat_bakat')
            ->whereNull('talent_code')
            ->whereNull('interest_category_id')
            ->pluck('id');

        if ($classicQuestionIds->isNotEmpty()) {
            DB::table('instrument_questions')
                ->whereIn('id', $classicQuestionIds)
                ->update([
                    'category' => 'minat_kerja',
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
        }

        if (! Schema::hasTable('instrument_submissions')) {
            return;
        }

        // Submission klasik: tidak punya kode_minat RIASEC.
        $query = DB::table('instrument_submissions')->where('category', 'minat_bakat');

        if (Schema::hasColumn('instrument_submissions', 'kode_minat')) {
            $query->where(function ($inner) {
                $inner->whereNull('kode_minat')->orWhere('kode_minat', '');
            });
        }

        $query->update([
            'category' => 'minat_kerja',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('instrument_questions')) {
            return;
        }

        DB::table('instrument_questions')
            ->where('category', 'minat_kerja')
            ->update([
                'category' => 'minat_bakat',
                'updated_at' => now(),
            ]);

        if (! Schema::hasTable('instrument_submissions')) {
            return;
        }

        DB::table('instrument_submissions')
            ->where('category', 'minat_kerja')
            ->update([
                'category' => 'minat_bakat',
                'updated_at' => now(),
            ]);
    }
};
