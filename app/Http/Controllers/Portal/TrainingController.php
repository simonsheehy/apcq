<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function show(Request $request, Training $training): View
    {
        $employee = $request->user('employee');

        $moduleNumber = Training::published()->ordered()->pluck('id')->search($training->id) + 1;

        return view('portal.trainings.show', [
            'training' => $training,
            'moduleNumber' => $moduleNumber,
            'documents' => $training->getMedia('documents'),
            'progress' => $training->progress()->whereBelongsTo($employee)->first(),
            'downloadedDocumentIds' => $employee->documentDownloads()->where('training_id', $training->id)->pluck('media_id'),
        ]);
    }
}
