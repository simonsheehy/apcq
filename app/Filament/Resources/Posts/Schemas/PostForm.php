<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
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
                        Section::make('Contenu')
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
                                FileUpload::make('featured_image')
                                    ->label('Image à la une')
                                    ->image()
                                    ->directory('posts')
                                    ->disk('public'),
                            ])
                            ->columns(2),
                        Section::make('Publication')
                            ->columnSpan(['lg' => 3])
                            ->schema([
                                Select::make('user_id')
                                    ->label('Auteur')
                                    ->relationship('author', 'name')
                                    ->required()
                                    ->default(auth()->id()),
                                DateTimePicker::make('published_at')
                                    ->label('Date de publication')
                                    ->native(false),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }
}
