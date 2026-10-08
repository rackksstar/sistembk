<?php

namespace App\Services\Minat;

use App\Models\InstrumentQuestion;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InterestScoringService
{
    /**
     * @param  Collection<int, InstrumentQuestion>  $questions
     * @param  array<int|string, int>  $answers  question id => option index
     *
     * @throws ValidationException
     * @throws HttpException
     */
    public function score(Collection $questions, array $answers): InterestResult
    {
        $this->assertAnswersMatchQuestions($questions, $answers);

        $questions->each(fn (InstrumentQuestion $question) => $question->loadMissing('interestCategory'));

        /** @var array<int, array{id: int, kode: string, nama: string, deskripsi: ?string, urutan: int, raw: float|int, max: float|int}> $buckets */
        $buckets = [];

        foreach ($questions as $question) {
            $optionIndex = $answers[$question->id] ?? $answers[(string) $question->id];
            $options = array_values($question->options ?? []);

            if (! array_key_exists((int) $optionIndex, $options) && ! array_key_exists($optionIndex, $options)) {
                abort(422, 'Pilihan jawaban tidak valid.');
            }

            $selected = $options[(int) $optionIndex] ?? null;
            if (! is_array($selected) || ! array_key_exists('score', $selected)) {
                abort(422, 'Pilihan jawaban tidak valid.');
            }

            $category = $question->interestCategory;
            if (! $category) {
                continue;
            }

            $bobot = max(1, (int) ($question->bobot ?? 1));
            $selectedScore = (int) $selected['score'];
            $highestScore = $this->highestOptionScore($options);

            $categoryId = (int) $category->id;
            if (! isset($buckets[$categoryId])) {
                $buckets[$categoryId] = [
                    'id' => $categoryId,
                    'kode' => (string) $category->kode,
                    'nama' => (string) $category->nama,
                    'deskripsi' => $category->deskripsi,
                    'urutan' => (int) $category->urutan,
                    'raw' => 0,
                    'max' => 0,
                ];
            }

            $buckets[$categoryId]['raw'] += $selectedScore * $bobot;
            $buckets[$categoryId]['max'] += $highestScore * $bobot;
        }

        $ranked = collect($buckets)
            ->map(function (array $bucket): array {
                $max = (float) $bucket['max'];
                $raw = $bucket['raw'];
                $persen = $max > 0 ? round(($raw / $max) * 100, 1) : 0.0;

                return [
                    'id' => $bucket['id'],
                    'kode' => $bucket['kode'],
                    'nama' => $bucket['nama'],
                    'raw' => $raw,
                    'max' => $bucket['max'],
                    'persen' => $persen,
                    'deskripsi' => $bucket['deskripsi'],
                    'urutan' => $bucket['urutan'],
                ];
            })
            ->sort(function (array $a, array $b): int {
                if ($a['persen'] !== $b['persen']) {
                    return $b['persen'] <=> $a['persen'];
                }

                if ($a['raw'] !== $b['raw']) {
                    return $b['raw'] <=> $a['raw'];
                }

                return $a['urutan'] <=> $b['urutan'];
            })
            ->values()
            ->map(function (array $row): array {
                unset($row['urutan']);

                return $row;
            })
            ->all();

        $isTied = false;
        if (count($ranked) >= 2) {
            $isTied = abs($ranked[0]['persen'] - $ranked[1]['persen']) < 0.5;
        }

        $topThree = array_slice($ranked, 0, 3);
        $kodeMinat = implode('', array_column($topThree, 'kode'));

        $dominant = $ranked[0] ?? null;
        $secondary = $ranked[1] ?? null;
        $totalScore = collect($ranked)->sum('raw');

        return new InterestResult(
            rankedCategories: $ranked,
            kode_minat: $kodeMinat,
            dominant_interest_id: $dominant['id'] ?? null,
            secondary_interest_id: $secondary['id'] ?? null,
            is_tied: $isTied,
            result_label: $dominant['nama'] ?? '',
            result_description: $dominant['deskripsi'] ?? null,
            total_score: $totalScore,
        );
    }

    /**
     * @param  Collection<int, InstrumentQuestion>  $questions
     * @param  array<int|string, int>  $answers
     */
    private function assertAnswersMatchQuestions(Collection $questions, array $answers): void
    {
        $questionIds = $questions->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();
        $answerIds = collect(array_keys($answers))->map(fn ($id) => (int) $id)->sort()->values();

        if ($questionIds->count() !== $answerIds->count() || $questionIds->all() !== $answerIds->all()) {
            throw ValidationException::withMessages([
                'answers' => 'Jawaban tidak sesuai dengan daftar soal aktif.',
            ]);
        }
    }

    /**
     * @param  list<array{label?: string, score?: int|float|string}>  $options
     */
    private function highestOptionScore(array $options): int
    {
        $scores = [];
        foreach ($options as $option) {
            if (is_array($option) && array_key_exists('score', $option)) {
                $scores[] = (int) $option['score'];
            }
        }

        return $scores === [] ? 0 : max($scores);
    }
}
