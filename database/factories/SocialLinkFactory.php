<?php

namespace Database\Factories;

use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialLink>
 */
class SocialLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'platform' => fake()->randomElement(array_keys(SocialLink::PLATFORMS)),
            // A closure so it's resolved last, after any ->create(['platform' => ...])
            // override has been merged in — keeps the url matching the final platform.
            'url' => fn (array $attributes) => $attributes['platform'] === 'website'
                ? fake()->url()
                : "https://{$attributes['platform']}.com/".fake()->userName(),
        ];
    }
}
