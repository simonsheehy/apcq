@extends('portal.auth.layout')

@section('title', 'Mot de passe oublié')

@section('intro', 'Indiquez votre adresse courriel et nous vous enverrons un lien pour choisir un nouveau mot de passe.')

@section('form')
    <form method="POST" action="{{ route('portal.password.email') }}" class="flex flex-col gap-5">
        @csrf

        <x-portal.input name="email" label="Courriel" type="email" autocomplete="email" required autofocus />

        <x-portal.button>Envoyer le lien</x-portal.button>

        <a href="{{ route('portal.login') }}" class="text-center text-sm text-white/70 underline-offset-4 hover:text-white hover:underline">
            Retour à la connexion
        </a>
    </form>
@endsection
