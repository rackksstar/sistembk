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
        'jenjang',
        'kode_minat',
        'category_scores',
        'dominant_interest_id',
        'secondary_interest_id',
        'is_tied',
        'total_score',
        'percentage',
        'result_label',
        'result_description',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'category_scores' => 'array',
            'is_tied' => 'boolean',
            'percentage' => 'float',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InstrumentAnswer::class);
    }

    public function dominantInterest(): BelongsTo
    {
        return $this->belongsTo(InterestCategory::class, 'dominant_interest_id');
    }

    public function secondaryInterest(): BelongsTo
    {
        return $this->belongsTo(InterestCategory::class, 'secondary_interest_id');
    }

    public function categoryLabel(): string
    {
        return InstrumentQuestion::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Rincian hasil Strategi Belajar per bagian (Perencanaan/Eksekusi/Refleksi).
     * Pakai relasi answers.question agar eager-load caller tidak N+1.
     *
     * @return list<array{section: int, label: string, percentage: int, level: string, level_label: string, level_color: string, description: string, tips: list<string>}>
     */
    public function strategiBelajarSectionResults(): array
    {
        if ($this->category !== InstrumentQuestion::CATEGORY_GAYA_BELAJAR) {
            return [];
        }

        $this->loadMissing('answers.question');

        $scoresBySection = $this->answers
            ->groupBy(fn (InstrumentAnswer $answer) => $answer->question?->section)
            ->filter(fn ($_, $section) => $section !== null && $section !== '')
            ->map(fn ($answers) => [
                'score' => $answers->sum('score'),
                'count' => $answers->count(),
            ]);

        if ($scoresBySection->isEmpty()) {
            return [];
        }

        $thresholds = config('strategi_belajar_results.thresholds');
        $levels = config('strategi_belajar_results.levels');
        $sectionContent = config('strategi_belajar_results.sections');
        $sectionLabels = InstrumentQuestion::SECTIONS[InstrumentQuestion::CATEGORY_GAYA_BELAJAR] ?? [];

        $results = [];
        foreach ($sectionLabels as $num => $label) {
            $stats = $scoresBySection->get($num, ['score' => 0, 'count' => 0]);
            if (($stats['count'] ?? 0) < 1) {
                continue;
            }

            $sectionAnswers = $this->answers->filter(
                fn (InstrumentAnswer $answer) => (int) ($answer->question?->section ?? 0) === (int) $num
            );
            $maxPerQuestion = $sectionAnswers
                ->map(function (InstrumentAnswer $answer) {
                    $scores = collect($answer->question?->options ?? [])
                        ->filter(fn ($option) => is_array($option) && array_key_exists('score', $option))
                        ->map(fn ($option) => (int) $option['score']);

                    return $scores->max() ?: 5;
                })
                ->max() ?: 5;

            $maxScore = max($stats['count'], 1) * $maxPerQuestion;
            $percentage = ($stats['score'] / $maxScore) * 100;

            $level = match (true) {
                $percentage >= ($thresholds['baik'] ?? 70) => 'baik',
                $percentage >= ($thresholds['cukup'] ?? 40) => 'cukup',
                default => 'kurang',
            };

            $results[] = [
                'section' => (int) $num,
                'label' => $label,
                'percentage' => (int) round($percentage),
                'level' => $level,
                'level_label' => $levels[$level]['label'] ?? $level,
                'level_color' => $levels[$level]['color'] ?? 'slate',
                'description' => $sectionContent[$num][$level]['description'] ?? '',
                'tips' => $sectionContent[$num][$level]['tips'] ?? [],
            ];
        }

        return $results;
    }

    /**
     * Skor per dimensi RIASEC (Talents Mapping), diurutkan dari persentase tertinggi.
     * Prefer snapshot category_scores; fallback hitung dari jawaban + talent_code.
     *
     * @return list<array{code: string, label: string, score: int|float, count: int, percentage: int, description: string}>
     */
    public function riasecScores(): array
    {
        if ($this->category !== InstrumentQuestion::CATEGORY_MINAT_BAKAT) {
            return [];
        }

        $descriptions = config('riasec_results.descriptions', []);

        if (is_array($this->category_scores) && $this->category_scores !== []) {
            $byKode = collect($this->category_scores)->keyBy('kode');
            $results = [];

            foreach (InstrumentQuestion::RIASEC_CODES as $code => $label) {
                $row = $byKode->get($code, []);
                $results[] = [
                    'code' => $code,
                    'label' => (string) ($row['nama'] ?? $label),
                    'score' => $row['raw'] ?? 0,
                    'count' => 0,
                    'percentage' => (int) round((float) ($row['persen'] ?? 0)),
                    'description' => (string) ($descriptions[$code] ?? ($row['deskripsi'] ?? '')),
                ];
            }

            usort($results, fn ($a, $b) => $b['percentage'] <=> $a['percentage']);

            return $results;
        }

        $this->loadMissing('answers.question');

        $scoresByCode = $this->answers
            ->groupBy(function (InstrumentAnswer $answer) {
                $question = $answer->question;

                return $question?->talent_code
                    ?? $question?->interestCategory?->kode
                    ?? null;
            })
            ->filter(fn ($_, $code) => is_string($code) && $code !== '')
            ->map(fn ($answers) => [
                'score' => $answers->sum('score'),
                'count' => $answers->count(),
            ]);

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
                'percentage' => (int) round($percentage),
                'description' => (string) ($descriptions[$code] ?? ''),
            ];
        }

        usort($results, fn ($a, $b) => $b['percentage'] <=> $a['percentage']);

        return $results;
    }

    /**
     * Kode Holland/RIASEC dominan (mis. "AES").
     */
    public function dominantRiasecCode(int $top = 3): string
    {
        if (is_string($this->kode_minat) && $this->kode_minat !== '') {
            return mb_substr($this->kode_minat, 0, $top);
        }

        return collect($this->riasecScores())->take($top)->pluck('code')->implode('');
    }
}
