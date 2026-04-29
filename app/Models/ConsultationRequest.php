<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'disetujui';
    public const STATUS_SELESAI = 'selesai';

    public const STATUS_MENUNGGU = self::STATUS_PENDING;
    public const STATUS_DIJADWALKAN = self::STATUS_APPROVED;

    protected $fillable = [
        'student_id',
        'counselor_id',
        'subject',
        'preferred_time',
        'consultation_date',
        'consultation_time',
        'details',
        'status',
        'scheduled_at',
        'notes',
        'result',
        'evaluation',
    ];

    protected function casts(): array
    {
        return [
            'consultation_date' => 'date',
            'scheduled_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
}
