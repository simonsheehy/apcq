<?php

namespace App\Http\Controllers;

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

        return view('home', [
            'featuredPost' => $featuredPost,
            'recentPosts' => $recentPosts,
        ]);
    }
}
