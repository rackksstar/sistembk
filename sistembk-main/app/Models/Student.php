<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kelas_id',
        'name',
        'nisn',
        'birth_date',
        'jenis_kelamin',
        'alamat',
        'status_biodata',
        'school',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guidanceClasses(): BelongsToMany
    {
        return $this->belongsToMany(GuidanceClass::class)->withTimestamps();
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function responsAngket(): HasMany
    {
        return $this->hasMany(ResponsAngket::class);
    }

    public function raporBk(): HasMany
    {
        return $this->hasMany(RaporBk::class);
    }

    public function siswaSmk(): HasOne
    {
        return $this->hasOne(SiswaSmk::class);
    }
}
