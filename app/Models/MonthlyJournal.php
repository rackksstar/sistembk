<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyJournal extends Model
{
    protected $fillable = [
        'teacher_id',
        'month',
        'year',
        'title',
        'individual_services',
        'group_services',
        'classical_services',
        'summary',
        'evaluation',
        'follow_up',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function periodLabel(): string
    {
        $date = now()->setDate((int) $this->year, (int) $this->month, 1);

        return $date->translatedFormat('F Y');
    }
}
