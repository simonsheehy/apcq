<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'label',
        'link',
        'sort_order',
        'location',
        'parent_id',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('is_visible', true)
            ->orderBy('sort_order');
    }

    public static function forHeader(): Collection
    {
        return static::query()
            ->where('location', 'header')
            ->where('is_visible', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }

    public static function forFooter(): Collection
    {
        return static::query()
            ->where('location', 'footer')
            ->where('is_visible', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }
}
