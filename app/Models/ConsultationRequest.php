<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConsultationRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'disetujui';

    public const STATUS_REJECTED = 'ditolak';

    public const STATUS_RESCHEDULED = 'dijadwalkan_ulang';

    public const STATUS_SELESAI = 'selesai';

    public const STATUS_MENUNGGU = self::STATUS_PENDING;

    public const STATUS_DIJADWALKAN = self::STATUS_APPROVED;

    public const CASE_PRIBADI = 'pribadi';

    public const CASE_SOSIAL = 'sosial';

    public const CASE_BELAJAR = 'belajar';

    public const CASE_KARIER = 'karier';

    public const CASE_KEDISIPLINAN = 'kedisiplinan';

    /** Layanan Konsultasi Prodi Kuliah (catatan dosen / modul Key). */
    public const CASE_PRODI_KULIAH = 'prodi_kuliah';

    public const CASE_CATEGORIES = [
        self::CASE_PRIBADI => 'Pribadi',
        self::CASE_SOSIAL => 'Sosial',
        self::CASE_BELAJAR => 'Belajar',
        self::CASE_KARIER => 'Karier',
        self::CASE_KEDISIPLINAN => 'Kedisiplinan',
        self::CASE_PRODI_KULIAH => 'Prodi Kuliah',
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Menunggu',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_REJECTED => 'Ditolak',
        self::STATUS_RESCHEDULED => 'Dijadwalkan ulang',
        self::STATUS_SELESAI => 'Selesai',
    ];

    protected $fillable = [
        'student_id',
        'counselor_id',
        'rpl_id',
        'subject',
        'case_category',
        'program_studi_id',
        'preferred_time',
        'preferred_date',
        'consultation_date',
        'consultation_time',
        'details',
        'status',
        'rejection_reason',
        'scheduled_at',
        'notes',
        'meeting_number',
        'duration_minutes',
        'location',
        'approach_technique',
        'semester',
        'year',
        'result',
        'evaluation',
        'follow_up',
    ];

    protected function casts(): array
    {
        return [
            'consultation_date' => 'date',
            'preferred_date' => 'date',
            'scheduled_at' => 'datetime',
            'meeting_number' => 'integer',
            'duration_minutes' => 'integer',
            'semester' => 'integer',
            'year' => 'integer',
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

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function isProdiKuliah(): bool
    {
        return $this->case_category === self::CASE_PRODI_KULIAH;
    }

    public function caseCategoryLabel(): string
    {
        return self::CASE_CATEGORIES[$this->case_category] ?? ($this->case_category ?: '-');
    }

    public function dayDateLabel(): string
    {
        return $this->consultation_date?->translatedFormat('l, d M Y') ?? '-';
    }

    public function semesterLabel(): string
    {
        return match ($this->semester) {
            1 => 'Ganjil',
            2 => 'Genap',
            default => '-',
        };
    }

    public function academicYearLabel(): string
    {
        return $this->year ? $this->year.'/'.($this->year + 1) : '-';
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public static function filterableStatuses(): array
    {
        return self::STATUS_LABELS;
    }

    public function isSchedulable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_RESCHEDULED,
        ], true);
    }

    public function canBeRejected(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
        ], true);
    }

    public function belongsToCounselor(int $counselorId): bool
    {
        return $this->counselor_id === null || $this->counselor_id === $counselorId;
    }

    public function penilaianPelayanan(): HasOne
    {
        return $this->hasOne(PenilaianPelayanan::class);
    }
}
