<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingDocument;
use App\Models\TrainingDocumentDownload;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrainingDocumentController extends Controller
{
    public function show(Request $request, Training $training, TrainingDocument $document): StreamedResponse
    {
        abort_unless($document->training_id === $training->id, 404);

        $file = $document->file();

        abort_if($file === null, 404);

        TrainingDocumentDownload::record($request->user('employee'), $document);

        return $file->toResponse($request);
    }
}
