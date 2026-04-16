<?php

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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('home_hero_badge')->nullable()->after('footer_youtube_url');
            $table->string('home_hero_title')->nullable()->after('home_hero_badge');
            $table->text('home_hero_text')->nullable()->after('home_hero_title');
            $table->string('home_hero_background_image')->nullable()->after('home_hero_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'home_hero_badge',
                'home_hero_title',
                'home_hero_text',
                'home_hero_background_image',
            ]);
        });
    }
};
