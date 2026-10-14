<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Training;
use App\Models\TrainingProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingProgress>
 */
class TrainingProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'training_id' => Training::factory(),
            'watched_segments' => [[0, 120]],
            'watched_seconds' => 120,
            'video_duration' => 600,
            'last_position' => 120,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'watched_segments' => [[0, 600]],
            'watched_seconds' => 600,
            'last_position' => 600,
            'completed_at' => now(),
        ]);
    }
}
