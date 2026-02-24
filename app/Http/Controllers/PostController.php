<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->with('author')
            ->paginate(10);

        return view('posts.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = Post::query()
            ->where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('author')
            ->firstOrFail();

        return view('posts.show', compact('post'));
    }
}
