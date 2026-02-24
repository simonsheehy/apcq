<?php

use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('site_settings', 'about_mission')) {
            $settings = SiteSetting::query()->first();
            if ($settings && Page::where('slug', 'a-propos')->doesntExist()) {
                $content = collect([
                    $settings->about_mission ? '<h2>Mission</h2>'.$settings->about_mission : '',
                    $settings->about_history ? '<h2>Au temps du noir et blanc</h2>'.$settings->about_history : '',
                    $settings->about_vision ? '<h2>Vision</h2>'.$settings->about_vision : '',
                ])->filter()->implode('');

                Page::create([
                    'title' => 'À propos',
                    'slug' => 'a-propos',
                    'content' => $content ?: $this->defaultAboutContent(),
                    'is_published' => true,
                    'sort_order' => 0,
                ]);
            }
        }

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['about_mission', 'about_history', 'about_vision']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->longText('about_mission')->nullable()->after('id');
            $table->longText('about_history')->nullable()->after('about_mission');
            $table->longText('about_vision')->nullable()->after('about_history');
        });
    }

    private function defaultAboutContent(): string
    {
        return '<h2>Mission</h2><p>L\'Association des Propriétaires de Cinémas du Québec (APCQ) existe depuis plus de 80 ans et regroupe près de 80 % des écrans de cinéma du Québec et 85 % du box-office à travers 40 villes de la province.</p>'
            .'<h2>Au temps du noir et blanc</h2><p>Fondée le 1er juin 1932, l\'Association des propriétaires de cinémas et ciné-parcs du Québec s\'appelait, à l\'époque, la « Quebec Allied Theatrical Industries Inc. ».</p>'
            .'<h2>Vision</h2><p>L\'APCQ aspire à être reconnue comme l\'interlocuteur incontournable auprès du public, des partenaires de l\'industrie du cinéma et des instances gouvernementales.</p>';
    }
};
