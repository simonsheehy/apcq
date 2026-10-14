<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Training;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('portal.home', [
            'trainings' => Training::published()->ordered()->with('media')->get(),
            'contactEmail' => SiteSetting::instance()->footer_email,
        ]);
    }
}
