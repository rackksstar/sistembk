<?php

namespace Database\Factories;

use App\Models\ConsultationRequest;
use App\Models\PenilaianPelayanan;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PenilaianPelayanan>
 */
class PenilaianPelayananFactory extends Factory
{
    protected $model = PenilaianPelayanan::class;

    public function definition(): array
    {
        return [
            'consultation_request_id' => function () {
                $user = User::factory()->create([
                    'role' => User::ROLE_SISWA,
                    'status' => User::STATUS_APPROVED,
                ]);

                $student = Student::query()->create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'nisn' => fake()->unique()->numerify('##########'),
                    'birth_date' => fake()->date(),
                ]);

                return ConsultationRequest::query()->create([
                    'student_id' => $user->id,
                    'counselor_id' => User::factory()->create([
                        'role' => User::ROLE_GURU,
                        'status' => User::STATUS_APPROVED,
                    ])->id,
                    'subject' => fake()->sentence(3),
                    'preferred_time' => 'Pagi',
                    'status' => ConsultationRequest::STATUS_SELESAI,
                    'scheduled_at' => now(),
                ])->id;
            },
            'student_id' => function (array $attributes) {
                $consultation = ConsultationRequest::query()->find($attributes['consultation_request_id']);

                return Student::query()
                    ->where('user_id', $consultation?->student_id)
                    ->value('id')
                    ?? Student::query()->create([
                        'user_id' => $consultation?->student_id,
                        'name' => fake()->name(),
                        'nisn' => fake()->unique()->numerify('##########'),
                        'birth_date' => fake()->date(),
                    ])->id;
            },
            'skor_materi' => fake()->numberBetween(1, 5),
            'skor_cara' => fake()->numberBetween(1, 5),
            'skor_manfaat' => fake()->numberBetween(1, 5),
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
