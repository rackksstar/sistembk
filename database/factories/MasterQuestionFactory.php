<?php

namespace Database\Factories;

use App\Models\MasterQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterQuestion>
 */
class MasterQuestionFactory extends Factory
{
    protected $model = MasterQuestion::class;

    public function definition(): array
    {
        return [
            'kategori' => MasterQuestion::KATEGORI_ANGKET,
            'teks_pertanyaan' => fake()->sentence(),
            'tipe_input' => MasterQuestion::TIPE_ISIAN,
            'is_active' => true,
        ];
    }

    public function angket(): static
    {
        return $this->state(fn (array $attributes) => [
            'kategori' => MasterQuestion::KATEGORI_ANGKET,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
