<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'APCQ') – Association des propriétaires de cinémas du Québec</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-stone-50 text-stone-900 font-sans antialiased flex flex-col min-h-screen">
    {{-- Bannière cookies --}}
    <div x-data="{ show: !localStorage.getItem('apcq-cookies-accepted') }"
         x-show="show"
         x-transition
         class="fixed bottom-0 left-0 right-0 z-50 bg-stone-900 text-white px-4 py-3 text-sm text-center">
        <p>
            En poursuivant votre navigation, vous acceptez le dépôt de cookies destinés à conserver vos préférences.
            <button @click="localStorage.setItem('apcq-cookies-accepted', '1'); show = false"
                    class="ml-2 underline font-medium hover:text-apcq">
                OK
            </button>
        </p>
    </div>

    {{-- Header --}}
    <header class="bg-white border-b border-stone-200 sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="text-xl font-bold tracking-tight text-stone-900">APCQ</span>
                </a>

                <div class="flex items-center gap-4" x-data="{ mobileMenuOpen: false }">
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                            class="lg:hidden p-2 -mr-2 text-stone-600 hover:text-stone-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <nav class="hidden lg:flex items-center gap-8 max-lg:absolute max-lg:top-full max-lg:left-0 max-lg:right-0 max-lg:bg-white max-lg:border-b max-lg:shadow-lg max-lg:py-4 max-lg:px-4 max-lg:z-50"
                         :class="mobileMenuOpen && 'max-lg:!flex max-lg:flex-col'"
                         @click.away="mobileMenuOpen = false">
                        @foreach($headerMenuItems ?? collect() as $item)
                            <a href="{{ $item->resolveUrl() }}"
                               @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif
                               class="text-stone-600 hover:text-apcq font-medium transition py-2 lg:py-0">{{ $item->label }}</a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-stone-900 text-stone-300 mt-auto">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="font-semibold text-white mb-4">À propos</h3>
                    <p class="text-sm">
                        {{ $siteSettings->footer_about ?? "L'Association des propriétaires de cinémas du Québec (APCQ) est une association représentée par des propriétaires ou des administrateurs de cinémas." }}
                    </p>
                </div>
                <div>
                    <h3 class="font-semibold text-white mb-4">Navigation</h3>
                    <ul class="space-y-2 text-sm">
                        @forelse($footerMenuItems ?? collect() as $item)
                            <li>
                                <a href="{{ $item->resolveUrl() }}"
                                   @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif
                                   class="hover:text-white transition">{{ $item->label }}</a>
                            </li>
                        @empty
                            <li><a href="{{ route('posts.index') }}" class="hover:text-white transition">Nouvelles</a></li>
                            <li><a href="{{ route('members.index') }}" class="hover:text-white transition">Membres</a></li>
                            <li><a href="{{ route('partners.index') }}" class="hover:text-white transition">Partenaires</a></li>
                            <li><a href="{{ route('about') }}" class="hover:text-white transition">À propos</a></li>
                            <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact</a></li>
                            <li><a href="#" class="hover:text-white transition">Zone Membre</a></li>
                            <li><a href="#" class="hover:text-white transition">Zone Conseil d'administration</a></li>
                        @endforelse
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-white mb-4">Contact</h3>
                    <p class="text-sm">
                        <strong class="text-white">L'APCQ</strong><br>
                        @if($siteSettings->footer_address)
                            {!! nl2br(e($siteSettings->footer_address)) !!}<br>
                        @else
                            63, rue King Ouest<br>
                            Sherbrooke, Québec, J1H 1P1<br>
                        @endif
                        @if($siteSettings->footer_phone || $siteSettings->footer_email)
                            @if($siteSettings->footer_phone)
                                <a href="tel:{{ preg_replace('/\D/', '', $siteSettings->footer_phone) }}" class="hover:text-white transition">{{ $siteSettings->footer_phone }}</a>
                            @endif
                            @if($siteSettings->footer_phone && $siteSettings->footer_email) | @endif
                            @if($siteSettings->footer_email)
                                <a href="mailto:{{ $siteSettings->footer_email }}" class="hover:text-white transition">{{ $siteSettings->footer_email }}</a>
                            @endif
                        @else
                            <a href="tel:+15144939898" class="hover:text-white transition">514 493-9898</a> | <a href="mailto:info@apcq.ca" class="hover:text-white transition">info@apcq.ca</a>
                        @endif
                    </p>
                    <div class="mt-4 flex gap-4">
                        <a href="https://facebook.com" target="_blank" rel="noopener" class="hover:text-white transition" aria-label="Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://linkedin.com" target="_blank" rel="noopener" class="hover:text-white transition" aria-label="LinkedIn">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="https://youtube.com" target="_blank" rel="noopener" class="hover:text-white transition" aria-label="YouTube">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-stone-700 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-stone-500">
                <p>Copyright © {{ date('Y') }}, APCQ, Tous droits réservés</p>
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
