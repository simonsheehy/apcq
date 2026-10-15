<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Training;
use App\Models\TrainingFaq;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('portal.home', [
            'trainings' => Training::published()->ordered()->with('media')->get(),
            'progressByTraining' => $request->user('employee')?->trainingProgress()->get()->keyBy('training_id') ?? collect(),
            'faqs' => TrainingFaq::published()->ordered()->get(),
            'contactEmail' => SiteSetting::instance()->footer_email,
        ]);
    }
}
