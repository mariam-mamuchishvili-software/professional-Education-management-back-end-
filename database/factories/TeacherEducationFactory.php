<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\TeacherEducation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherEducation>
 */
class TeacherEducationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-20 years', '-5 years');

        return [
            'teacher_id' => Teacher::factory(),
            'institution' => fake()->company().' University',
            'degree' => fake()->randomElement(['Bachelor', 'Master', 'Doctorate']),
            'specialization' => fake()->jobTitle(),
            'start_date' => $startDate,
            'end_date' => fake()->dateTimeBetween($startDate, '-1 year'),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
