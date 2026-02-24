<?php

namespace App\Filament\Resources\Partners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerForm
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
                                    ->maxLength(255),
                                FileUpload::make('logo')
                                    ->label('Logo')
                                    ->image()
                                    ->directory('partners')
                                    ->disk('public'),
                                TextInput::make('url')
                                    ->label('Site web')
                                    ->url()
                                    ->maxLength(255),
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
