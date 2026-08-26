<?php

namespace App\Filament\Actions;

use App\Exports\CinemaExcelExport;
use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadCinemasExcelBulkAction extends BulkAction
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
            ->successNotification(null)
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, CinemaExcelExport $export): BinaryFileResponse {
                return $export->download($records);
            });
    }
}
