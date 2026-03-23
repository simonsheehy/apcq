<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'footer_about',
        'footer_address',
        'footer_phone',
        'footer_email',
        'footer_facebook_url',
        'footer_linkedin_url',
        'footer_youtube_url',
    ];

    protected static ?SiteSetting $instance = null;

    public static function instance(): SiteSetting
    {
        if (static::$instance === null) {
            static::$instance = static::query()->first() ?? static::query()->create([]);
        }

        return static::$instance;
    }

    public static function flushInstance(): void
    {
        static::$instance = null;
    }
}
