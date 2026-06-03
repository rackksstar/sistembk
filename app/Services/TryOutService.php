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
        ]);

        $tryout->kelas()->sync($kelasIds);

        return $tryout;
    }

    /**
     * @param  array<string, string>  $jawaban
     */
    public function submitAnswers(TryOut $tryout, Student $student, array $jawaban): TryOutDetail
    {
        $soal = $this->soalCollection($tryout);
        $skor = $this->hitungRataSkor($soal, $jawaban);

        return TryOutDetail::query()->updateOrCreate(
            [
                'try_out_id' => $tryout->id,
                'student_id' => $student->id,
            ],
            [
                'jawaban' => $jawaban,
                'rata_skor' => $skor,
                'submitted_at' => now(),
            ]
        );
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
     */
    public function hitungRataSkor(Collection $soal, array $jawaban): float
    {
        if ($soal->isEmpty()) {
            return 0.0;
        }

        $total = 0;
        $count = 0;

        foreach ($soal as $item) {
            $nilai = trim((string) ($jawaban[$item->id] ?? ''));
            if ($nilai === '') {
                continue;
            }

            $count++;
            $total += match ($item->tipe_input) {
                MasterQuestion::TIPE_SKALA => min(5, max(1, (int) $nilai)) * 20,
                default => strlen($nilai) >= 3 ? 80 : 40,
            };
        }

        return $count > 0 ? round($total / $count, 1) : 0.0;
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
}
