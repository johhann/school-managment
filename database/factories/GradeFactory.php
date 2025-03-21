<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $students = User::student()->pluck('id');
        $teachers = User::teacher()->pluck('id');

        return [
            'student_id' => $this->faker->randomElement($students),
            'subject_id' => Subject::inRandomOrder()->first()->id ?? Subject::factory(),
            'teacher_id' => $this->faker->randomElement($teachers),
            'score' => $this->faker->randomFloat(2, 50, 100),
        ];
    }
}
