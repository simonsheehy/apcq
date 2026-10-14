<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('portal.home', [
            'trainings' => Training::published()->ordered()->with('media')->get(),
            'progressByTraining' => $request->user('employee')->trainingProgress()->get()->keyBy('training_id'),
            'contactEmail' => SiteSetting::instance()->footer_email,
        ]);
    }
}
