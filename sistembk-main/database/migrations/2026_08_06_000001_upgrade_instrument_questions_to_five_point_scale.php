<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_OPTIONS = [
        ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
        ['label' => 'Tidak Sesuai', 'score' => 2],
        ['label' => 'Sesuai', 'score' => 3],
        ['label' => 'Sangat Sesuai', 'score' => 4],
    ];

    private const NEW_OPTIONS = [
        ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
        ['label' => 'Tidak Sesuai', 'score' => 2],
        ['label' => 'Cukup Sesuai', 'score' => 3],
        ['label' => 'Sesuai', 'score' => 4],
        ['label' => 'Sangat Sesuai', 'score' => 5],
    ];

    public function up(): void
    {
        $this->replaceMatchingOptions(self::OLD_OPTIONS, self::NEW_OPTIONS);
    }

    public function down(): void
    {
        $this->replaceMatchingOptions(self::NEW_OPTIONS, self::OLD_OPTIONS);
    }

    /**
     * Only rewrites rows whose options exactly match $from, so custom
     * questions guru already edited by hand are left untouched.
     */
    private function replaceMatchingOptions(array $from, array $to): void
    {
        DB::table('instrument_questions')->select('id', 'options')->orderBy('id')->cursor()->each(function ($row) use ($from, $to) {
            if (json_decode($row->options, true) === $from) {
                DB::table('instrument_questions')->where('id', $row->id)->update([
                    'options' => json_encode($to),
                ]);
            }
        });
    }
};
