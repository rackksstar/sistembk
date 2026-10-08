<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentQuestion extends Model
{
    public const CATEGORY_MINAT_BAKAT = 'minat_bakat';

    public const CATEGORY_STRATEGI_BELAJAR = 'strategi_belajar';

    public const CATEGORY_KEPRIBADIAN = 'kepribadian';

    public const CATEGORIES = [
        self::CATEGORY_MINAT_BAKAT => 'Minat Bakat',
        self::CATEGORY_STRATEGI_BELAJAR => 'Strategi Belajar',
        self::CATEGORY_KEPRIBADIAN => 'Kepribadian',
    ];

    /**
     * Categories whose questions are answered in numbered sections
     * (ala ruangguru), one section confirmed and locked at a time.
     */
    public const SECTIONS = [
        self::CATEGORY_STRATEGI_BELAJAR => [
            1 => 'Perencanaan Belajar',
            2 => 'Eksekusi Belajar',
            3 => 'Refleksi Belajar',
        ],
    ];

    /**
     * Enam dimensi RIASEC (kerangka Talents Mapping) yang dipakai untuk
     * mengelompokkan soal Minat Bakat.
     */
    public const RIASEC_CODES = [
        'R' => 'Realistic',
        'I' => 'Investigative',
        'A' => 'Artistic',
        'S' => 'Social',
        'E' => 'Enterprising',
        'C' => 'Conventional',
    ];

    protected $fillable = ['category', 'section', 'talent_code', 'question', 'options', 'is_active', 'created_by'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_active' => 'boolean',
            'section' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InstrumentAnswer::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function sectionLabel(): ?string
    {
        return self::SECTIONS[$this->category][$this->section] ?? null;
    }

    public function talentCodeLabel(): ?string
    {
        return self::RIASEC_CODES[$this->talent_code] ?? null;
    }
}
