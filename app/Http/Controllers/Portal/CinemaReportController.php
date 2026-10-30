<?php

namespace App\Http\Controllers\Portal;

use App\Exports\TrainingReportExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CinemaReportController extends Controller
{
    /**
     * Downloads the training report of the administrator's cinema, deactivated employees included
     * so the history of past employees is kept.
     */
    public function __invoke(Request $request, TrainingReportExport $export): BinaryFileResponse
    {
        $cinema = $request->user('employee')->cinema;

        return $export->download(
            ['cinema_id' => $cinema->id, 'include_deactivated' => true],
            fileName: 'rapport-formations-'.Str::slug($cinema->name).'-'.now()->timezone('America/Toronto')->format('Y-m-d').'.xlsx',
        );
    }
}
