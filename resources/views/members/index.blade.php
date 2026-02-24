@extends('layouts.app')

@section('title', 'Membres')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-bold text-stone-900 mb-8">Membres</h1>

    @if($members->isNotEmpty())
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($members as $member)
                <div class="bg-white rounded-lg shadow-sm border border-stone-200 p-6 hover:shadow-md transition">
                    <h3 class="font-semibold text-stone-900 text-lg mb-1">{{ $member->name }}</h3>
                    <p class="text-apcq font-medium mb-2">{{ $member->cinema_name }}</p>
                    @if($member->city)
                        <p class="text-stone-600 text-sm mb-2">{{ $member->city }}</p>
                    @endif
                    @if($member->address)
                        <p class="text-stone-600 text-sm">{{ $member->address }}</p>
                    @endif
                    @if($member->phone || $member->email)
                        <div class="mt-3 space-y-1 text-sm">
                            @if($member->phone)
                                <a href="tel:{{ $member->phone }}" class="block text-apcq hover:underline">{{ $member->phone }}</a>
                            @endif
                            @if($member->email)
                                <a href="mailto:{{ $member->email }}" class="block text-apcq hover:underline">{{ $member->email }}</a>
                            @endif
                        </div>
                    @endif
                    @if($member->website)
                        <a href="{{ $member->website }}" target="_blank" rel="noopener" class="text-sm text-apcq hover:underline mt-2 inline-block">
                            Site web
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-12">
            {{ $members->links() }}
        </div>
    @else
        <p class="text-stone-600">Aucun membre pour le moment.</p>
    @endif
</div>
@endsection
