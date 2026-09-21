<?php

namespace Database\Factories;

use App\Models\CollegeDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollegeDetail>
 */
class CollegeDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'description' => fake()->paragraph(),
            'additional_information' => fake()->optional()->sentence(),
        ];
    }
}
