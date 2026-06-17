<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PertanyaanInstrumen extends Model
{
    public const KATEGORI_MINAT_BAKAT = 'minat_bakat';
    public const KATEGORI_GAYA_BELAJAR = 'gaya_belajar';
    public const KATEGORI_KEPRIBADIAN = 'kepribadian';
    public const KATEGORI_KARIER = 'karier';
    public const KATEGORI_AKADEMIK = 'akademik';
    public const KATEGORI_SOSIOMETRI = 'sosiometri';
    public const KATEGORI_ANGKET_MASALAH = 'angket_masalah';

    public const KATEGORI = [
        self::KATEGORI_MINAT_BAKAT,
        self::KATEGORI_GAYA_BELAJAR,
        self::KATEGORI_KEPRIBADIAN,
        self::KATEGORI_KARIER,
        self::KATEGORI_AKADEMIK,
        self::KATEGORI_SOSIOMETRI,
        self::KATEGORI_ANGKET_MASALAH,
    ];

    public const CATEGORIES = [
        self::KATEGORI_MINAT_BAKAT => 'Minat Bakat',
        self::KATEGORI_GAYA_BELAJAR => 'Gaya Belajar',
        self::KATEGORI_KEPRIBADIAN => 'Kepribadian',
        self::KATEGORI_KARIER => 'Karier',
        self::KATEGORI_AKADEMIK => 'Akademik',
        self::KATEGORI_SOSIOMETRI => 'Sosiometri',
        self::KATEGORI_ANGKET_MASALAH => 'Angket Masalah',
    ];

    protected $table = 'instrument_questions';

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
        return $this->hasMany(JawabanInstrumen::class, 'instrument_question_id');
    }

    public function packages()
    {
        return $this->belongsToMany(
            PaketInstrumen::class,
            'instrument_package_questions',
            'instrument_question_id',
            'instrument_package_id'
        )
            ->using(PaketInstrumenPertanyaan::class)
            ->withPivot('order');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
