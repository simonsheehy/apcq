<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingDocumentDownload;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrainingDocumentController extends Controller
{
    public function show(Request $request, Training $training, Media $document): StreamedResponse
    {
        abort_unless(
            $document->model_type === $training->getMorphClass()
                && $document->model_id === $training->id
                && $document->collection_name === 'documents',
            404,
        );

        TrainingDocumentDownload::record($request->user('employee'), $training, $document);

        return $document->toResponse($request);
    }
}
