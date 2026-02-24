<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['label' => 'Nouvelles', 'link' => 'posts.index', 'sort_order' => 1, 'location' => 'header'],
            ['label' => 'Calendrier', 'link' => '#calendrier', 'sort_order' => 2, 'location' => 'header'],
            ['label' => 'Membres', 'link' => 'members.index', 'sort_order' => 3, 'location' => 'header'],
            ['label' => 'Partenaires', 'link' => 'partners.index', 'sort_order' => 4, 'location' => 'header'],
            ['label' => 'À propos', 'link' => 'about', 'sort_order' => 5, 'location' => 'header'],
            ['label' => 'Contact', 'link' => 'contact', 'sort_order' => 6, 'location' => 'header'],
            ['label' => 'Nouvelles', 'link' => 'posts.index', 'sort_order' => 1, 'location' => 'footer'],
            ['label' => 'Membres', 'link' => 'members.index', 'sort_order' => 2, 'location' => 'footer'],
            ['label' => 'Partenaires', 'link' => 'partners.index', 'sort_order' => 3, 'location' => 'footer'],
            ['label' => 'À propos', 'link' => 'about', 'sort_order' => 4, 'location' => 'footer'],
            ['label' => 'Contact', 'link' => 'contact', 'sort_order' => 5, 'location' => 'footer'],
            ['label' => 'Zone Membre', 'link' => '#', 'sort_order' => 6, 'location' => 'footer'],
            ['label' => "Zone Conseil d'administration", 'link' => '#', 'sort_order' => 7, 'location' => 'footer'],
        ];

        foreach ($items as $item) {
            MenuItem::firstOrCreate(
                ['label' => $item['label'], 'location' => $item['location']],
                array_merge($item, ['is_visible' => true, 'open_in_new_tab' => false])
            );
        }
    }
}
