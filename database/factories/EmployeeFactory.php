<?php

namespace Database\Factories;

use App\Models\Cinema;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cinema_id' => Cinema::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'invited_at' => now()->subWeek(),
            'invitation_accepted_at' => now()->subDays(6),
        ];
    }

    public function invited(): static
    {
        return $this->state(fn (): array => [
            'password' => null,
            'invitation_accepted_at' => null,
        ]);
    }

    public function deactivated(): static
    {
        return $this->state(fn (): array => ['deactivated_at' => now()]);
    }
}
