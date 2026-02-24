<?php

namespace App\Http\Controllers;

use App\Models\Partner;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('partners.index', compact('partners'));
    }
}
