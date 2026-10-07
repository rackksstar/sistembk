<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentSubmission extends Model
{
    protected $fillable = [
        'student_id',
        'category',
        'jenjang',
        'kode_minat',
        'category_scores',
        'dominant_interest_id',
        'secondary_interest_id',
        'is_tied',
        'total_score',
        'result_label',
        'result_description',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'category_scores' => 'array',
            'is_tied' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InstrumentAnswer::class);
    }

    public function dominantInterest(): BelongsTo
    {
        return $this->belongsTo(InterestCategory::class, 'dominant_interest_id');
    }

    public function secondaryInterest(): BelongsTo
    {
        return $this->belongsTo(InterestCategory::class, 'secondary_interest_id');
    }

    public function categoryLabel(): string
    {
        return InstrumentQuestion::CATEGORIES[$this->category] ?? $this->category;
    }
}
