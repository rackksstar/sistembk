<?php

namespace App\Services\Minat;

use App\Models\CareerField;
use App\Models\ProgramStudi;
use Illuminate\Support\Collection;

class RecommendationService
{
    public const WEIGHT_TOP1 = 1.0;

    public const WEIGHT_TOP2 = 0.6;

    public const WEIGHT_TOP3 = 0.3;

    public const EMPTY_MESSAGE_SMA = 'Belum ada program studi PCR yang langsung sesuai minatmu; diskusikan pilihan lain dengan Guru BK.';

    public const EMPTY_MESSAGE_SMA_UNVERIFIED = 'Program studi PCR sudah ada di sistem, tetapi belum ada yang diverifikasi Admin. Minta Admin memverifikasi prodi di menu Program Studi agar rekomendasi muncul.';

    public const EMPTY_MESSAGE_SMK = 'Belum ada bidang karier yang langsung sesuai minatmu; diskusikan pilihan lain dengan Guru BK.';

    /**
     * @return array{items: list<array<string, mixed>>, empty_message: ?string}
     */
    public function forSma(InterestResult $result): array
    {
        $rankWeights = $this->rankWeights($result);
        $categoryIds = array_keys($rankWeights);

        if ($categoryIds === []) {
            return [
                'items' => [],
                'empty_message' => self::EMPTY_MESSAGE_SMA,
            ];
        }

        $programs = ProgramStudi::query()
            ->active()
            ->where('is_verified', true)
            ->whereHas('interestCategories', function ($query) use ($categoryIds) {
                $query->whereIn('interest_categories.id', $categoryIds);
            })
            ->with('interestCategories')
            ->get();

        $items = $this->scoreAndSort($programs, $rankWeights)
            ->take(6)
            ->map(function (array $row): array {
                /** @var ProgramStudi $model */
                $model = $row['model'];

                return [
                    'id' => $model->id,
                    'nama' => $model->nama,
                    'institusi' => $model->institusi,
                    'jenjang_pendidikan' => $model->jenjang_pendidikan,
                    'jurusan' => $model->jurusan,
                    'deskripsi' => $model->deskripsi,
                    'website_url' => $model->website_url,
                    'score' => $row['score'],
                    'match_label' => $row['match_label'],
                    'kode_tag' => $model->kode_tag,
                ];
            })
            ->values()
            ->all();

        if ($items !== []) {
            return [
                'items' => $items,
                'empty_message' => null,
            ];
        }

        $hasUnverifiedMatch = ProgramStudi::query()
            ->active()
            ->where('is_verified', false)
            ->whereHas('interestCategories', function ($query) use ($categoryIds) {
                $query->whereIn('interest_categories.id', $categoryIds);
            })
            ->exists();

        return [
            'items' => [],
            'empty_message' => $hasUnverifiedMatch
                ? self::EMPTY_MESSAGE_SMA_UNVERIFIED
                : self::EMPTY_MESSAGE_SMA,
        ];
    }

    /**
     * @return array{items: list<array<string, mixed>>, empty_message: ?string}
     */
    public function forSmk(InterestResult $result): array
    {
        $rankWeights = $this->rankWeights($result);
        $categoryIds = array_keys($rankWeights);

        if ($categoryIds === []) {
            return [
                'items' => [],
                'empty_message' => self::EMPTY_MESSAGE_SMK,
            ];
        }

        $fields = CareerField::query()
            ->active()
            ->whereHas('interestCategories', function ($query) use ($categoryIds) {
                $query->whereIn('interest_categories.id', $categoryIds);
            })
            ->with('interestCategories')
            ->get();

        $items = $this->scoreAndSort($fields, $rankWeights)
            ->take(6)
            ->map(function (array $row): array {
                /** @var CareerField $model */
                $model = $row['model'];

                return [
                    'id' => $model->id,
                    'nama' => $model->nama,
                    'deskripsi' => $model->deskripsi,
                    'contoh_pekerjaan' => $model->contoh_pekerjaan,
                    'job_zone' => $model->job_zone,
                    'score' => $row['score'],
                    'match_label' => $row['match_label'],
                    'kode_tag' => $model->kode_tag,
                ];
            })
            ->values()
            ->all();

        return [
            'items' => $items,
            'empty_message' => $items === [] ? self::EMPTY_MESSAGE_SMK : null,
        ];
    }

    /**
     * @return array<int, float> category_id => weight
     */
    private function rankWeights(InterestResult $result): array
    {
        $weights = [self::WEIGHT_TOP1, self::WEIGHT_TOP2, self::WEIGHT_TOP3];
        $map = [];

        foreach ($result->topCategories(3) as $index => $category) {
            $map[(int) $category['id']] = $weights[$index];
        }

        return $map;
    }

    /**
     * @param  Collection<int, ProgramStudi|CareerField>  $models
     * @param  array<int, float>  $rankWeights
     * @return Collection<int, array{model: ProgramStudi|CareerField, score: float, match_label: string}>
     */
    private function scoreAndSort(Collection $models, array $rankWeights): Collection
    {
        return $models
            ->map(function (ProgramStudi|CareerField $model) use ($rankWeights): array {
                $score = 0.0;

                foreach ($model->interestCategories as $category) {
                    $categoryId = (int) $category->id;
                    if (! isset($rankWeights[$categoryId])) {
                        continue;
                    }

                    $score += (int) $category->pivot->relevansi * $rankWeights[$categoryId];
                }

                return [
                    'model' => $model,
                    'score' => round($score, 2),
                    'match_label' => $score >= 3 ? 'Sangat Cocok' : 'Cocok',
                ];
            })
            ->filter(fn (array $row) => $row['score'] > 0)
            ->sortByDesc('score')
            ->values();
    }
}
