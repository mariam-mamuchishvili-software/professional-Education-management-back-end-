<?php

namespace Database\Factories;

use App\Models\Profession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profession>
 */
class ProfessionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->jobTitle(),
            'code' => fake()->unique()->bothify('PRF-###'),
            'description' => fake()->sentence(),
            'duration' => fake()->numberBetween(1, 4),
            'qualification' => fake()->randomElement(['Bachelor', 'Master', 'Diploma', 'Certificate']),
        ];
    }
}

