<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SociometryQuestion extends Model
{
    public const JENIS_POSITIF = 'positif';

    public const JENIS_NEGATIF = 'negatif';

    public const JENIS = [
        self::JENIS_POSITIF => 'Dipilih',
        self::JENIS_NEGATIF => 'Ditolak',
    ];

    public const ASPEK_PERTEMANAN = 'pertemanan';

    public const ASPEK_ORGANISASI = 'organisasi';

    public const ASPEK_BELAJAR = 'belajar';

    public const ASPEK_KARIER = 'karier';

    public const ASPEK = [
        self::ASPEK_PERTEMANAN => 'Pertemanan',
        self::ASPEK_ORGANISASI => 'Organisasi/Ekstrakurikuler/Sosial',
        self::ASPEK_BELAJAR => 'Belajar',
        self::ASPEK_KARIER => 'Karier/Masa Depan',
    ];

    protected $fillable = ['pertanyaan', 'jenis', 'aspek', 'urutan', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function jenisLabel(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }

    public function aspekLabel(): string
    {
        return self::ASPEK[$this->aspek] ?? $this->aspek;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
