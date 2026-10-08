<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id',
    'talent_interest',
    'sociometry',
    'status',
    'submitted_at',
])]
class AssessmentResponse extends Model
{
    public const STATUS_SUBMITTED = 'submitted';

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    protected function casts(): array
    {
        return [
            'talent_interest' => 'array',
            'sociometry' => 'array',
            'submitted_at' => 'datetime',
        ];
    }
}
