<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoomInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Salle')
                    ->schema([
                        TextEntry::make('cinema.name')->label('Cinéma'),
                        TextEntry::make('name')->label('Nom'),
                        TextEntry::make('roomType.name')->label('Type de salle')->placeholder('—'),
                        TextEntry::make('total_seats')->label('Total de places'),
                    ])
                    ->columns(2),

                Section::make('Technologies')
                    ->schema([
                        TextEntry::make('imageTechnologies.name')->label('Image')->badge()->placeholder('—'),
                        TextEntry::make('soundTechnologies.name')->label('Son')->badge()->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Configuration des sièges')
                    ->schema([
                        RepeatableEntry::make('seatAllocations')
                            ->label('Places par type de siège')
                            ->schema([
                                TextEntry::make('seatType.name')->label('Type'),
                                TextEntry::make('quantity')->label('Places'),
                            ])
                            ->columns(2),
                    ]),

                Section::make('Équipement')
                    ->schema([
                        TextEntry::make('sound_processor')->label('Processeur de son')->placeholder('—'),
                        TextEntry::make('projector_brand')->label('Marque du projecteur')->placeholder('—'),
                        TextEntry::make('projector_brand_other')->label('Marque (autre)')->placeholder('—'),
                        TextEntry::make('projector_model')->label('Modèle du projecteur')->placeholder('—'),
                        TextEntry::make('server_model')->label('Modèle du serveur')->placeholder('—'),
                        TextEntry::make('projection_type')->label('Type de projection')->placeholder('—'),
                        TextEntry::make('installation_year')->label('Année d\'installation')->placeholder('—'),
                        TextEntry::make('screen_size')->label('Grandeur de l\'écran')->placeholder('—'),
                    ])
                    ->columns(2),
            ]);
    }
}
