<?php

/**
 * @deprecated Phase 4 (2026-05-30) — Gunakan PenilaianPelayanan sebagai gantinya.
 * Model ini tetap aktif. Penghapusan: Phase 9.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceFeedback extends Model
{
    protected $table = 'service_feedback';

    protected $fillable = [
        'student_id',
        'consultation_request_id',
        'service_type',
        'rating',
        'message',
        'suggestion',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(ConsultationRequest::class, 'consultation_request_id');
    }
}
