<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menonaktifkan soal Kepribadian format pilihan-paksa dua kutub (opsi berisi
 * 'pole') karena kategori ini beralih ke format pernyataan skala Likert ala
 * 16Personalities (dimensi + kutub tersimpan di kolom mbti_axis/mbti_pole).
 * Riwayat jawaban lama tetap dipertahankan.
 */
return new class extends Migration
{
    public function up(): void
    {
        $ids = DB::table('instrument_questions')
            ->where('category', 'kepribadian')
            ->where('is_active', true)
            ->when(Schema::hasColumn('instrument_questions', 'mbti_axis'), function ($query) {
                $query->whereNull('mbti_axis');
            })
            ->get(['id', 'options'])
            ->filter(function ($row) {
                $options = json_decode((string) $row->options, true) ?? [];

                if ($options === []) {
                    return false;
                }

                foreach ($options as $option) {
                    if (is_array($option) && array_key_exists('pole', $option)) {
                        return true;
                    }
                }

                return false;
            })
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            DB::table('instrument_questions')
                ->whereIn('id', $ids)
                ->update(['is_active' => false]);
        }
    }

    public function down(): void
    {
        // Sengaja tidak mengaktifkan ulang: status soal adalah data operasional.
    }
};
