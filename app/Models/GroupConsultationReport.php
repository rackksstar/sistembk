<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupConsultationReport extends Model
{
    protected $fillable = [
        'teacher_id',
        'rpl_id',
        'class_id',
        'title',
        'service_date',
        'duration_minutes',
        'location',
        'case_category',
        'result',
        'evaluation',
        'follow_up',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'duration_minutes' => 'integer',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function rpl(): BelongsTo
    {
        return $this->belongsTo(Rpl::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function caseCategoryLabel(): string
    {
        return ConsultationRequest::CASE_CATEGORIES[$this->case_category] ?? ($this->case_category ?: '-');
    }
}
