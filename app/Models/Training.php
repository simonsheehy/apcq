<?php

namespace App\Models;

use App\Enums\TrainingStatus;
use Database\Factories\TrainingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Training extends Model implements HasMedia
{
    /** @use HasFactory<TrainingFactory> */
    use HasFactory;

    use InteractsWithMedia;

    /**
     * Keeps only the basic controls (play/pause, timeline, volume, subtitles, quality and
     * fullscreen). Like, watch later and share buttons are not URL options: they are hidden in
     * the video's embed settings on vimeo.com.
     */
    private const VIMEO_PLAYER_OPTIONS = [
        'dnt' => 1,
        'title' => 0,
        'byline' => 0,
        'portrait' => 0,
        'vimeo_logo' => 0,
        'speed' => 0,
        'pip' => 0,
        'airplay' => 0,
        'chromecast' => 0,
        'transcript' => 0,
    ];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'vimeo_video_id',
        'video_duration',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => TrainingStatus::class,
            'sort_order' => 'integer',
            'video_duration' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Training $training): void {
            $hasManualDuration = $training->isDirty('video_duration') && filled($training->video_duration);

            if ($training->isDirty('vimeo_video_id') && ! $hasManualDuration) {
                $training->video_duration = self::fetchVimeoDuration($training->vimeo_video_id);
            }
        });
    }

    /**
     * Reads the video length from Vimeo's oEmbed endpoint. Domain-restricted videos only answer
     * when the request comes from an allowed domain, hence the Referer header.
     */
    public static function fetchVimeoDuration(?string $vimeoVideoId): ?int
    {
        if (blank($vimeoVideoId)) {
            return null;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders(['Referer' => config('app.url')])
                ->get('https://vimeo.com/api/oembed.json', [
                    'url' => "https://vimeo.com/{$vimeoVideoId}",
                ]);
        } catch (ConnectionException) {
            return null;
        }

        $duration = $response->successful() ? (int) $response->json('duration') : 0;

        return $duration > 0 ? $duration : null;
    }

    public function progress(): HasMany
    {
        return $this->hasMany(TrainingProgress::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TrainingDocument::class)->orderBy('sort_order')->orderBy('id');
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

        return "https://player.vimeo.com/video/{$this->vimeo_video_id}?".http_build_query(self::VIMEO_PLAYER_OPTIONS);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->useDisk('public');
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
