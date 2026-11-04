<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterestCategory extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'warna',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(InstrumentQuestion::class);
    }

    public function programStudis(): BelongsToMany
    {
        return $this->belongsToMany(ProgramStudi::class, 'interest_category_program_studi')
            ->withPivot('relevansi')
            ->withTimestamps();
    }

    public function careerFields(): BelongsToMany
    {
        return $this->belongsToMany(CareerField::class, 'career_field_interest_category')
            ->withPivot('relevansi')
            ->withTimestamps();
    }
}
