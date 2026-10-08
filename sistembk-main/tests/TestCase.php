<?php

namespace Tests;

use App\Models\GuruBk;
use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @return array{0: User, 1: Student, 2: Kelas}
     */
    protected function buatGuruDanSiswaTerhubung(): array
    {
        $sekolah = Sekolah::query()->create([
            'nama' => 'SMK Test BK',
            'is_mou' => true,
        ]);

        $kelas = Kelas::query()->create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'X TKJ 1',
        ]);

        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);

        GuruBk::query()->create([
            'user_id' => $guru->id,
            'sekolah_id' => $sekolah->id,
        ]);

        $siswaUser = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $student = Student::query()->create([
            'user_id' => $siswaUser->id,
            'name' => $siswaUser->name,
            'nisn' => fake()->unique()->numerify('##########'),
            'birth_date' => '2010-01-01',
            'kelas_id' => $kelas->id,
        ]);

        return [$guru->fresh(['guruBkProfile']), $student->fresh(['kelas', 'user']), $kelas];
    }
}
