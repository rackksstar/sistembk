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

    /** Key — RIASEC / Talents Mapping untuk lanjut kuliah (rekomendasi PCR). */
    public const CATEGORY_MINAT_BAKAT = 'minat_bakat';

    /** Yola — asesmen klasik kesiapan minat untuk jalur kerja. */
    public const CATEGORY_MINAT_KERJA = 'minat_kerja';

    public const CATEGORY_GAYA_BELAJAR = 'gaya_belajar';

    /** Alias slug lama dari referensi Key/Yola (strategi_belajar). */
    public const CATEGORY_STRATEGI_BELAJAR = 'gaya_belajar';

    public const CATEGORY_KEPRIBADIAN = 'kepribadian';

    public const CATEGORY_SOSIOMETRI = 'sosiometri';

    public const CATEGORY_ANGKET_MASALAH = 'angket_masalah';

    public const CATEGORIES = [
        self::CATEGORY_MINAT_BAKAT => 'Minat Bakat Kuliah',
        self::CATEGORY_MINAT_KERJA => 'Minat Bakat Kerja',
        self::CATEGORY_GAYA_BELAJAR => 'Strategi Belajar',
        self::CATEGORY_KEPRIBADIAN => 'Kepribadian',
        self::CATEGORY_SOSIOMETRI => 'Sosiometri',
        self::CATEGORY_ANGKET_MASALAH => 'Masalah',
    ];

    /** Kategori instrumen klasik modul Yola (siap kerja / asesmen diri). */
    public const YOLA_CATEGORIES = [
        self::CATEGORY_MINAT_KERJA => 'Minat Bakat Kerja',
        self::CATEGORY_GAYA_BELAJAR => 'Strategi Belajar',
        self::CATEGORY_KEPRIBADIAN => 'Kepribadian',
        self::CATEGORY_ANGKET_MASALAH => 'Masalah',
    ];

    /** Kategori asesmen RIASEC modul Key (lanjut kuliah / PCR). */
    public const KEY_CATEGORIES = [
        self::CATEGORY_MINAT_BAKAT => 'Minat Bakat Kuliah',
    ];

    public const JENJANG_TARGETS = ['semua', 'SMA', 'SMK'];

    /**
     * Bagian numbered (ala ruangguru) untuk Strategi Belajar.
     */
    public const SECTIONS = [
        self::CATEGORY_GAYA_BELAJAR => [
            1 => 'Perencanaan Belajar',
            2 => 'Eksekusi Belajar',
            3 => 'Refleksi Belajar',
        ],
    ];

    /**
     * Enam dimensi RIASEC (kerangka Talents Mapping) untuk soal Minat Bakat.
     */
    public const RIASEC_CODES = [
        'R' => 'Realistic',
        'I' => 'Investigative',
        'A' => 'Artistic',
        'S' => 'Social',
        'E' => 'Enterprising',
        'C' => 'Conventional',
    ];

    /** Opsi Likert baku Talents Mapping (referensi Key). */
    public const TALENTS_LIKERT_OPTIONS = [
        ['label' => 'Sangat Tidak Suka', 'score' => 1],
        ['label' => 'Tidak Suka', 'score' => 2],
        ['label' => 'Netral', 'score' => 3],
        ['label' => 'Suka', 'score' => 4],
        ['label' => 'Sangat Suka', 'score' => 5],
    ];

    /**
     * Skala persetujuan baku Tes Kepribadian ala 16Personalities — gaya
     * tampilannya sama seperti instrumen asesmen minat bakat (Likert 1-5).
     */
    public const KEPRIBADIAN_LIKERT_OPTIONS = [
        ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
        ['label' => 'Tidak Sesuai', 'score' => 2],
        ['label' => 'Netral', 'score' => 3],
        ['label' => 'Sesuai', 'score' => 4],
        ['label' => 'Sangat Sesuai', 'score' => 5],
    ];

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
        'section',
        'interest_category_id',
        'talent_code',
        'jenjang_target',
        'bobot',
        'mbti_axis',
        'mbti_pole',
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
            'section' => 'integer',
        ];
    }

    public function talentCodeLabel(): ?string
    {
        return self::RIASEC_CODES[$this->talent_code] ?? null;
    }

    public function sectionLabel(): ?string
    {
        return self::SECTIONS[$this->category][$this->section] ?? null;
    }

    public function usesNumberedSections(): bool
    {
        return array_key_exists($this->category, self::SECTIONS) && $this->section !== null;
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

    /**
     * Soal Kepribadian format baru (pernyataan Likert ala 16Personalities):
     * dimensi + kutub tersimpan di kolom, bukan di opsi jawaban.
     */
    public function isMbtiLikert(): bool
    {
        return $this->category === self::CATEGORY_KEPRIBADIAN
            && is_string($this->mbti_axis)
            && $this->mbti_axis !== ''
            && is_string($this->mbti_pole)
            && $this->mbti_pole !== '';
    }

    /**
     * Kutub lawan dari kutub yang didukung jawaban "Setuju" pada soal ini.
     */
    public function mbtiOppositePole(): ?string
    {
        if (! $this->isMbtiLikert()) {
            return null;
        }

        foreach (\App\Support\Mbti::AXES[$this->mbti_axis] ?? [] as $letter => $name) {
            if ($letter !== $this->mbti_pole) {
                return $letter;
            }
        }

        return null;
    }
}
