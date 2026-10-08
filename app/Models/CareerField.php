<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CareerField extends Model
{
    protected $fillable = [
        'nama',
        'deskripsi',
        'contoh_pekerjaan',
        'job_zone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'contoh_pekerjaan' => 'array',
            'is_active' => 'boolean',
            'job_zone' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function interestCategories(): BelongsToMany
    {
        return $this->belongsToMany(InterestCategory::class, 'career_field_interest_category')
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
