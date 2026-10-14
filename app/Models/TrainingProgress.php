<?php

namespace App\Models;

use Database\Factories\TrainingProgressFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingProgress extends Model
{
    /** @use HasFactory<TrainingProgressFactory> */
    use HasFactory;

    /**
     * Gap (in seconds) under which two watched segments are considered continuous.
     */
    private const SEGMENT_MERGE_TOLERANCE = 1.0;

    /**
     * Maximum playback speed offered by the player, used to cap the watched time.
     */
    private const MAX_PLAYBACK_RATE = 2;

    /**
     * Extra seconds allowed on top of the elapsed time, to absorb network latency.
     */
    private const GROWTH_MARGIN_SECONDS = 15;

    protected $table = 'training_progress';

    protected $fillable = [
        'employee_id',
        'training_id',
        'watched_segments',
        'watched_seconds',
        'video_duration',
        'last_position',
        'completed_at',
    ];

    protected $attributes = [
        'watched_segments' => '[]',
        'watched_seconds' => 0,
        'video_duration' => 0,
        'last_position' => 0,
    ];

    protected function casts(): array
    {
        return [
            'watched_segments' => 'array',
            'watched_seconds' => 'integer',
            'video_duration' => 'integer',
            'last_position' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    protected function progressPercent(): Attribute
    {
        return Attribute::get(function (): int {
            if ($this->video_duration === 0) {
                return 0;
            }

            return (int) min(100, floor($this->watched_seconds / $this->video_duration * 100));
        });
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Merges the segments reported by the player into the watched portions and updates completion.
     *
     * Total watched time cannot exceed the real time elapsed since the first report, at the maximum
     * playback speed. A report that claims more is ignored; the player resends all its segments, so
     * they are counted on a later report once enough time has passed.
     *
     * @param  list<array{0: float|int, 1: float|int}>  $segments
     */
    public function recordViewing(array $segments, float $videoDuration, float $position): void
    {
        $videoDuration = max(0, $videoDuration);

        $mergedSegments = self::mergeSegments([
            ...($this->watched_segments ?? []),
            ...self::clampSegments($segments, $videoDuration),
        ]);
        $watchedSeconds = (int) floor(self::totalLength($mergedSegments));

        $elapsedSeconds = $this->exists ? $this->created_at->diffInSeconds(now()) : 0;
        $maximumWatchedSeconds = $elapsedSeconds * self::MAX_PLAYBACK_RATE + self::GROWTH_MARGIN_SECONDS;

        if ($watchedSeconds <= $maximumWatchedSeconds) {
            $this->watched_segments = $mergedSegments;
            $this->watched_seconds = $watchedSeconds;
        }

        $this->video_duration = (int) round($videoDuration);
        $this->last_position = (int) floor(min(max(0, $position), $videoDuration));

        if (! $this->isCompleted() && $this->progress_percent >= config('trainings.completion_threshold')) {
            $this->completed_at = now();
        }

        $this->save();
    }

    /**
     * @param  list<array{0: float|int, 1: float|int}>  $segments
     * @return list<array{0: float, 1: float}>
     */
    private static function clampSegments(array $segments, float $videoDuration): array
    {
        return collect($segments)
            ->map(fn (array $segment): array => [
                round(min(max(0, (float) $segment[0]), $videoDuration), 1),
                round(min(max(0, (float) $segment[1]), $videoDuration), 1),
            ])
            ->filter(fn (array $segment): bool => $segment[1] > $segment[0])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{0: float|int, 1: float|int}>  $segments
     * @return list<array{0: float, 1: float}>
     */
    public static function mergeSegments(array $segments): array
    {
        $sorted = collect($segments)
            ->map(fn (array $segment): array => [(float) $segment[0], (float) $segment[1]])
            ->sortBy(fn (array $segment): float => $segment[0])
            ->values();

        $merged = [];

        foreach ($sorted as [$start, $end]) {
            $last = array_key_last($merged);

            if ($last !== null && $start <= $merged[$last][1] + self::SEGMENT_MERGE_TOLERANCE) {
                $merged[$last][1] = max($merged[$last][1], $end);

                continue;
            }

            $merged[] = [$start, $end];
        }

        return $merged;
    }

    /**
     * @param  list<array{0: float, 1: float}>  $segments
     */
    private static function totalLength(array $segments): float
    {
        return array_sum(array_map(fn (array $segment): float => $segment[1] - $segment[0], $segments));
    }
}
