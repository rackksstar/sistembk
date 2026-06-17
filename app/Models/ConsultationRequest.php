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

    public const CASE_PRIBADI = 'pribadi';
    public const CASE_SOSIAL = 'sosial';
    public const CASE_BELAJAR = 'belajar';
    public const CASE_KARIER = 'karier';
    public const CASE_KEDISIPLINAN = 'kedisiplinan';

    public const CASE_CATEGORIES = [
        self::CASE_PRIBADI => 'Pribadi',
        self::CASE_SOSIAL => 'Sosial',
        self::CASE_BELAJAR => 'Belajar',
        self::CASE_KARIER => 'Karier',
        self::CASE_KEDISIPLINAN => 'Kedisiplinan',
    ];

    protected $fillable = [
        'student_id',
        'counselor_id',
        'rpl_id',
        'subject',
        'case_category',
        'preferred_time',
        'consultation_date',
        'consultation_time',
        'duration_minutes',
        'details',
        'status',
        'scheduled_at',
        'notes',
        'result',
        'evaluation',
        'follow_up',
    ];

    protected function casts(): array
    {
        return [
            'consultation_date' => 'date',
            'scheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
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

    public function rpl(): BelongsTo
    {
        return $this->belongsTo(Rpl::class);
    }

    public function caseCategoryLabel(): string
    {
        return self::CASE_CATEGORIES[$this->case_category] ?? ($this->case_category ?: '-');
    }
}
