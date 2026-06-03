<?php

namespace App\Support;

use App\Models\Student;

class AuthenticatedStudent
{
    public static function profile(): ?Student
    {
        return once(function () {
            $user = auth()->user();

            if (! $user || ! $user->isRole(\App\Models\User::ROLE_SISWA)) {
                return null;
            }

            return $user->loadMissing('studentProfile')->studentProfile;
        });
    }

    public static function profileOrFail(): Student
    {
        $student = self::profile();
        abort_unless($student, 404);

        return $student;
    }
}
