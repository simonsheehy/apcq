<?php

namespace App\Models;

use App\Enums\TrainingStatus;
use Database\Factories\TrainingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function progress(): HasMany
    {
        return $this->hasMany(TrainingProgress::class);
    }

    public function documentDownloads(): HasMany
    {
        return $this->hasMany(TrainingDocumentDownload::class);
    }

    public function isPublished(): bool
    {
        return $this->status === TrainingStatus::Published;
    }

    public function portalUrl(): string
    {
        return route('portal.trainings.show', ['publishedTraining' => $this->slug]);
    }

    public function vimeoEmbedUrl(): ?string
    {
        if (blank($this->vimeo_video_id)) {
            return null;
        }

        return "https://player.vimeo.com/video/{$this->vimeo_video_id}?dnt=1&title=0&byline=0&portrait=0";
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
