<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanInstrumen extends Model
{
    protected $table = 'instrument_answers';

    protected $fillable = [
        'instrument_submission_id',
        'instrument_question_id',
        'answer_label',
        'score',
    ];

    public function hasil(): BelongsTo
    {
        return $this->belongsTo(HasilInstrumen::class, 'instrument_submission_id');
    }

    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(PertanyaanInstrumen::class, 'instrument_question_id');
    }
}
