<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->with(['author', 'tags'])
            ->paginate(10);

        return view('posts.index', [
            'posts' => $posts,
            'activeTag' => null,
        ]);
    }

    public function byTag(Tag $tag): View
    {
        $posts = Post::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('tags', fn ($query) => $query->where('tags.id', $tag->id))
            ->latest('published_at')
            ->with(['author', 'tags'])
            ->paginate(10)
            ->withQueryString();

        return view('posts.index', [
            'posts' => $posts,
            'activeTag' => $tag,
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::query()
            ->where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['author', 'tags'])
            ->firstOrFail();

        return view('posts.show', compact('post'));
    }
}
