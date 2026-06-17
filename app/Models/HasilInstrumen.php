<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasilInstrumen extends Model
{
    protected $table = 'instrument_submissions';

    protected $fillable = [
        'student_id',
        'instrument_package_id',
        'category',
        'total_score',
        'percentage',
        'result_label',
        'result_description',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'percentage' => 'float',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(PaketInstrumen::class, 'instrument_package_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(JawabanInstrumen::class, 'instrument_submission_id');
    }

    public function categoryLabel(): string
    {
        return PertanyaanInstrumen::CATEGORIES[$this->category] ?? $this->category;
    }
}
