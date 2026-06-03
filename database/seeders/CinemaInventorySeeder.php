<?php

namespace Database\Seeders;

use App\Models\AdministrativeRegion;
use App\Models\ImageTechnology;
use App\Models\RoomType;
use App\Models\SeatType;
use App\Models\SoundTechnology;
use Illuminate\Database\Seeder;

class CinemaInventorySeeder extends Seeder
{
    public function run(): void
    {
        // 17 régions administratives du Québec
        $regions = [
            'Bas-Saint-Laurent',
            'Saguenay–Lac-Saint-Jean',
            'Capitale-Nationale',
            'Mauricie',
            'Estrie',
            'Montréal',
            'Outaouais',
            'Abitibi-Témiscamingue',
            'Côte-Nord',
            'Nord-du-Québec',
            'Gaspésie–Îles-de-la-Madeleine',
            'Chaudière-Appalaches',
            'Laval',
            'Lanaudière',
            'Laurentides',
            'Montérégie',
            'Centre-du-Québec',
        ];

        foreach ($regions as $name) {
            AdministrativeRegion::firstOrCreate(['name' => $name]);
        }

        foreach (['Régulière', 'VIP', 'Mixte'] as $name) {
            RoomType::firstOrCreate(['name' => $name]);
        }

        foreach (['2D', '3D', '2K', '4K', 'IMAX', 'ScreenX'] as $name) {
            ImageTechnology::firstOrCreate(['name' => $name]);
        }

        foreach (['5.1', '7.1', 'Dolby Atmos'] as $name) {
            SoundTechnology::firstOrCreate(['name' => $name]);
        }

        foreach (['Régulier', 'Inclinable', 'DBOX', 'VIP'] as $name) {
            SeatType::firstOrCreate(['name' => $name]);
        }
    }
}
