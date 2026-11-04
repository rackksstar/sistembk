<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Kategori "kepribadian" sebelumnya memakai skor Likert biasa (opsi berisi
 * 'label' + 'score'). Sekarang kategori ini memakai format tes MBTI, di mana
 * setiap opsi jawaban berisi 'label' + 'pole' (huruf kutub E/I/S/N/T/F/J/P).
 *
 * Soal lama yang belum mengikuti format baru (tidak punya 'pole' pada
 * opsinya) dinonaktifkan agar tidak lagi ditampilkan ke siswa dan tidak
 * mengacaukan penghitungan skor MBTI, tanpa menghapus riwayat jawaban yang
 * sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        $legacyIds = DB::table('instrument_questions')
            ->where('category', 'kepribadian')
            ->where('is_active', true)
            ->get(['id', 'options'])
            ->filter(function ($row) {
                $options = json_decode((string) $row->options, true) ?? [];

                if ($options === []) {
                    return false;
                }

                foreach ($options as $option) {
                    if (! is_array($option) || ! array_key_exists('pole', $option)) {
                        return true;
                    }
                }

                return false;
            })
            ->pluck('id');

        if ($legacyIds->isNotEmpty()) {
            DB::table('instrument_questions')
                ->whereIn('id', $legacyIds)
                ->update(['is_active' => false]);
        }
    }

    public function down(): void
    {
        // Sengaja tidak dibalik: kita tidak ingin mengaktifkan kembali
        // soal format lama yang sudah tidak kompatibel dengan skoring MBTI.
    }
};
