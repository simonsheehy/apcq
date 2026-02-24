<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'label',
        'link',
        'sort_order',
        'location',
        'is_visible',
        'open_in_new_tab',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'open_in_new_tab' => 'boolean',
        ];
    }

    public function resolveUrl(): string
    {
        $link = trim($this->link);

        if (str_starts_with($link, 'http://') || str_starts_with($link, 'https://')) {
            return $link;
        }

        if (str_starts_with($link, '/')) {
            return url($link);
        }

        if (str_starts_with($link, '#')) {
            return route('home').$link;
        }

        if (str_contains($link, ':')) {
            [$routeName, $param] = explode(':', $link, 2);
            try {
                return route(trim($routeName), trim($param));
            } catch (\Throwable) {
                return $link;
            }
        }

        try {
            return route($link);
        } catch (\Throwable) {
            return $link;
        }
    }

    public function scopeForLocation($query, string $location)
    {
        return $query->where('location', $location)->where('is_visible', true)->orderBy('sort_order');
    }

    public static function forHeader(): \Illuminate\Database\Eloquent\Collection
    {
        return static::forLocation('header')->get();
    }

    public static function forFooter(): \Illuminate\Database\Eloquent\Collection
    {
        return static::forLocation('footer')->get();
    }
}
