@extends('layouts.app')

@section('title', 'Contact')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-bold text-stone-900 mb-8">Contactez-nous</h1>

    <p class="text-stone-700 mb-8">N'hésitez pas à nous contacter pour tous renseignements ou commentaires.</p>

    <div class="grid lg:grid-cols-3 gap-12">
        <div class="lg:col-span-2">
            @livewire('contact-form')
        </div>

        <div class="bg-stone-700 rounded-lg p-6">
            <h3 class="font-semibold text-white mb-4">Coordonnées de l'APCQ</h3>
            <address class="text-white not-italic">
                <img src="{{ asset('images/logo.png') }}" alt="APCQ" class="h-12 object-contain mb-4">
                @if($siteSettings->footer_address)
                    {!! nl2br(e($siteSettings->footer_address)) !!}<br>
                @else
                    63, rue King Ouest<br>
                    Sherbrooke, Québec, J1H 1P1<br>
                @endif
                Canada
            </address>
            <div class="mt-4 space-y-2">
                @if($siteSettings->footer_phone)
                    <a href="tel:{{ preg_replace('/\D/', '', $siteSettings->footer_phone) }}" class="block text-apcq hover:underline font-medium">{{ $siteSettings->footer_phone }}</a>
                @else
                    <a href="tel:+15144939898" class="block text-apcq hover:underline font-medium">514 493-9898</a>
                @endif
                @if($siteSettings->footer_email)
                    <a href="mailto:{{ $siteSettings->footer_email }}" class="block text-apcq hover:underline font-medium">{{ $siteSettings->footer_email }}</a>
                @else
                    <a href="mailto:info@apcq.ca" class="block text-apcq hover:underline font-medium">info@apcq.ca</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
