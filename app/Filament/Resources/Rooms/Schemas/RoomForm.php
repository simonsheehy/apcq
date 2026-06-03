<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::components());
    }

    /**
     * Composants du formulaire. Le champ « Cinéma » est masqué lorsque le
     * formulaire est utilisé depuis le gestionnaire de relation d'un cinéma.
     *
     * @return array<int, mixed>
     */
    public static function components(bool $includeCinema = true): array
    {
        return [
            Section::make('Salle')
                ->schema(array_values(array_filter([
                    $includeCinema
                        ? Select::make('cinema_id')
                            ->label('Cinéma')
                            ->relationship('cinema', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                        : null,
                    Select::make('room_type_id')
                        ->label('Type de salle')
                        ->relationship('roomType', 'name')
                        ->searchable()
                        ->preload(),
                    TextInput::make('name')
                        ->label('Nom')
                        ->required()
                        ->maxLength(255),
                ])))
                ->columns(2),

            Section::make('Technologies')
                ->schema([
                    Select::make('imageTechnologies')
                        ->label('Image (2D, 3D, 2K, 4K, IMAX, ScreenX…)')
                        ->relationship('imageTechnologies', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload(),
                    Select::make('soundTechnologies')
                        ->label('Son (5.1, 7.1, Dolby Atmos…)')
                        ->relationship('soundTechnologies', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload(),
                ])
                ->columns(2),

            Section::make('Configuration des sièges')
                ->description('Nombre de places par type de siège (ex. : 20 DBOX, 120 réguliers, 20 inclinables).')
                ->schema([
                    Repeater::make('seatAllocations')
                        ->label('Places par type de siège')
                        ->relationship()
                        ->schema([
                            Select::make('seat_type_id')
                                ->label('Type de siège')
                                ->relationship('seatType', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            TextInput::make('quantity')
                                ->label('Nombre de places')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('Ajouter un type de siège')
                        ->defaultItems(0),
                ]),

            Section::make('Équipement')
                ->schema([
                    TextInput::make('sound_processor')
                        ->label('Processeur de son (marque / modèle)')
                        ->maxLength(255),
                    TextInput::make('projector')
                        ->label('Projecteur (marque / modèle)')
                        ->maxLength(255),
                    TextInput::make('screen_size')
                        ->label('Taille de l\'écran')
                        ->maxLength(255),
                ])
                ->columns(2),
        ];
    }
}
