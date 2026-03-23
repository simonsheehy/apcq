<?php

namespace App\Http\Controllers;

use App\Models\Member;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::query()
            ->with('media')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('members.index', compact('members'));
    }
}
