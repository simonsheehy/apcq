<?php

namespace App\Filament\Resources\SoundTechnologies;

use App\Filament\Resources\SoundTechnologies\Pages\ManageSoundTechnologies;
use App\Models\SoundTechnology;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SoundTechnologyResource extends Resource
{
    protected static ?string $model = SoundTechnology::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSpeakerWave;

    protected static string|UnitEnum|null $navigationGroup = 'Référentiel';

    protected static ?string $navigationLabel = 'Technologies de son';

    protected static ?string $modelLabel = 'technologie de son';

    protected static ?string $pluralModelLabel = 'technologies de son';

    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rooms_count')
                    ->label('Salles')
                    ->counts('rooms')
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSoundTechnologies::route('/'),
        ];
    }
}
