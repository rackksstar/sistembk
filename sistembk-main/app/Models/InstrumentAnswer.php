<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstrumentAnswer extends Model
{
    protected $fillable = [
        'instrument_submission_id',
        'instrument_question_id',
        'answer_label',
        'score',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(InstrumentSubmission::class, 'instrument_submission_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(InstrumentQuestion::class, 'instrument_question_id');
    }
}
