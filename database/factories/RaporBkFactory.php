<?php

namespace Database\Factories;

use App\Models\RaporBk;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaporBk>
 */
class RaporBkFactory extends Factory
{
    protected $model = RaporBk::class;

    public function definition(): array
    {
        return [
            'student_id' => function () {
                $user = User::factory()->create([
                    'role' => User::ROLE_SISWA,
                    'status' => User::STATUS_APPROVED,
                ]);

                return Student::query()->create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'nisn' => fake()->unique()->numerify('##########'),
                    'birth_date' => fake()->date(),
                ])->id;
            },
            'counselor_id' => User::factory()->create([
                'role' => User::ROLE_GURU,
                'status' => User::STATUS_APPROVED,
            ])->id,
            'semester' => RaporBk::defaultSemester(),
            'tahun_ajaran' => RaporBk::defaultTahunAjaran(),
            'perkembangan_akademik' => fake()->paragraph(),
            'perkembangan_sosial' => fake()->paragraph(),
            'perkembangan_psikologis' => fake()->paragraph(),
            'saran_tindak_lanjut' => fake()->sentence(),
            'catatan_guru' => fake()->optional()->sentence(),
            'status' => RaporBk::STATUS_DRAFT,
        ];
    }

    public function final(): static
    {
        return $this->state(fn () => ['status' => RaporBk::STATUS_FINAL]);
    }
}
