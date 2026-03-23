<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(12)
                    ->columns(['lg' => 12])
                    ->schema([
                        Section::make('Éditeur')
                            ->columnSpan(['lg' => 9])
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug($state))),
                                TextInput::make('slug')
                                    ->label('URL (slug)')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Textarea::make('excerpt')
                                    ->label('Extrait')
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->columnSpanFull(),
                                RichEditor::make('content')
                                    ->label('Contenu')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),
                        Group::make([
                            Section::make('Image à la une')
                                ->schema([
                                    FileUpload::make('featured_image')
                                        ->label('Image')
                                        ->image()
                                        ->directory('posts')
                                        ->disk('public'),
                                    FileUpload::make('gallery_images')
                                        ->label('Galerie d’images')
                                        ->image()
                                        ->multiple()
                                        ->reorderable()
                                        ->appendFiles()
                                        ->directory('posts')
                                        ->disk('public'),
                                ])
                                ->columns(1),
                            Section::make('Publication')
                                ->schema([
                                    Select::make('user_id')
                                        ->label('Auteur')
                                        ->relationship('author', 'name')
                                        ->required()
                                        ->default(auth()->id()),
                                    DateTimePicker::make('published_at')
                                        ->label('Date de publication')
                                        ->native(false)
                                        ->weekStartsOnSunday(),
                                ])
                                ->columns(1),
                            Section::make('Catégories / Tags')
                                ->schema([
                                    Select::make('tags')
                                        ->label('Tags')
                                        ->multiple()
                                        ->relationship('tags', 'name')
                                        ->preload()
                                        ->searchable()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Nom')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->maxLength(255)
                                                ->unique('tags', 'slug')
                                                ->default(fn (callable $get) => Str::slug((string) $get('name'))),
                                        ]),
                                ])
                                ->columns(1),
                        ])
                            ->columnSpan(['lg' => 3])
                            ->columns(1),
                    ]),
            ]);
    }
}
