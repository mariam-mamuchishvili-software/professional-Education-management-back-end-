<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\TeacherTraining;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherTraining>
 */
class TeacherTrainingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-5 years', '-1 month');

        return [
            'teacher_id' => Teacher::factory(),
            'title' => fake()->catchPhrase(),
            'organizer' => fake()->company(),
            'certificate_number' => fake()->optional()->bothify('CERT-####-??'),
            'issue_date' => $issueDate,
            'expiry_date' => fake()->optional()->dateTimeBetween($issueDate, '+5 years'),
            'certificate_url' => null,
            'description' => fake()->optional()->sentence(),
        ];
    }
}
