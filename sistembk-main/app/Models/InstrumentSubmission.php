<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentSubmission extends Model
{
    protected $fillable = [
        'student_id',
        'category',
        'total_score',
        'result_label',
        'result_description',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InstrumentAnswer::class);
    }

    public function categoryLabel(): string
    {
        return InstrumentQuestion::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Rincian hasil Strategi Belajar per bagian (Perencanaan/Eksekusi/Refleksi).
     * Dipakai baik di halaman hasil siswa maupun laporan hasil instrumen guru,
     * jadi logikanya hidup di satu tempat. Butuh relasi `answers.question` --
     * pakai `$this->answers` (bukan query baru) supaya eager load di caller
     * (mis. daftar submission guru) tidak memicu N+1.
     */
    public function strategiBelajarSectionResults(): array
    {
        if ($this->category !== InstrumentQuestion::CATEGORY_STRATEGI_BELAJAR) {
            return [];
        }

        $scoresBySection = $this->answers
            ->groupBy(fn (InstrumentAnswer $answer) => $answer->question->section)
            ->map(fn ($answers) => [
                'score' => $answers->sum('score'),
                'count' => $answers->count(),
            ]);

        $thresholds = config('strategi_belajar_results.thresholds');
        $levels = config('strategi_belajar_results.levels');
        $sectionContent = config('strategi_belajar_results.sections');
        $sectionLabels = InstrumentQuestion::SECTIONS[InstrumentQuestion::CATEGORY_STRATEGI_BELAJAR];

        $results = [];
        foreach ($sectionLabels as $num => $label) {
            $stats = $scoresBySection->get($num, ['score' => 0, 'count' => 10]);
            $maxScore = max($stats['count'], 1) * 5;
            $percentage = ($stats['score'] / $maxScore) * 100;

            $level = match (true) {
                $percentage >= $thresholds['baik'] => 'baik',
                $percentage >= $thresholds['cukup'] => 'cukup',
                default => 'kurang',
            };

            $results[] = [
                'section' => $num,
                'label' => $label,
                'percentage' => round($percentage),
                'level' => $level,
                'level_label' => $levels[$level]['label'],
                'level_color' => $levels[$level]['color'],
                'description' => $sectionContent[$num][$level]['description'],
                'tips' => $sectionContent[$num][$level]['tips'],
            ];
        }

        return $results;
    }

    /**
     * Skor per dimensi RIASEC (kerangka Talents Mapping) untuk hasil Minat Bakat,
     * diurutkan dari persentase tertinggi. Sama seperti strategiBelajarSectionResults(),
     * pakai `$this->answers` supaya eager load caller (guru: daftar/matriks banyak
     * siswa sekaligus) tidak memicu N+1.
     */
    public function riasecScores(): array
    {
        if ($this->category !== InstrumentQuestion::CATEGORY_MINAT_BAKAT) {
            return [];
        }

        $scoresByCode = $this->answers
            ->groupBy(fn (InstrumentAnswer $answer) => $answer->question->talent_code)
            ->map(fn ($answers) => [
                'score' => $answers->sum('score'),
                'count' => $answers->count(),
            ]);

        $descriptions = config('riasec_results.descriptions');

        $results = [];
        foreach (InstrumentQuestion::RIASEC_CODES as $code => $label) {
            $stats = $scoresByCode->get($code, ['score' => 0, 'count' => 0]);
            $maxScore = max($stats['count'], 1) * 5;
            $percentage = $stats['count'] > 0 ? ($stats['score'] / $maxScore) * 100 : 0;

            $results[] = [
                'code' => $code,
                'label' => $label,
                'score' => $stats['score'],
                'count' => $stats['count'],
                'percentage' => round($percentage),
                'description' => $descriptions[$code] ?? '',
            ];
        }

        usort($results, fn ($a, $b) => $b['percentage'] <=> $a['percentage']);

        return $results;
    }

    /**
     * Kode Holland/RIASEC dominan (mis. "AES"), diambil dari $top dimensi
     * dengan persentase tertinggi.
     */
    public function dominantRiasecCode(int $top = 3): string
    {
        return collect($this->riasecScores())->take($top)->pluck('code')->implode('');
    }
}
