<?php

namespace App\Filament\Resources\Members\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(12)
                    ->columns(12)
                    ->schema([
                        Section::make('Informations')
                            ->columnSpan(['lg' => 9])
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('cinema_name')
                                    ->label('Nom du cinéma')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('address')
                                    ->label('Adresse')
                                    ->maxLength(255),
                                TextInput::make('city')
                                    ->label('Ville')
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('Téléphone')
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Courriel')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('website')
                                    ->label('Site web')
                                    ->url()
                                    ->maxLength(255),
                                SpatieMediaLibraryFileUpload::make('logo')
                                    ->label('Logo')
                                    ->collection('logo')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(2048),
                            ])
                            ->columns(2),
                        Section::make('Affichage')
                            ->columnSpan(['lg' => 3])
                            ->schema([
                                TextInput::make('sort_order')
                                    ->label('Ordre d\'affichage')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }
}
