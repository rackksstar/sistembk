<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    public const JENJANG_OPTIONS = ['SD', 'SMP', 'SMA', 'SMK'];

    public const TINGKATAN_OPTIONS = [
        '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12',
        'X', 'XI', 'XII',
    ];

    protected $fillable = [
        'sekolah_id',
        'nama',
        'jenjang',
        'tingkatan',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}

