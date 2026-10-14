<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nom')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name', 'first_name']),
                TextColumn::make('email')
                    ->label('Courriel')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('cinema.name')
                    ->label('Cinéma')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('invited_at')
                    ->label('Invité le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('last_login_at')
                    ->label('Dernière connexion')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Jamais')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('last_name')
            ->filters([
                SelectFilter::make('cinema')
                    ->label('Cinéma')
                    ->relationship('cinema', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(EmployeeStatus::class)
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'])
                        ? $query->withStatus(EmployeeStatus::from($data['value']))
                        : $query),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    Action::make('resendInvitation')
                        ->label('Renvoyer l’invitation')
                        ->icon(Heroicon::OutlinedEnvelope)
                        ->visible(fn (Employee $record): bool => $record->status === EmployeeStatus::Invited)
                        ->requiresConfirmation()
                        ->action(function (Employee $record): void {
                            $record->sendInvitation();

                            Notification::make()
                                ->title("Invitation envoyée à {$record->email}")
                                ->success()
                                ->send();
                        }),
                    Action::make('deactivate')
                        ->label('Désactiver')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->color('warning')
                        ->visible(fn (Employee $record): bool => ! $record->isDeactivated())
                        ->requiresConfirmation()
                        ->modalDescription('L’employé ne pourra plus se connecter. Son historique de formation est conservé.')
                        ->action(fn (Employee $record) => $record->deactivate()),
                    Action::make('reactivate')
                        ->label('Réactiver')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('success')
                        ->visible(fn (Employee $record): bool => $record->isDeactivated())
                        ->requiresConfirmation()
                        ->action(fn (Employee $record) => $record->reactivate()),
                    DeleteAction::make()
                        ->modalDescription('L’employé et tout son historique de formation seront supprimés définitivement.'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('deactivate')
                        ->label('Désactiver')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->deactivate()),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
