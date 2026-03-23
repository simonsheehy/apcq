<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        if (Tag::query()->exists()) {
            return;
        }

        $tags = [
            'Industrie',
            'Réglementation',
            'Innovation',
            'Formation',
            'Événement',
            'Distribution',
        ];

        foreach ($tags as $tagName) {
            Tag::query()->create([
                'name' => $tagName,
                'slug' => Str::slug($tagName),
            ]);
        }
    }
}
