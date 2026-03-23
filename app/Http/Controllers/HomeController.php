<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Post;

class HomeController extends Controller
{
    public function __invoke()
    {
        $featuredPost = Post::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->with('author')
            ->first();

        $recentQuery = Post::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->with('author');

        if ($featuredPost) {
            $recentQuery->where('id', '!=', $featuredPost->id);
        }

        $recentPosts = $recentQuery->limit(5)->get();
        $upcomingEvents = Event::query()
            ->published()
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(4)
            ->get();

        return view('home', [
            'featuredPost' => $featuredPost,
            'recentPosts' => $recentPosts,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
