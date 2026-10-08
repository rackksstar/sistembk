<?php

namespace App\Services\Minat;

class InterestResult
{
    /**
     * @param  list<array{id: int, kode: string, nama: string, raw: float|int, max: float|int, persen: float, deskripsi?: ?string}>  $rankedCategories
     */
    public function __construct(
        public readonly array $rankedCategories,
        public readonly string $kode_minat,
        public readonly ?int $dominant_interest_id,
        public readonly ?int $secondary_interest_id,
        public readonly bool $is_tied,
        public readonly string $result_label,
        public readonly ?string $result_description,
        public readonly float|int $total_score,
    ) {}

    /**
     * Bangun ulang InterestResult dari snapshot category_scores (tanpa scoring ulang).
     *
     * @param  list<array{id?: int|string, kode?: string, nama?: string, raw?: float|int, max?: float|int, persen?: float|int, deskripsi?: ?string}>  $categoryScores
     * @param  array{
     *     kode_minat?: ?string,
     *     dominant_interest_id?: ?int,
     *     secondary_interest_id?: ?int,
     *     is_tied?: bool,
     *     result_label?: ?string,
     *     result_description?: ?string,
     *     total_score?: float|int|null
     * }  $metadata
     */
    public static function fromSnapshot(array $categoryScores, array $metadata = []): self
    {
        $ranked = array_map(static function (array $category): array {
            return [
                'id' => (int) ($category['id'] ?? 0),
                'kode' => (string) ($category['kode'] ?? ''),
                'nama' => (string) ($category['nama'] ?? ''),
                'raw' => $category['raw'] ?? 0,
                'max' => $category['max'] ?? 0,
                'persen' => (float) ($category['persen'] ?? 0),
                'deskripsi' => $category['deskripsi'] ?? null,
            ];
        }, array_values($categoryScores));

        $topThree = array_slice($ranked, 0, 3);
        $dominant = $ranked[0] ?? null;
        $secondary = $ranked[1] ?? null;

        $kodeMinat = (string) ($metadata['kode_minat'] ?? '');
        if ($kodeMinat === '') {
            $kodeMinat = implode('', array_column($topThree, 'kode'));
        }

        $totalScore = $metadata['total_score'] ?? null;
        if ($totalScore === null) {
            $totalScore = collect($ranked)->sum('raw');
        }

        $resultLabel = (string) ($metadata['result_label'] ?? ($dominant['nama'] ?? ''));
        $resultDescription = $metadata['result_description'] ?? ($dominant['deskripsi'] ?? null);

        return new self(
            rankedCategories: $ranked,
            kode_minat: $kodeMinat,
            dominant_interest_id: isset($metadata['dominant_interest_id'])
                ? ($metadata['dominant_interest_id'] !== null ? (int) $metadata['dominant_interest_id'] : null)
                : ($dominant['id'] ?? null),
            secondary_interest_id: isset($metadata['secondary_interest_id'])
                ? ($metadata['secondary_interest_id'] !== null ? (int) $metadata['secondary_interest_id'] : null)
                : ($secondary['id'] ?? null),
            is_tied: (bool) ($metadata['is_tied'] ?? false),
            result_label: $resultLabel,
            result_description: $resultDescription,
            total_score: $totalScore,
        );
    }

    /**
     * Snapshot untuk kolom category_scores (tanpa deskripsi).
     *
     * @return list<array{id: int, kode: string, nama: string, raw: float|int, max: float|int, persen: float}>
     */
    public function toArray(): array
    {
        return array_map(static function (array $category): array {
            return [
                'id' => $category['id'],
                'kode' => $category['kode'],
                'nama' => $category['nama'],
                'raw' => $category['raw'],
                'max' => $category['max'],
                'persen' => $category['persen'],
            ];
        }, $this->rankedCategories);
    }

    /**
     * @return list<array{id: int, kode: string, nama: string, raw: float|int, max: float|int, persen: float, deskripsi?: ?string}>
     */
    public function topCategories(int $limit = 3): array
    {
        return array_slice($this->rankedCategories, 0, $limit);
    }

    /**
     * True jika semua kategori skor 0% (mis. semua jawaban "Sangat tidak tertarik").
     * Kode Minat saat itu hanya urutan default, bukan kecenderungan nyata.
     */
    public function isInconclusive(): bool
    {
        if ($this->rankedCategories === []) {
            return true;
        }

        $maxPersen = collect($this->rankedCategories)->max('persen');

        return (float) $maxPersen <= 0.0;
    }
}
