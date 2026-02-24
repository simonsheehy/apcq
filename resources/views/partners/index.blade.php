@extends('layouts.app')

@section('title', 'Partenaires')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-bold text-stone-900 mb-8">Partenaires</h1>

    @if($partners->isNotEmpty())
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            @foreach($partners as $partner)
                @if($partner->url)
                    <a href="{{ $partner->url }}" target="_blank" rel="noopener"
                       class="bg-white rounded-lg shadow-sm border border-stone-200 p-6 hover:shadow-md transition flex flex-col items-center justify-center min-h-[160px] group">
                        @if($partner->logo)
                            <img src="{{ asset('storage/' . $partner->logo) }}" alt="{{ $partner->name }}"
                                 class="max-h-20 w-auto object-contain group-hover:opacity-80 transition">
                        @else
                            <span class="font-semibold text-stone-700 text-center">{{ $partner->name }}</span>
                        @endif
                    </a>
                @else
                    <div class="bg-white rounded-lg shadow-sm border border-stone-200 p-6 flex flex-col items-center justify-center min-h-[160px]">
                        @if($partner->logo)
                            <img src="{{ asset('storage/' . $partner->logo) }}" alt="{{ $partner->name }}"
                                 class="max-h-20 w-auto object-contain">
                        @else
                            <span class="font-semibold text-stone-700 text-center">{{ $partner->name }}</span>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <p class="text-stone-600">Aucun partenaire pour le moment.</p>
    @endif
</div>
@endsection
