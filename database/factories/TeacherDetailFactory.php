<?php

namespace Database\Factories;

use App\Models\TeacherDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherDetail>
 */
class TeacherDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'biography' => fake()->paragraph(),
            'additional_information' => fake()->optional()->sentence(),
        ];
    }
}
