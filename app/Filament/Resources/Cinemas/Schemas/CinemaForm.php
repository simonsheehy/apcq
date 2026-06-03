<?php

namespace App\Filament\Resources\Cinemas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CinemaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('legal_company_name')
                            ->label('Nom de la compagnie légale')
                            ->maxLength(255),
                        Select::make('group_id')
                            ->label('Groupe')
                            ->relationship('group', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Select::make('administrative_region_id')
                            ->label('Région administrative')
                            ->relationship('administrativeRegion', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('Coordonnées')
                    ->schema([
                        TextInput::make('address')
                            ->label('Adresse')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->label('Ville')
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->label('Code postal')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Courriel (info)')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('website')
                            ->label('Site web')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Contact primaire')
                    ->schema([
                        TextInput::make('primary_contact_name')
                            ->label('Nom')
                            ->maxLength(255),
                        TextInput::make('primary_contact_phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('primary_contact_email')
                            ->label('Courriel')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Contact secondaire')
                    ->schema([
                        TextInput::make('secondary_contact_name')
                            ->label('Nom')
                            ->maxLength(255),
                        TextInput::make('secondary_contact_phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('secondary_contact_email')
                            ->label('Courriel')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Exploitation')
                    ->schema([
                        TextInput::make('pos_software')
                            ->label('Logiciel de caisse')
                            ->maxLength(255),
                        TextInput::make('edelivery')
                            ->label('eDelivery (CineSend, Global DCP, etc.)')
                            ->maxLength(255),
                        TextInput::make('cash_registers_count')
                            ->label('Nombre de caisses')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('ticket_booths_count')
                            ->label('Nombre de guichets')
                            ->numeric()
                            ->minValue(0),
                        Toggle::make('alcohol_permit')
                            ->label('Permis d\'alcool')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
