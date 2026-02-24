<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(12)
                    ->columns(12)
                    ->schema([
                        Section::make('Contenu')
                            ->columnSpan(['lg' => 9])
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug($state ?? ''))),
                                TextInput::make('slug')
                                    ->label('URL (slug)')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(\App\Models\Page::class, 'slug', ignoreRecord: true),
                                Textarea::make('excerpt')
                                    ->label('Extrait')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                RichEditor::make('content')
                                    ->label('Contenu')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),
                        Section::make('Publication')
                            ->columnSpan(['lg' => 3])
                            ->schema([
                                Toggle::make('is_published')
                                    ->label('Publié')
                                    ->default(true),
                                TextInput::make('sort_order')
                                    ->label('Ordre')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }
}
