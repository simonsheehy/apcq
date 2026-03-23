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
            $table->string('footer_facebook_url')->nullable()->after('footer_email');
            $table->string('footer_linkedin_url')->nullable()->after('footer_facebook_url');
            $table->string('footer_youtube_url')->nullable()->after('footer_linkedin_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'footer_facebook_url',
                'footer_linkedin_url',
                'footer_youtube_url',
            ]);
        });
    }
};
