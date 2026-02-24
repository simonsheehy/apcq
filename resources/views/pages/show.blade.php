@extends('layouts.app')

@section('title', $page->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-bold text-stone-900 mb-12">{{ $page->title }}</h1>

    <div class="prose prose-stone max-w-none text-stone-700 prose-p:text-stone-700 prose-ul:text-stone-700 prose-li:text-stone-700 prose-h2:text-stone-900 prose-h2:text-xl prose-h2:font-bold prose-h2:mb-4 prose-h2:mt-8 first:prose-h2:mt-0">
        @if(filled($page->content))
            {!! $page->content !!}
        @else
            <p>Contenu à venir.</p>
        @endif
    </div>
</div>
@endsection
