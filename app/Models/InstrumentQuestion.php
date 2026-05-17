<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentQuestion extends Model
{
    public const CATEGORY_MINAT_BAKAT = 'minat_bakat';
    public const CATEGORY_GAYA_BELAJAR = 'gaya_belajar';
    public const CATEGORY_KEPRIBADIAN = 'kepribadian';
    public const CATEGORY_SOSIOMETRI = 'sosiometri';
    public const CATEGORY_ANGKET_MASALAH = 'angket_masalah';

    public const CATEGORIES = [
        self::CATEGORY_MINAT_BAKAT => 'Minat Bakat',
        self::CATEGORY_GAYA_BELAJAR => 'Gaya Belajar',
        self::CATEGORY_KEPRIBADIAN => 'Kepribadian',
        self::CATEGORY_SOSIOMETRI => 'Sosiometri',
        self::CATEGORY_ANGKET_MASALAH => 'Masalah',
    ];

    protected $fillable = ['category', 'question', 'options', 'is_active', 'created_by'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_active' => 'boolean',
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
}
