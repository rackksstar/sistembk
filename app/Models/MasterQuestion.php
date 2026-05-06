<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterQuestion extends Model
{
    use HasFactory;

    protected $table = 'master_questions';

    public const KATEGORI_ANGKET = 'angket';
    public const KATEGORI_TRYOUT = 'tryout';

    public const TIPE_PILIHAN_GANDA = 'pilihan_ganda';
    public const TIPE_SKALA = 'skala';
    public const TIPE_ISIAN = 'isian';

    public const KATEGORI = [
        self::KATEGORI_ANGKET,
        self::KATEGORI_TRYOUT,
    ];

    public const TIPE_INPUT = [
        self::TIPE_PILIHAN_GANDA,
        self::TIPE_SKALA,
        self::TIPE_ISIAN,
    ];

    protected $fillable = [
        'kategori',
        'teks_pertanyaan',
        'tipe_input',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}

