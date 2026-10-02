<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\TeacherWorkExperience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherWorkExperience>
 */
class TeacherWorkExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-15 years', '-2 years');

        return [
            'teacher_id' => Teacher::factory(),
            'organization' => fake()->company(),
            'position' => fake()->jobTitle(),
            'start_date' => $startDate,
            'end_date' => fake()->dateTimeBetween($startDate, '-1 year'),
            'is_current' => false,
            'description' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * A position the teacher still holds.
     */
    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_date' => null,
            'is_current' => true,
        ]);
    }
}
