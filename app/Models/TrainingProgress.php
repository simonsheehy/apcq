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

    /**
     * Most watched time a single report can add. The player reports every 10 seconds, so this
     * leaves room to catch up after failed reports while preventing a whole video from being
     * claimed at once after a long idle period.
     */
    private const MAX_GROWTH_PER_REPORT_SECONDS = 60;

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
     * Watched time cannot grow faster than the real time elapsed since the previous change, at the
     * maximum playback speed, nor by more than a fixed amount per report. Newly reported portions
     * beyond that allowance are dropped (earliest kept first); the player resends all its
     * segments, so they are counted on a later report.
     *
     * @param  list<array{0: float|int, 1: float|int}>  $segments
     */
    public function recordViewing(array $segments, int $videoDuration, float $position): void
    {
        $videoDuration = max(0, $videoDuration);
        $knownSegments = $this->watched_segments ?? [];

        $elapsedSeconds = $this->exists ? (int) floor($this->updated_at->diffInSeconds(now())) : 0;
        $allowedGrowth = min(
            $elapsedSeconds * self::MAX_PLAYBACK_RATE + self::GROWTH_MARGIN_SECONDS,
            self::MAX_GROWTH_PER_REPORT_SECONDS,
        );

        $newPortions = self::subtractSegments(
            self::mergeSegments(self::clampSegments($segments, $videoDuration)),
            $knownSegments,
        );

        $mergedSegments = self::mergeSegments([
            ...$knownSegments,
            ...self::truncateToLength($newPortions, $allowedGrowth),
        ]);

        $this->watched_segments = $mergedSegments;
        $this->watched_seconds = (int) floor(self::totalLength($mergedSegments));
        $this->video_duration = $videoDuration;
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
    private static function clampSegments(array $segments, int $videoDuration): array
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
     * Removes from each segment the portions already covered by the known segments.
     *
     * @param  list<array{0: float, 1: float}>  $segments
     * @param  list<array{0: float, 1: float}>  $knownSegments
     * @return list<array{0: float, 1: float}>
     */
    private static function subtractSegments(array $segments, array $knownSegments): array
    {
        $remaining = [];

        foreach ($segments as [$start, $end]) {
            foreach ($knownSegments as [$knownStart, $knownEnd]) {
                if ($knownEnd <= $start || $knownStart >= $end) {
                    continue;
                }

                if ($knownStart > $start) {
                    $remaining[] = [$start, (float) $knownStart];
                }

                $start = max($start, (float) $knownEnd);

                if ($start >= $end) {
                    continue 2;
                }
            }

            $remaining[] = [$start, $end];
        }

        return $remaining;
    }

    /**
     * Keeps segments in chronological order until their total length reaches the limit.
     *
     * @param  list<array{0: float, 1: float}>  $segments
     * @return list<array{0: float, 1: float}>
     */
    private static function truncateToLength(array $segments, float $maximumLength): array
    {
        $kept = [];

        foreach ($segments as [$start, $end]) {
            if ($maximumLength <= 0) {
                break;
            }

            $length = min($end - $start, $maximumLength);
            $kept[] = [$start, round($start + $length, 1)];
            $maximumLength -= $length;
        }

        return $kept;
    }

    /**
     * @param  list<array{0: float, 1: float}>  $segments
     */
    private static function totalLength(array $segments): float
    {
        return array_sum(array_map(fn (array $segment): float => $segment[1] - $segment[0], $segments));
    }
}
