<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rpl extends Model
{
    public const TYPE_INDIVIDU = 'individu';
    public const TYPE_KELOMPOK = 'kelompok';
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_SELESAI = 'selesai';

    public const TYPES = [
        self::TYPE_INDIVIDU => 'RPL Konseling Individu',
        self::TYPE_KELOMPOK => 'RPL Konseling Kelompok',
    ];

    public const STATUSES = [
        self::STATUS_AKTIF => 'Aktif',
        self::STATUS_SELESAI => 'Selesai',
    ];

    protected $fillable = [
        'teacher_id',
        'class_id',
        'student_id',
        'title',
        'type',
        'status',
        'semester',
        'year',
        'service_date',
        'target',
        'tujuan',
        'materi',
        'metode',
        'evaluasi',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'semester' => 'integer',
            'year' => 'integer',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function groupStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'rpl_student', 'rpl_id', 'student_id')->withTimestamps();
    }

    public function consultationReports(): HasMany
    {
        return $this->hasMany(ConsultationRequest::class);
    }

    public function groupReports(): HasMany
    {
        return $this->hasMany(GroupConsultationReport::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function reportNumber(): string
    {
        return sprintf('RPL-%s-%03d', $this->year ?? now()->year, $this->id ?? 0);
    }

    public function schoolName(): string
    {
        return $this->teacher?->schoolModel?->name
            ?? $this->teacher?->school
            ?? 'Sekolah Tidak Diketahui';
    }
}
