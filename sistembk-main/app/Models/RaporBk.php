<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaporBk extends Model
{
    use HasFactory;

    public const SEMESTER_GANJIL = 'ganjil';

    public const SEMESTER_GENAP = 'genap';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_FINAL = 'final';

    public const SEMESTERS = [
        self::SEMESTER_GANJIL => 'Semester Ganjil',
        self::SEMESTER_GENAP => 'Semester Genap',
    ];

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_FINAL => 'Final',
    ];

    protected $table = 'rapor_bk';

    protected $fillable = [
        'student_id',
        'counselor_id',
        'semester',
        'tahun_ajaran',
        'perkembangan_akademik',
        'perkembangan_sosial',
        'perkembangan_psikologis',
        'saran_tindak_lanjut',
        'catatan_guru',
        'status',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function semesterLabel(): string
    {
        return self::SEMESTERS[$this->semester] ?? $this->semester;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public static function defaultSemester(): string
    {
        return now()->month <= 6 ? self::SEMESTER_GENAP : self::SEMESTER_GANJIL;
    }

    public static function defaultTahunAjaran(): string
    {
        $year = (int) now()->format('Y');

        if (now()->month >= 7) {
            return "{$year}/".($year + 1);
        }

        return ($year - 1)."/{$year}";
    }
}
