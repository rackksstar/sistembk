<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris = satu entri layanan BK (bukan lagi "satu baris = satu bulan").
 * month/year disimpan sebagai ringkasan periode supaya filter arsip per tahun
 * tetap jalan, sedangkan entry_date adalah tanggal pelaksanaan yang sebenarnya.
 */
class MonthlyJournal extends Model
{
    public const SERVICE_TYPE_INDIVIDU = 'individu';
    public const SERVICE_TYPE_KELOMPOK = 'kelompok';
    public const SERVICE_TYPE_KLASIKAL = 'klasikal';
    public const SERVICE_TYPE_HOME_VISIT = 'home_visit';
    public const SERVICE_TYPE_KONSULTASI = 'konsultasi';
    public const SERVICE_TYPE_LAINNYA = 'lainnya';

    public const SERVICE_TYPES = [
        self::SERVICE_TYPE_KLASIKAL => 'Layanan Klasikal',
        self::SERVICE_TYPE_INDIVIDU => 'Konseling Individu',
        self::SERVICE_TYPE_KELOMPOK => 'Bimbingan Kelompok',
        self::SERVICE_TYPE_HOME_VISIT => 'Home Visit',
        self::SERVICE_TYPE_KONSULTASI => 'Konsultasi',
        self::SERVICE_TYPE_LAINNYA => 'Lainnya',
    ];

    public const TARGET_SISWA = 'siswa';
    public const TARGET_KELAS = 'kelas';
    public const TARGET_PIHAK_TERKAIT = 'pihak_terkait';

    public const TARGET_TYPES = [
        self::TARGET_SISWA => 'Siswa',
        self::TARGET_KELAS => 'Kelas',
        self::TARGET_PIHAK_TERKAIT => 'Pihak Terkait',
    ];

    protected $fillable = [
        'teacher_id',
        'entry_date',
        'month',
        'year',
        'service_type',
        'case_category',
        'target_type',
        'target_name',
        'title',
        'individual_services',
        'group_services',
        'classical_services',
        'summary',
        'outcome',
        'evaluation',
        'follow_up',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function serviceTypeLabel(): string
    {
        return self::SERVICE_TYPES[$this->service_type] ?? ($this->service_type ?: '-');
    }

    public function caseCategoryLabel(): string
    {
        return ConsultationRequest::CASE_CATEGORIES[$this->case_category] ?? ($this->case_category ?: '-');
    }

    public function targetTypeLabel(): string
    {
        return self::TARGET_TYPES[$this->target_type] ?? ($this->target_type ?: '-');
    }

    /**
     * Tanggal pelaksanaan. Baris lama yang dibuat sebelum kolom entry_date
     * ada (atau entri tanpa tanggal) jatuh ke created_at supaya tidak kosong.
     */
    public function entryDate(): ?\Illuminate\Support\Carbon
    {
        return $this->entry_date ?? ($this->created_at?->copy());
    }

    public function periodLabel(): string
    {
        $date = now()->setDate((int) $this->year, (int) $this->month, 1);

        return $date->translatedFormat('F Y');
    }
}
