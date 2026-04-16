<?php

namespace App\Filament\Resources\BoardMembers;

use App\Filament\Resources\BoardMembers\Pages\CreateBoardMember;
use App\Filament\Resources\BoardMembers\Pages\EditBoardMember;
use App\Filament\Resources\BoardMembers\Pages\ListBoardMembers;
use App\Filament\Resources\BoardMembers\Schemas\BoardMemberForm;
use App\Filament\Resources\BoardMembers\Tables\BoardMembersTable;
use App\Models\BoardMember;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BoardMemberResource extends Resource
{
    protected static ?string $model = BoardMember::class;

    protected static ?string $navigationLabel = 'Conseil d\'administration';

    protected static ?string $modelLabel = 'membre du conseil';

    protected static ?string $pluralModelLabel = 'membres du conseil';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return BoardMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoardMembersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBoardMembers::route('/'),
            'create' => CreateBoardMember::route('/create'),
            'edit' => EditBoardMember::route('/{record}/edit'),
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
