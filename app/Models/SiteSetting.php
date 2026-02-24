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
