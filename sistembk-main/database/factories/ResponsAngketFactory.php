<?php

namespace Database\Factories;

use App\Models\MasterQuestion;
use App\Models\ResponsAngket;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResponsAngket>
 */
class ResponsAngketFactory extends Factory
{
    protected $model = ResponsAngket::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'master_question_id' => MasterQuestion::factory(),
            'jawaban' => fake()->sentence(3),
        ];
    }
}
