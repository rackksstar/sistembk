<?php

namespace App\Services;

use App\Models\MasterQuestion;
use App\Models\Student;
use App\Models\TryOut;
use App\Models\TryOutDetail;
use Illuminate\Support\Collection;

class TryOutService
{
    /**
     * @param  array<int>  $kelasIds
     * @param  array<int>  $soalIds
     */
    public function createForCounselor(int $counselorId, array $data, array $kelasIds, array $soalIds): TryOut
    {
        $tryout = TryOut::query()->create([
            'counselor_id' => $counselorId,
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'durasi_menit' => $data['durasi_menit'],
            'mulai_at' => $data['mulai_at'],
            'selesai_at' => $data['selesai_at'],
            'soal_ids' => array_values($soalIds),
            'status' => $data['status'] ?? TryOut::STATUS_DRAFT,
            'hasil_dipublikasikan' => false,
            'hasil_dipublikasikan_at' => null,
        ]);

        $tryout->kelas()->sync($kelasIds);

        return $tryout;
    }

    /**
     * @param  array<int>  $kelasIds
     * @param  array<int>  $soalIds
     */
    public function updateForCounselor(TryOut $tryout, array $data, array $kelasIds, array $soalIds): TryOut
    {
        $hasSubmissions = $tryout->details()->exists();

        $payload = [
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'durasi_menit' => $data['durasi_menit'],
            'mulai_at' => $data['mulai_at'],
            'selesai_at' => $data['selesai_at'],
            'status' => $data['status'] ?? $tryout->status,
        ];

        if (! $hasSubmissions) {
            $payload['soal_ids'] = array_values($soalIds);
        }

        $tryout->update($payload);

        if (! $hasSubmissions) {
            $tryout->kelas()->sync($kelasIds);
        }

        return $tryout->fresh(['kelas']);
    }

    public function deleteForCounselor(TryOut $tryout): void
    {
        if ($tryout->details()->exists()) {
            throw new \RuntimeException('Tryout tidak dapat dihapus karena sudah ada jawaban siswa.');
        }

        $tryout->delete();
    }

    /**
     * @param  array<string, string>  $jawaban
     */
    public function submitAnswers(TryOut $tryout, Student $student, array $jawaban): TryOutDetail
    {
        $soal = $this->soalCollection($tryout);
        $skorPerSoal = $this->hitungSkorPerSoal($soal, $jawaban);
        $skor = $this->rataDariSkorPerSoal($skorPerSoal);

        return TryOutDetail::query()->updateOrCreate(
            [
                'try_out_id' => $tryout->id,
                'student_id' => $student->id,
            ],
            [
                'jawaban' => $jawaban,
                'skor_per_soal' => $skorPerSoal,
                'rata_skor' => $skor,
                'submitted_at' => now(),
            ]
        );
    }

    /**
     * @param  array<string, float|int|null>  $skorPerSoal
     */
    public function updateSkorDetail(TryOutDetail $detail, array $skorPerSoal): TryOutDetail
    {
        $normalized = [];
        foreach ($skorPerSoal as $soalId => $skor) {
            if ($skor === null || $skor === '') {
                $normalized[(string) $soalId] = null;
                continue;
            }

            $normalized[(string) $soalId] = round(min(100, max(0, (float) $skor)), 1);
        }

        $existing = $detail->skor_per_soal ?? [];
        foreach ($normalized as $soalId => $skor) {
            $existing[$soalId] = $skor;
        }

        $detail->update([
            'skor_per_soal' => $existing,
            'rata_skor' => $this->rataDariSkorPerSoal($existing),
        ]);

        return $detail->fresh();
    }

    public function publishHasil(TryOut $tryout): TryOut
    {
        $tryout->update([
            'hasil_dipublikasikan' => true,
            'hasil_dipublikasikan_at' => now(),
        ]);

        return $tryout->fresh();
    }

    public function unpublishHasil(TryOut $tryout): TryOut
    {
        $tryout->update([
            'hasil_dipublikasikan' => false,
            'hasil_dipublikasikan_at' => null,
        ]);

        return $tryout->fresh();
    }

    /** @return Collection<int, MasterQuestion> */
    public function soalCollection(TryOut $tryout): Collection
    {
        $ids = $tryout->soal_ids ?? [];

        if ($ids === []) {
            return collect();
        }

        $questions = MasterQuestion::query()
            ->whereIn('id', $ids)
            ->where('kategori', MasterQuestion::KATEGORI_TRYOUT)
            ->where('is_active', true)
            ->get();

        return $questions
            ->sortBy(fn ($q) => array_search($q->id, $ids, true))
            ->values();
    }

    /**
     * @param  Collection<int, MasterQuestion>  $soal
     * @param  array<string, string>  $jawaban
     * @return array<string, float|null>
     */
    public function hitungSkorPerSoal(Collection $soal, array $jawaban): array
    {
        $hasil = [];

        foreach ($soal as $item) {
            $nilai = trim((string) ($jawaban[$item->id] ?? $jawaban[(string) $item->id] ?? ''));
            $key = (string) $item->id;

            if ($nilai === '') {
                $hasil[$key] = null;
                continue;
            }

            $hasil[$key] = match ($item->tipe_input) {
                MasterQuestion::TIPE_SKALA => (float) (min(5, max(1, (int) $nilai)) * 20),
                MasterQuestion::TIPE_PILIHAN_GANDA => $this->skorPilihanGanda($item, $nilai),
                // Isian/esai: menunggu penilaian Guru BK.
                default => null,
            };
        }

        return $hasil;
    }

    /**
     * @param  Collection<int, MasterQuestion>  $soal
     * @param  array<string, string>  $jawaban
     */
    public function hitungRataSkor(Collection $soal, array $jawaban): float
    {
        return $this->rataDariSkorPerSoal($this->hitungSkorPerSoal($soal, $jawaban));
    }

    /**
     * @param  array<string, float|int|null>  $skorPerSoal
     */
    public function rataDariSkorPerSoal(array $skorPerSoal): float
    {
        $nilai = collect($skorPerSoal)
            ->filter(fn ($skor) => $skor !== null && $skor !== '')
            ->map(fn ($skor) => (float) $skor);

        if ($nilai->isEmpty()) {
            return 0.0;
        }

        return round($nilai->avg(), 1);
    }

    public function siswaBisaAkses(TryOut $tryout, Student $student): bool
    {
        if (! $tryout->isActiveNow()) {
            return false;
        }

        if (! $student->kelas_id) {
            return false;
        }

        return $tryout->relationLoaded('kelas')
            ? $tryout->kelas->contains('id', $student->kelas_id)
            : $tryout->kelas()->where('kelas.id', $student->kelas_id)->exists();
    }

    private function skorPilihanGanda(MasterQuestion $item, string $jawaban): ?float
    {
        $kunci = trim((string) $item->kunci_jawaban);
        if ($kunci === '') {
            // Tanpa kunci: guru menilai manual.
            return null;
        }

        return trim($jawaban) === $kunci ? 100.0 : 0.0;
    }
}
