<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProgramStudi extends Model
{
    protected $fillable = [
        'institusi',
        'nama',
        'jenjang_pendidikan',
        'jurusan',
        'deskripsi',
        'prospek_karier',
        'website_url',
        'is_verified',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function interestCategories(): BelongsToMany
    {
        return $this->belongsToMany(InterestCategory::class, 'interest_category_program_studi')
            ->withPivot('relevansi')
            ->withTimestamps();
    }

    protected function kodeTag(): Attribute
    {
        return Attribute::get(function (): string {
            $codes = $this->interestCategories
                ->sortByDesc(fn (InterestCategory $category) => (int) $category->pivot->relevansi)
                ->take(2)
                ->pluck('kode')
                ->filter()
                ->values();

            return $codes->implode('-');
        });
    }
}
