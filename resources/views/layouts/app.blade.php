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
    @cookieconsentscripts
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased flex flex-col min-h-screen">
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
    <header class="sticky top-0 z-40 border-b border-black bg-black/95 backdrop-blur">
        <div class="border-b border-cyan-400 bg-apcq text-white">
            <div class="mx-auto flex h-11 w-full max-w-7xl items-center justify-between px-4 sm:px-6">
                <p class="text-xs font-medium tracking-wide">Association des propriétaires de cinémas du Québec</p>
                <div class="hidden items-center gap-3 md:flex">
                    @if(filled($siteSettings->footer_facebook_url))
                        <a href="{{ $siteSettings->footer_facebook_url }}" target="_blank" rel="noopener" class="hover:text-white/85 transition" aria-label="Facebook">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    @endif
                    @if(filled($siteSettings->footer_linkedin_url))
                        <a href="{{ $siteSettings->footer_linkedin_url }}" target="_blank" rel="noopener" class="hover:text-white/85 transition" aria-label="LinkedIn">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                    @endif
                    @if(filled($siteSettings->footer_youtube_url))
                        <a href="{{ $siteSettings->footer_youtube_url }}" target="_blank" rel="noopener" class="hover:text-white/85 transition" aria-label="YouTube">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 text-white" x-data="{ mobileMenuOpen: false }">
            <div class="flex h-16 items-center justify-between lg:h-24">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="inline-flex h-18 text-sm font-black text-white"><img src="{{ asset('images/logo.png') }}" alt="APCQ" class="w-full h-full object-contain"></span>
                </a>

                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2 text-white/80 hover:text-white lg:hidden"
                        aria-label="Ouvrir le menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <nav class="hidden items-center gap-7 lg:flex">
                    @foreach($headerMenuItems ?? collect() as $item)
                        @if($item->children->isNotEmpty())
                            {{-- pt-2 (sans mt) : la marge créait un « trou » où le survol quittait .group et le menu se fermait --}}
                            <div class="group relative">
                                <a href="{{ $item->resolveUrl() }}"
                                   @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif
                                   class="inline-flex items-center gap-1 text-sm font-semibold text-white/85 hover:text-white transition">
                                    {{ $item->label }}
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </a>
                                <div class="invisible absolute left-0 top-full z-50 min-w-56 pt-2 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100">
                                    <div class="rounded-lg border border-slate-700 bg-black p-2">
                                        @foreach($item->children as $child)
                                            <a href="{{ $child->resolveUrl() }}"
                                               @if($child->open_in_new_tab) target="_blank" rel="noopener" @endif
                                               class="block rounded-md px-3 py-2 text-sm font-medium text-white/85 hover:bg-white/10 hover:text-white transition">
                                                {{ $child->label }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ $item->resolveUrl() }}"
                               @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif
                               class="text-sm font-semibold text-white/85 hover:text-white transition">{{ $item->label }}</a>
                        @endif
                    @endforeach
                </nav>
            </div>

            <nav x-show="mobileMenuOpen"
                 x-transition
                 @click.away="mobileMenuOpen = false"
                 class="space-y-2 border-t border-white/15 py-4 lg:hidden">
                @foreach($headerMenuItems ?? collect() as $item)
                    <a href="{{ $item->resolveUrl() }}"
                       @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif
                       class="block rounded-md px-3 py-2 text-sm font-medium text-white/85 hover:bg-white/10 hover:text-white transition">{{ $item->label }}</a>
                    @if($item->children->isNotEmpty())
                        <div class="mt-1 space-y-1 pl-4">
                            @foreach($item->children as $child)
                                <a href="{{ $child->resolveUrl() }}"
                                   @if($child->open_in_new_tab) target="_blank" rel="noopener" @endif
                                   class="block rounded-md px-3 py-2 text-sm text-white/70 hover:bg-white/10 hover:text-white transition">{{ $child->label }}</a>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-auto bg-black text-white">
        <div class="mx-auto w-full max-w-7xl px-4 py-14 sm:px-6">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white">À propos</h3>
                    <p class="text-sm leading-6 text-slate-300">
                        {{ $siteSettings->footer_about ?? "L'Association des propriétaires de cinémas du Québec (APCQ) est une association représentée par des propriétaires ou des administrateurs de cinémas." }}
                    </p>
                </div>
                <div>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white">Navigation</h3>
                    <ul class="space-y-2 text-sm">
                        @forelse($footerMenuItems ?? collect() as $item)
                            <li>
                                <a href="{{ $item->resolveUrl() }}"
                                   @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif
                                   class="text-slate-300 hover:text-white transition">{{ $item->label }}</a>
                                @if($item->children->isNotEmpty())
                                    <ul class="mt-2 space-y-1 pl-4 text-xs">
                                        @foreach($item->children as $child)
                                            <li>
                                                <a href="{{ $child->resolveUrl() }}"
                                                   @if($child->open_in_new_tab) target="_blank" rel="noopener" @endif
                                                   class="text-slate-400 hover:text-white transition">{{ $child->label }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
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
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white">Contact</h3>
                    <p class="text-sm leading-6">
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
                    <p class="mt-4">
                        <strong class="text-white block mb-2">Partenaire</strong>
                        <img src="{{ asset('/images/logo-sodec.png') }}" alt="Société de développement des entreprises culturelles (SODEC)" class="h-12 object-contain">
                    </p>
                    @if(filled($siteSettings->footer_facebook_url) || filled($siteSettings->footer_linkedin_url) || filled($siteSettings->footer_youtube_url))
                        <div class="mt-4 flex gap-4">
                            @if(filled($siteSettings->footer_facebook_url))
                                <a href="{{ $siteSettings->footer_facebook_url }}" target="_blank" rel="noopener" class="hover:text-white transition" aria-label="Facebook">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                            @endif
                            @if(filled($siteSettings->footer_linkedin_url))
                                <a href="{{ $siteSettings->footer_linkedin_url }}" target="_blank" rel="noopener" class="hover:text-white transition" aria-label="LinkedIn">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                </a>
                            @endif
                            @if(filled($siteSettings->footer_youtube_url))
                                <a href="{{ $siteSettings->footer_youtube_url }}" target="_blank" rel="noopener" class="hover:text-white transition" aria-label="YouTube">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-slate-700 pt-8 text-sm text-slate-400 sm:flex-row">
                <p>Copyright © {{ date('Y') }}, APCQ, Tous droits réservés</p>
            </div>
        </div>
    </footer>

    @cookieconsentview
    @livewireScripts
    @stack('scripts')
</body>
</html>
