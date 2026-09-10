<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Profession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => 'Group ' . fake()->unique()->numberBetween(1, 100),
            'code' => fake()->unique()->bothify('GRP-###'),
            'capacity' => fake()->numberBetween(15, 30),
            'study_shift' => fake()->randomElement(['morning', 'afternoon', 'evening']),
            'profession_id' => Profession::inRandomOrder()->value('id') ?? Profession::factory(),
        ];
    }
}
