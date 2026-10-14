<?php

namespace App\Models;

use App\Enums\TrainingStatus;
use Database\Factories\TrainingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Training extends Model implements HasMedia
{
    /** @use HasFactory<TrainingFactory> */
    use HasFactory;

    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'vimeo_video_id',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => TrainingStatus::class,
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->useDisk('public');

        $this->addMediaCollection('documents')
            ->useDisk('local');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('card')
            ->performOnCollections('cover')
            ->width(640)
            ->height(360)
            ->nonQueued();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', TrainingStatus::Published);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
