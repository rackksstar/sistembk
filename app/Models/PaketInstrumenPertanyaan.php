<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PaketInstrumenPertanyaan extends Pivot
{
    protected $table = 'instrument_package_questions';

    protected $fillable = [
        'instrument_package_id',
        'instrument_question_id',
        'order',
    ];

    public function paket(): BelongsTo
    {
        return $this->belongsTo(PaketInstrumen::class, 'instrument_package_id');
    }

    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(PertanyaanInstrumen::class, 'instrument_question_id');
    }
}
