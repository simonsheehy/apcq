<?php

namespace App\Filament\Resources\Cinemas;

use App\Filament\Resources\Cinemas\Pages\CreateCinema;
use App\Filament\Resources\Cinemas\Pages\EditCinema;
use App\Filament\Resources\Cinemas\Pages\ListCinemas;
use App\Filament\Resources\Cinemas\Pages\ViewCinema;
use App\Filament\Resources\Cinemas\RelationManagers\RoomsRelationManager;
use App\Filament\Resources\Cinemas\Schemas\CinemaForm;
use App\Filament\Resources\Cinemas\Schemas\CinemaInfolist;
use App\Filament\Resources\Cinemas\Tables\CinemasTable;
use App\Models\Cinema;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CinemaResource extends Resource
{
    protected static ?string $model = Cinema::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Inventaire';

    protected static ?string $navigationLabel = 'Cinémas';

    protected static ?string $modelLabel = 'cinéma';

    protected static ?string $pluralModelLabel = 'cinémas';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return CinemaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CinemaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CinemasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RoomsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCinemas::route('/'),
            'create' => CreateCinema::route('/create'),
            'view' => ViewCinema::route('/{record}'),
            'edit' => EditCinema::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
