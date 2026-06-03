<?php

namespace App\Services;

use App\Models\ConsultationRequest;
use App\Models\Kelas;
use App\Models\RaporBk;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CounselorStudentService
{
    /** @var array<int, Collection<int, int>> */
    private array $relatedStudentIdsCache = [];

    public function queryForCounselor(User $counselor): Builder
    {
        $counselor->loadMissing('guruBkProfile');

        $relatedIds = $this->relatedStudentIds($counselor);
        $sekolahId = $counselor->guruBkProfile?->sekolah_id;

        $query = Student::query();

        if ($sekolahId) {
            return $query->where(function (Builder $inner) use ($relatedIds, $sekolahId) {
                if ($relatedIds->isNotEmpty()) {
                    $inner->whereIn('students.id', $relatedIds);
                }

                $inner->orWhereHas('kelas', fn (Builder $kelas) => $kelas->where('sekolah_id', $sekolahId));
            });
        }

        if ($relatedIds->isNotEmpty()) {
            return $query->whereIn('students.id', $relatedIds);
        }

        return $query;
    }

    public function canAccess(Student $student, User $counselor): bool
    {
        $counselor->loadMissing('guruBkProfile');

        if ($this->relatedStudentIds($counselor)->contains($student->id)) {
            return true;
        }

        $sekolahId = $counselor->guruBkProfile?->sekolah_id;

        if ($sekolahId && $student->kelas_id) {
            return Kelas::query()
                ->where('id', $student->kelas_id)
                ->where('sekolah_id', $sekolahId)
                ->exists();
        }

        return $sekolahId === null && $this->relatedStudentIds($counselor)->isEmpty();
    }

    /**
     * @return Collection<int, int>
     */
    private function relatedStudentIds(User $counselor): Collection
    {
        if (isset($this->relatedStudentIdsCache[$counselor->id])) {
            return $this->relatedStudentIdsCache[$counselor->id];
        }

        $userIds = ConsultationRequest::query()
            ->where('counselor_id', $counselor->id)
            ->distinct()
            ->pluck('student_id');

        $fromKonseling = $userIds->isEmpty()
            ? collect()
            : Student::query()
                ->whereIn('user_id', $userIds)
                ->pluck('id');

        $fromRapor = RaporBk::query()
            ->where('counselor_id', $counselor->id)
            ->distinct()
            ->pluck('student_id');

        return $this->relatedStudentIdsCache[$counselor->id] = $fromKonseling
            ->merge($fromRapor)
            ->unique()
            ->values();
    }
}
