<?php

namespace App\Filament\Actions;

use App\Exports\CinemaExcelExport;
use App\Models\Cinema;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadCinemasExcelAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'downloadCinemasExcel';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Télécharger Excel')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->tooltip('Exporte les cinémas visibles (filtres compris) et leurs salles.')
            ->successNotification(null)
            ->action(function (Component $livewire, CinemaExcelExport $export): BinaryFileResponse {
                $query = $livewire instanceof HasTable
                    ? ($livewire->getFilteredTableQuery()?->clone() ?? Cinema::query())
                    : Cinema::query();

                return $export->download($query);
            });
    }
}
