<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolahs';

    protected $fillable = [
        'nama',
        'npsn',
        'alamat',
        'logo_path',
        'is_mou',
        'paket_aktif',
        'tanggal_aktivasi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_aktivasi' => 'date',
            'is_mou' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    public function guruBks(): HasMany
    {
        return $this->hasMany(GuruBk::class);
    }
}
