<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstrumentQuestion extends Model
{
    use SoftDeletes;

    public const CATEGORY_MINAT_BAKAT = 'minat_bakat';

    public const CATEGORY_GAYA_BELAJAR = 'gaya_belajar';

    public const CATEGORY_KEPRIBADIAN = 'kepribadian';

    public const CATEGORY_SOSIOMETRI = 'sosiometri';

    public const CATEGORY_ANGKET_MASALAH = 'angket_masalah';

    public const CATEGORIES = [
        self::CATEGORY_MINAT_BAKAT => 'Minat Bakat',
        self::CATEGORY_GAYA_BELAJAR => 'Strategi Belajar',
        self::CATEGORY_KEPRIBADIAN => 'Kepribadian',
        self::CATEGORY_SOSIOMETRI => 'Sosiometri',
        self::CATEGORY_ANGKET_MASALAH => 'Masalah',
    ];

    /** Kategori instrumen klasik modul Yola (kerja/diri). */
    public const YOLA_CATEGORIES = [
        self::CATEGORY_GAYA_BELAJAR => 'Strategi Belajar',
        self::CATEGORY_KEPRIBADIAN => 'Kepribadian',
        self::CATEGORY_ANGKET_MASALAH => 'Masalah',
    ];

    /** Kategori asesmen RIASEC modul Key (lanjut kuliah / PCR). */
    public const KEY_CATEGORIES = [
        self::CATEGORY_MINAT_BAKAT => 'Minat Bakat RIASEC',
    ];

    public const JENJANG_TARGETS = ['semua', 'SMA', 'SMK'];

    public static function isYolaCategory(string $category): bool
    {
        return array_key_exists($category, self::YOLA_CATEGORIES);
    }

    public static function isKeyCategory(string $category): bool
    {
        return array_key_exists($category, self::KEY_CATEGORIES);
    }

    protected $fillable = [
        'category',
        'interest_category_id',
        'jenjang_target',
        'bobot',
        'question',
        'options',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_active' => 'boolean',
            'bobot' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function interestCategory(): BelongsTo
    {
        return $this->belongsTo(InterestCategory::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InstrumentAnswer::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForJenjang(Builder $query, ?string $jenjang): Builder
    {
        if (! $jenjang || ! in_array($jenjang, ['SMA', 'SMK'], true)) {
            return $query->where('jenjang_target', 'semua');
        }

        return $query->whereIn('jenjang_target', ['semua', $jenjang]);
    }

    public function isCategorizedForMinat(): bool
    {
        return $this->category === self::CATEGORY_MINAT_BAKAT && $this->interest_category_id !== null;
    }
}
