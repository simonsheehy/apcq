<?php

namespace App\Http\Controllers;

use App\Models\BoardMember;
use Illuminate\Contracts\View\View;

class BoardMemberController extends Controller
{
    public function index(): View
    {
        $boardMembers = BoardMember::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12);

        return view('board-members.index', [
            'boardMembers' => $boardMembers,
        ]);
    }

    public function show(string $slug): View
    {
        $boardMember = BoardMember::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('board-members.show', [
            'boardMember' => $boardMember,
        ]);
    }
}
