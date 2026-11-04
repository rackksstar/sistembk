<?php

namespace App\Support;

use App\Models\Student;
use Illuminate\Http\Exceptions\HttpResponseException;

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

        if (! $student) {
            throw new HttpResponseException(
                redirect()
                    ->route('siswa.dashboard')
                    ->with('error', 'Profil siswa belum terhubung ke data NISN. Hubungi Guru BK atau admin.')
            );
        }

        return $student;
    }
}
