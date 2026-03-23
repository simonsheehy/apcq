<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (SiteSetting::query()->exists()) {
            return;
        }

        SiteSetting::query()->create([
            'footer_about' => "L'Association des propriétaires de cinémas du Québec (APCQ) est une association représentée par des propriétaires ou des administrateurs de cinémas.",
            'footer_address' => '63, rue King Ouest, Sherbrooke, Québec, J1H 1P1',
            'footer_phone' => '514 493-9898',
            'footer_email' => 'info@apcq.ca',
            'footer_facebook_url' => 'https://facebook.com',
            'footer_linkedin_url' => 'https://linkedin.com',
            'footer_youtube_url' => 'https://youtube.com',
        ]);
    }
}
