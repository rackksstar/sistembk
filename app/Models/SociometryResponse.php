<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SociometryResponse extends Model
{
    public const TYPE_CLOSE_FRIEND = 'teman_dekat';
    public const TYPE_STUDY_FRIEND = 'teman_belajar';

    public const TYPES = [
        self::TYPE_CLOSE_FRIEND => 'Teman Dekat',
        self::TYPE_STUDY_FRIEND => 'Teman Belajar',
    ];

    protected $fillable = ['student_id', 'chosen_student_id', 'relation_type', 'reason', 'submitted_at'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function chosenStudent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chosen_student_id');
    }
}
