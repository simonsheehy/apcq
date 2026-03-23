<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);
        $startsAt = fake()->dateTimeBetween('now', '+6 months');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'excerpt' => fake()->sentence(14),
            'description' => fake()->paragraphs(3, true),
            'location' => fake()->city().', Québec',
            'event_url' => fake()->optional()->url(),
            'starts_at' => $startsAt,
            'ends_at' => fake()->optional()->dateTimeBetween($startsAt, '+7 days'),
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
