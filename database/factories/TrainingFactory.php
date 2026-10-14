<?php

namespace Database\Factories;

use App\Enums\TrainingStatus;
use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->paragraphs(2, true),
            'vimeo_video_id' => (string) fake()->numberBetween(100000000, 999999999),
            'video_duration' => 600,
            'status' => TrainingStatus::Published,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['status' => TrainingStatus::Draft]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => ['status' => TrainingStatus::Archived]);
    }
}
