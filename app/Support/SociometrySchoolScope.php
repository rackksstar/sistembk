<?php

namespace App\Support;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SociometrySchoolScope
{
    public function schoolIdOrFail(User $teacher): int
    {
        $teacher->loadMissing('guruBkProfile');
        $schoolId = $teacher->guruBkProfile?->sekolah_id;

        abort_unless($schoolId, 403, 'Akun Guru BK belum terhubung ke sekolah.');

        return (int) $schoolId;
    }

    /**
     * @return Builder<Kelas>
     */
    public function classes(User $teacher): Builder
    {
        return Kelas::query()->where('sekolah_id', $this->schoolIdOrFail($teacher));
    }

    /**
     * Query of student user IDs linked to a class in the teacher's school.
     *
     * @return Builder<User>
     */
    public function students(User $teacher): Builder
    {
        $schoolId = $this->schoolIdOrFail($teacher);

        return User::query()
            ->where('role', User::ROLE_SISWA)
            ->whereHas('studentProfile.kelas', fn (Builder $query) => $query->where('sekolah_id', $schoolId));
    }
}
