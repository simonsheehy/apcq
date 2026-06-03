<?php

namespace App\Filament\Resources\Cinemas\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CinemaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->schema([
                        TextEntry::make('name')->label('Nom'),
                        TextEntry::make('legal_company_name')->label('Compagnie légale')->placeholder('—'),
                        TextEntry::make('group.name')->label('Groupe')->placeholder('—'),
                        TextEntry::make('administrativeRegion.name')->label('Région administrative')->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Coordonnées')
                    ->schema([
                        TextEntry::make('address')->label('Adresse')->placeholder('—'),
                        TextEntry::make('city')->label('Ville')->placeholder('—'),
                        TextEntry::make('postal_code')->label('Code postal')->placeholder('—'),
                        TextEntry::make('phone')->label('Téléphone')->placeholder('—'),
                        TextEntry::make('email')->label('Courriel (info)')->placeholder('—'),
                        TextEntry::make('website')->label('Site web')->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Contacts')
                    ->schema([
                        TextEntry::make('primary_contact_name')->label('Primaire — nom')->placeholder('—'),
                        TextEntry::make('primary_contact_phone')->label('Primaire — téléphone')->placeholder('—'),
                        TextEntry::make('primary_contact_email')->label('Primaire — courriel')->placeholder('—'),
                        TextEntry::make('secondary_contact_name')->label('Secondaire — nom')->placeholder('—'),
                        TextEntry::make('secondary_contact_phone')->label('Secondaire — téléphone')->placeholder('—'),
                        TextEntry::make('secondary_contact_email')->label('Secondaire — courriel')->placeholder('—'),
                    ])
                    ->columns(3),

                Section::make('Exploitation')
                    ->schema([
                        TextEntry::make('pos_software')->label('Logiciel de caisse')->placeholder('—'),
                        TextEntry::make('edelivery')->label('eDelivery')->placeholder('—'),
                        TextEntry::make('cash_registers_count')->label('Nombre de caisses')->placeholder('—'),
                        TextEntry::make('ticket_booths_count')->label('Nombre de guichets')->placeholder('—'),
                        IconEntry::make('alcohol_permit')->label('Permis d\'alcool')->boolean(),
                    ])
                    ->columns(2),
            ]);
    }
}
