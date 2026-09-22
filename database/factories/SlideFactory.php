<?php

namespace Database\Factories;

use App\Models\College;
use App\Models\Slide;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slide>
 */
class SlideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'college_id' => College::inRandomOrder()->value('id') ?? College::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
        ];
    }
}
