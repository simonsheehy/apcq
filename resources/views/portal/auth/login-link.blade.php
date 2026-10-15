@extends('portal.auth.layout')

@section('title', 'Bonjour, '.$employee->first_name)

@section('intro', 'Cliquez sur le bouton pour vous connecter au Centre d’expertise. Ce lien ne fonctionne qu’une seule fois.')

@section('form')
    <form method="POST" action="{{ route('portal.login-link.consume', ['employee' => $employee, 'token' => $token]) }}" class="flex flex-col gap-5">
        @csrf

        <x-portal.button>Me connecter</x-portal.button>
    </form>
@endsection
