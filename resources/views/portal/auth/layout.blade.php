@extends('layouts.portal')

@section('content')
    <div class="mx-auto w-full max-w-md px-4 py-10 sm:py-16">
        <h1 class="font-display text-4xl leading-tight sm:text-5xl">@yield('title')</h1>

        @hasSection('intro')
            <p class="mt-4 text-sm text-white/70">@yield('intro')</p>
        @endif

        @if(session('status'))
            <div class="mt-6 rounded-xl bg-apcq/15 px-4 py-3 text-sm text-apcq" role="status">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-8">
            @yield('form')
        </div>
    </div>
@endsection
