<?php

use App\Models\Employee;
use App\Models\Training;
use App\Models\TrainingProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Starts a progress record, then lets enough time pass for any realistic report to be accepted.
 */
function startedProgress(): TrainingProgress
{
    $progress = TrainingProgress::create([
        'employee_id' => Employee::factory()->create()->id,
        'training_id' => Training::factory()->create()->id,
    ]);

    test()->travel(10)->minutes();

    return $progress;
}

it('merges overlapping and adjacent segments', function () {
    expect(TrainingProgress::mergeSegments([[30, 40], [0, 10], [10.5, 20], [35, 50]]))
        ->toBe([[0.0, 20.0], [30.0, 50.0]]);
});

it('counts a portion watched twice only once', function () {
    $progress = startedProgress();
    $progress->recordViewing([[0, 10]], 100, 10);

    $this->travel(30)->seconds();
    $progress->recordViewing([[0, 10], [5, 12]], 100, 12);

    expect($progress->watched_seconds)->toBe(12);
});

it('does not count skipped portions', function () {
    $progress = startedProgress();
    $progress->recordViewing([[0, 10], [80, 90]], 100, 90);

    expect($progress->watched_seconds)->toBe(20)
        ->and($progress->last_position)->toBe(90);
});

it('ignores reports claiming more viewing than time allows', function () {
    $progress = TrainingProgress::create([
        'employee_id' => Employee::factory()->create()->id,
        'training_id' => Training::factory()->create()->id,
    ]);

    $this->travel(10)->seconds();
    $progress->recordViewing([[0, 400]], 600, 400);

    expect($progress->watched_seconds)->toBe(0)
        ->and($progress->last_position)->toBe(400);

    $this->travel(200)->seconds();
    $progress->recordViewing([[0, 400]], 600, 400);

    expect($progress->watched_seconds)->toBe(400);
});

it('clamps segments to the video duration', function () {
    $progress = startedProgress();
    $progress->recordViewing([[-5, 8], [95, 130]], 100, 130);

    expect($progress->watched_seconds)->toBe(13)
        ->and($progress->last_position)->toBe(100);
});

it('completes the training at the configured threshold', function (int $threshold, int $watched, bool $isCompleted) {
    config(['trainings.completion_threshold' => $threshold]);
    $progress = startedProgress();

    $progress->recordViewing([[0, $watched]], 100, $watched);

    expect($progress->isCompleted())->toBe($isCompleted);
})->with([
    'below default threshold' => [90, 89, false],
    'at default threshold' => [90, 90, true],
    'custom threshold' => [50, 50, true],
]);

it('stays completed after rewatching from the start', function () {
    $progress = startedProgress();
    $progress->recordViewing([[0, 10]], 10, 10);
    $completedAt = $progress->completed_at;

    $this->travel(1)->minutes();
    $progress->recordViewing([[0, 2]], 10, 2);

    expect($progress->completed_at->equalTo($completedAt))->toBeTrue();
});
