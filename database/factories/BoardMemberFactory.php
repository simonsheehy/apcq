<?php

namespace Database\Factories;

use App\Models\BoardMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BoardMember>
 */
class BoardMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->numberBetween(1, 9999)),
            'title' => fake()->randomElement([
                'Président',
                'Vice-président',
                'Trésorier',
                'Secrétaire',
                'Administrateur',
            ]),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'linkedin_url' => fake()->optional()->url(),
            'photo' => null,
            'bio' => fake()->paragraphs(2, true),
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
