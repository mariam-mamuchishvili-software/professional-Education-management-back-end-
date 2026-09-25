<?php

namespace Database\Factories;

use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Training>
 */
class TrainingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'video_link' => fake()->boolean(70)
                ? 'https://www.youtube.com/watch?v='.fake()->regexify('[A-Za-z0-9_-]{11}')
                : null,
        ];
    }
}
