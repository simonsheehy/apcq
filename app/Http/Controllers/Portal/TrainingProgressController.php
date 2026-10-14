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
            'segments' => ['present', 'array', 'max:500'],
            'segments.*' => ['array', 'size:2'],
            'segments.*.*' => ['numeric', 'min:0'],
            'duration' => ['required', 'numeric', 'min:1', 'max:21600'],
            'position' => ['required', 'numeric', 'min:0'],
        ]);

        $progress = TrainingProgress::firstOrNew([
            'employee_id' => $request->user('employee')->id,
            'training_id' => $training->id,
        ]);

        $progress->recordViewing(
            $validated['segments'],
            (float) $validated['duration'],
            (float) $validated['position'],
        );

        return response()->json([
            'progress_percent' => $progress->progress_percent,
            'completed' => $progress->isCompleted(),
        ]);
    }
}
