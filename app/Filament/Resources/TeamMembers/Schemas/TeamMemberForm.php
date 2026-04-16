<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TeamMemberForm
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
                                TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state))),
                                TextInput::make('slug')
                                    ->label('URL (slug)')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('title')
                                    ->label('Titre')
                                    ->placeholder('Coordination, Communication, ...')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Courriel')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('Téléphone')
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('linkedin_url')
                                    ->label('Lien LinkedIn')
                                    ->url()
                                    ->maxLength(255),
                                RichEditor::make('bio')
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
                            ])
                            ->columns(2),
                        Group::make([
                            Section::make('Photo')
                                ->schema([
                                    FileUpload::make('photo')
                                        ->label('Photo')
                                        ->image()
                                        ->imageEditor()
                                        ->directory('team-members')
                                        ->disk('public'),
                                ]),
                            Section::make('Publication')
                                ->schema([
                                    Toggle::make('is_published')
                                        ->label('Publié')
                                        ->default(true),
                                    TextInput::make('sort_order')
                                        ->label('Ordre d\'affichage')
                                        ->numeric()
                                        ->default(0),
                                ]),
                        ])
                            ->columnSpan(['lg' => 3]),
                    ]),
            ]);
    }
}
