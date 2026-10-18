<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Centre d’expertise') – APCQ</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-portal font-sans text-white antialiased">
    <header class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-6 sm:px-6">
        <a href="{{ route('portal.home') }}" class="inline-flex h-12 md:h-16">
            <img src="{{ asset('images/logo.png') }}" alt="APCQ" class="h-full w-auto object-contain">
        </a>

        @auth('employee')
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('portal.profile.show') }}" class="inline-flex items-center gap-2 text-white/70 transition hover:text-white" @if(request()->routeIs('portal.profile.show')) aria-current="page" @endif>
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <span class="hidden sm:inline">{{ auth('employee')->user()->full_name }}</span>
                    <span class="sm:hidden">Mon profil</span>
                </a>
                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-white/30 px-4 py-1.5 font-semibold transition hover:border-white hover:bg-white hover:text-portal">
                        Déconnexion
                    </button>
                </form>
            </div>
        @else
            @unless(request()->routeIs('portal.login'))
                <a href="{{ route('portal.login') }}" class="rounded-full border border-white/30 px-4 py-1.5 text-sm font-semibold transition hover:border-white hover:bg-white hover:text-portal">
                    Se connecter
                </a>
            @endunless
        @endauth
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="mx-auto w-full max-w-7xl px-4 py-8 text-xs text-white/50 sm:px-6">
        © {{ date('Y') }} Association des propriétaires de cinémas du Québec
    </footer>
    @stack('scripts')
</body>
</html>
