<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingProgressController extends Controller
{
    public function store(Request $request, Training $training): JsonResponse
    {
        $validated = $request->validate([
            'segments' => ['present', 'array', 'list', 'max:500'],
            'segments.*' => ['array', 'list', 'size:2'],
            'segments.*.0' => ['required', 'numeric', 'min:0'],
            'segments.*.1' => ['required', 'numeric', 'min:0'],
            'duration' => ['required', 'numeric', 'min:1', 'max:21600'],
            'position' => ['required', 'numeric', 'min:0'],
        ]);

        $progress = TrainingProgress::createOrFirst([
            'employee_id' => $request->user('employee')->id,
            'training_id' => $training->id,
        ]);

        $progress->recordViewing(
            $validated['segments'],
            $this->videoDuration($training, $progress, (float) $validated['duration']),
            (float) $validated['position'],
        );

        return response()->json([
            'progress_percent' => $progress->progress_percent,
            'completed' => $progress->isCompleted(),
        ]);
    }

    /**
     * The duration reported by the player is only trusted when the training has none on record,
     * and only the first time: it cannot be lowered afterwards to inflate the percentage.
     */
    private function videoDuration(Training $training, TrainingProgress $progress, float $reportedDuration): int
    {
        if ($training->video_duration) {
            return $training->video_duration;
        }

        if ($progress->video_duration > 0) {
            return $progress->video_duration;
        }

        return (int) round($reportedDuration);
    }
}
