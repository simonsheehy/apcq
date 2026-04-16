<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->published()
            ->orderByRaw('starts_at IS NULL')
            ->orderBy('starts_at')
            ->paginate(9);

        return view('events.index', [
            'events' => $events,
        ]);
    }

    public function show(string $slug): View
    {
        $event = Event::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('events.show', [
            'event' => $event,
        ]);
    }
}
