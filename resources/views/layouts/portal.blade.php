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
        <a href="{{ auth('employee')->check() ? route('portal.home') : route('portal.login') }}" class="inline-flex h-12 md:h-16">
            <img src="{{ asset('images/logo.png') }}" alt="APCQ" class="h-full w-auto object-contain">
        </a>

        @auth('employee')
            <div class="flex items-center gap-4 text-sm">
                <span class="hidden text-white/70 sm:inline">{{ auth('employee')->user()->full_name }}</span>
                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-white/30 px-4 py-1.5 font-semibold transition hover:border-white hover:bg-white hover:text-portal">
                        Déconnexion
                    </button>
                </form>
            </div>
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
