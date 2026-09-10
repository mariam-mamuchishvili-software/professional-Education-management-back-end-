<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => fake()->unique()->bothify('MOD-###'),
            'description' => fake()->sentence(),
            'duration' => fake()->numberBetween(10, 100),
            'credits' => fake()->numberBetween(1, 10),
        ];
    }
}
