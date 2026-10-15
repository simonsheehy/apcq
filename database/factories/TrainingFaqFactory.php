<?php

namespace Database\Factories;

use App\Models\TrainingFaq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingFaq>
 */
class TrainingFaqFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => rtrim(fake()->sentence(6), '.').' ?',
            'answer' => fake()->paragraph(),
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
