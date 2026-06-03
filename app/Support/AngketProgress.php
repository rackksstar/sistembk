<?php

namespace App\Support;

class AngketProgress
{
    public static function predikat(int $dijawab, int $total): string
    {
        if ($total === 0) {
            return 'Belum Ada Soal';
        }

        $persen = ($dijawab / $total) * 100;

        return match (true) {
            $persen >= 80 => 'Lengkap',
            $persen >= 50 => 'Sebagian',
            default => 'Belum Lengkap',
        };
    }
}
