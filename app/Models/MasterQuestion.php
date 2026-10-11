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

    public const TIPE_LABELS = [
        self::TIPE_PILIHAN_GANDA => 'Pilihan ganda',
        self::TIPE_SKALA => 'Skala 1–5',
        self::TIPE_ISIAN => 'Isian / esai',
    ];

    protected $fillable = [
        'kategori',
        'teks_pertanyaan',
        'tipe_input',
        'opsi',
        'kunci_jawaban',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'opsi' => 'array',
        ];
    }

    public function tipeLabel(): string
    {
        return self::TIPE_LABELS[$this->tipe_input] ?? str_replace('_', ' ', (string) $this->tipe_input);
    }

    public function opsiList(): array
    {
        return collect($this->opsi ?? [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    public function responAngket(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResponsAngket::class);
    }
}
