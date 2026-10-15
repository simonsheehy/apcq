@extends('portal.auth.layout')

@section('title', 'Connexion')

@section('intro', 'Entrez l’adresse courriel à laquelle vous avez reçu votre invitation : nous vous enverrons un lien pour vous connecter, sans mot de passe.')

@section('form')
    <form method="POST" action="{{ route('portal.login-link.store') }}" class="flex flex-col gap-5">
        @csrf

        <x-portal.input name="email" label="Courriel" type="email" autocomplete="email" required autofocus />

        <x-portal.button>Recevoir mon lien de connexion</x-portal.button>
    </form>

    <details class="group mt-10 border-t border-white/10 pt-6" @if($errors->hasAny(['password', 'password_email'])) open @endif>
        <summary class="cursor-pointer list-none text-sm text-white/70 transition hover:text-white">
            <span class="underline-offset-4 group-open:underline">Vous avez un mot de passe ? Connectez-vous avec celui-ci</span>
        </summary>

        <form method="POST" action="{{ route('portal.login.store') }}" class="mt-6 flex flex-col gap-5">
            @csrf

            <x-portal.input name="password_email" label="Courriel" type="email" autocomplete="username" required />
            <x-portal.input name="password" label="Mot de passe" type="password" autocomplete="current-password" required />

            <div class="flex items-center justify-between gap-4 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded border-white/30 bg-white/5 accent-apcq">
                    Se souvenir de moi
                </label>
                <a href="{{ route('portal.password.request') }}" class="text-white/70 underline-offset-4 hover:text-white hover:underline">
                    Mot de passe oublié ?
                </a>
            </div>

            <x-portal.button variant="secondary">Se connecter</x-portal.button>
        </form>
    </details>
@endsection
