<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(12)
                    ->columns(['lg' => 12])
                    ->schema([
                        Section::make('Informations')
                            ->columnSpan(['lg' => 9])
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state))),
                                TextInput::make('slug')
                                    ->label('URL (slug)')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Textarea::make('excerpt')
                                    ->label('Extrait')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                RichEditor::make('description')
                                    ->label('Description')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'bulletList',
                                        'orderedList',
                                        'link',
                                        'undo',
                                        'redo',
                                    ])
                                    ->columnSpanFull(),
                                TextInput::make('location')
                                    ->label('Lieu')
                                    ->maxLength(255),
                                TextInput::make('event_url')
                                    ->label('Lien externe')
                                    ->url()
                                    ->maxLength(255),
                            ])
                            ->columns(2),
                        Group::make([
                            Section::make('Publication')
                                ->schema([
                                    DateTimePicker::make('starts_at')
                                        ->label('Début')
                                        ->native(false)
                                        ->weekStartsOnSunday(),
                                    DateTimePicker::make('ends_at')
                                        ->label('Fin')
                                        ->native(false)
                                        ->weekStartsOnSunday(),
                                    Toggle::make('is_published')
                                        ->label('Publié')
                                        ->default(true),
                                ])
                                ->columns(1),
                            Section::make('Image')
                                ->schema([
                                    FileUpload::make('featured_image')
                                        ->label('Image')
                                        ->image()
                                        ->directory('events')
                                        ->disk('public'),
                                ])
                                ->columns(1),
                        ])
                            ->columnSpan(['lg' => 3]),
                    ]),
            ]);
    }
}
