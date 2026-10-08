<?php

namespace Database\Factories;

use App\Models\TryOut;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TryOut>
 */
class TryOutFactory extends Factory
{
    protected $model = TryOut::class;

    public function definition(): array
    {
        return [
            'counselor_id' => User::factory()->create(['role' => User::ROLE_GURU, 'status' => User::STATUS_APPROVED]),
            'judul' => fake()->sentence(3),
            'deskripsi' => fake()->optional()->sentence(),
            'durasi_menit' => 45,
            'mulai_at' => now()->subHour(),
            'selesai_at' => now()->addDays(2),
            'soal_ids' => [],
            'status' => TryOut::STATUS_AKTIF,
        ];
    }

    public function aktif(): static
    {
        return $this->state(fn () => [
            'status' => TryOut::STATUS_AKTIF,
            'mulai_at' => now()->subHour(),
            'selesai_at' => now()->addDay(),
        ]);
    }
}
