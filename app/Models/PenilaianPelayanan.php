<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianPelayanan extends Model
{
    use HasFactory;

    protected $table = 'penilaian_pelayanan';

    protected $fillable = [
        'consultation_request_id',
        'student_id',
        'skor_materi',
        'skor_cara',
        'skor_manfaat',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'skor_materi' => 'integer',
            'skor_cara' => 'integer',
            'skor_manfaat' => 'integer',
        ];
    }

    public function consultationRequest(): BelongsTo
    {
        return $this->belongsTo(ConsultationRequest::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getRataRataAttribute(): float
    {
        return round(
            ($this->skor_materi + $this->skor_cara + $this->skor_manfaat) / 3,
            1
        );
    }

    public function getPredikatAttribute(): string
    {
        return match (true) {
            $this->rata_rata >= 4.5 => 'Sangat Baik',
            $this->rata_rata >= 3.5 => 'Baik',
            $this->rata_rata >= 2.5 => 'Cukup',
            default => 'Kurang',
        };
    }

    public function getPredikatClassAttribute(): string
    {
        return match ($this->predikat) {
            'Sangat Baik' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300',
            'Baik' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300',
            'Cukup' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
            default => 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-300',
        };
    }
}
