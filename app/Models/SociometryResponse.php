<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SociometryResponse extends Model
{
    public const TYPE_CLOSE_FRIEND = 'teman_dekat';

    public const TYPE_STUDY_FRIEND = 'teman_belajar';

    /** Jenis sosiometri berbasis bank soal: teman dipilih / ditolak. */
    public const TYPE_POSITIF = 'positif';

    public const TYPE_NEGATIF = 'negatif';

    public const TYPES = [
        self::TYPE_CLOSE_FRIEND => 'Teman Dekat',
        self::TYPE_STUDY_FRIEND => 'Teman Belajar',
        self::TYPE_POSITIF => 'Dipilih',
        self::TYPE_NEGATIF => 'Ditolak',
    ];

    protected $fillable = [
        'student_id',
        'chosen_student_id',
        'relation_type',
        'sociometry_question_id',
        'reason',
        'submitted_at',
    ];

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

    public function question(): BelongsTo
    {
        return $this->belongsTo(SociometryQuestion::class, 'sociometry_question_id');
    }
}
