<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instrument_questions', function (Blueprint $table) {
            $table->char('talent_code', 1)->nullable()->after('interest_category_id');
            $table->index('talent_code');
        });

        // Backfill dari interest_categories.kode bila sudah terhubung.
        if (Schema::hasTable('interest_categories')) {
            DB::table('instrument_questions')
                ->whereNotNull('interest_category_id')
                ->whereNull('talent_code')
                ->orderBy('id')
                ->chunkById(100, function ($rows): void {
                    $categoryIds = $rows->pluck('interest_category_id')->unique()->filter()->all();
                    $kodes = DB::table('interest_categories')
                        ->whereIn('id', $categoryIds)
                        ->pluck('kode', 'id');

                    foreach ($rows as $row) {
                        $kode = $kodes[$row->interest_category_id] ?? null;
                        if (! is_string($kode) || strlen($kode) !== 1) {
                            continue;
                        }

                        DB::table('instrument_questions')
                            ->where('id', $row->id)
                            ->update(['talent_code' => strtoupper($kode)]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('instrument_questions', function (Blueprint $table) {
            $table->dropIndex(['talent_code']);
            $table->dropColumn('talent_code');
        });
    }
};
