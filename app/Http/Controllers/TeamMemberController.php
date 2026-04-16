<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\Contracts\View\View;

class TeamMemberController extends Controller
{
    public function index(): View
    {
        $teamMembers = TeamMember::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12);

        return view('team-members.index', [
            'teamMembers' => $teamMembers,
        ]);
    }

    public function show(string $slug): View
    {
        $teamMember = TeamMember::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('team-members.show', [
            'teamMember' => $teamMember,
        ]);
    }
}
