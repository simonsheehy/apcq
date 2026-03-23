<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        if (Event::query()->exists()) {
            return;
        }

        $events = [
            [
                'title' => 'Rencontre des exploitants',
                'excerpt' => 'Discussion sur les enjeux de l’industrie et les priorités de la prochaine saison.',
                'description' => 'Discussion sur les enjeux de l’industrie et les priorités de l’association pour la prochaine saison.',
                'location' => 'Montréal, Québec',
                'starts_at' => now()->addWeeks(2),
                'ends_at' => now()->addWeeks(2)->addHours(3),
                'is_published' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Forum innovation cinéma',
                'excerpt' => 'Échanges sur les pratiques innovantes en expérience client, billetterie et marketing local.',
                'description' => 'Échanges sur les nouvelles pratiques en expérience client, billetterie et marketing local.',
                'location' => 'Québec, Québec',
                'starts_at' => now()->addWeeks(5),
                'ends_at' => now()->addWeeks(5)->addHours(4),
                'is_published' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Assemblée annuelle APCQ',
                'excerpt' => 'Bilan annuel, résolutions et perspectives pour les membres.',
                'description' => 'Bilan annuel, vote des résolutions et perspectives pour les membres.',
                'location' => 'Sherbrooke, Québec',
                'starts_at' => now()->addMonths(2),
                'ends_at' => now()->addMonths(2)->addHours(5),
                'is_published' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($events as $event) {
            Event::query()->create([
                ...$event,
                'slug' => Str::slug($event['title']),
            ]);
        }
    }
}
