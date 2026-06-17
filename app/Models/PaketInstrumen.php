<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaketInstrumen extends Model
{
    protected $table = 'instrument_packages';

    public const KATEGORI_MINAT_BAKAT = 'minat_bakat';
    public const KATEGORI_GAYA_BELAJAR = 'gaya_belajar';
    public const KATEGORI_KEPRIBADIAN = 'kepribadian';
    public const KATEGORI_KARIER = 'karier';
    public const KATEGORI_AKADEMIK = 'akademik';
    public const KATEGORI_SOSIOMETRI = 'sosiometri';
    public const KATEGORI_ANGKET_MASALAH = 'angket_masalah';
    public const KATEGORI_TRY_OUT = 'try_out';

    public const KATEGORI = [
        self::KATEGORI_MINAT_BAKAT,
        self::KATEGORI_GAYA_BELAJAR,
        self::KATEGORI_KEPRIBADIAN,
        self::KATEGORI_KARIER,
        self::KATEGORI_AKADEMIK,
        self::KATEGORI_SOSIOMETRI,
        self::KATEGORI_ANGKET_MASALAH,
        self::KATEGORI_TRY_OUT,
    ];

    public const CATEGORIES = [
        self::KATEGORI_MINAT_BAKAT => 'Minat Bakat',
        self::KATEGORI_GAYA_BELAJAR => 'Gaya Belajar',
        self::KATEGORI_KEPRIBADIAN => 'Kepribadian',
        self::KATEGORI_KARIER => 'Karier',
        self::KATEGORI_AKADEMIK => 'Akademik',
        self::KATEGORI_SOSIOMETRI => 'Sosiometri',
        self::KATEGORI_ANGKET_MASALAH => 'Angket Masalah',
        self::KATEGORI_TRY_OUT => 'Try Out',
    ];

    public const TIPE_ANALISIS = [
        'basic' => 'Analisis Dasar (Skor Saja)',
        'detailed' => 'Analisis Detail (Interpretasi + Rekomendasi)',
        'custom' => 'Analisis Kustom',
    ];

    protected $fillable = [
        'name',
        'description',
        'category',
        'analysis_type',
        'created_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(
            PertanyaanInstrumen::class,
            'instrument_package_questions',
            'instrument_package_id',
            'instrument_question_id'
        )
            ->using(PaketInstrumenPertanyaan::class)
            ->withPivot('order')
            ->orderBy('instrument_package_questions.order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(HasilInstrumen::class, 'instrument_package_id');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function analysisTypeLabel(): string
    {
        return self::TIPE_ANALISIS[$this->analysis_type] ?? $this->analysis_type;
    }

    public function getTotalQuestionsAttribute(): int
    {
        return $this->questions()->count();
    }
}
