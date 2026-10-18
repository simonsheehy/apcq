<?php

namespace App\Exports;

use App\Enums\TrainingProgressStatus;
use App\Filament\Pages\TrainingReport\TrainingReportQuery;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TrainingReportExport
{
    public function __construct(protected TrainingReportQuery $report) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function download(array $filters = [], ?string $sortColumn = null, ?string $sortDirection = null): BinaryFileResponse
    {
        $path = $this->writeToTemp($filters, $sortColumn, $sortDirection);
        $fileName = 'rapport-formations-'.now()->timezone('America/Toronto')->format('Y-m-d').'.xlsx';

        return response()
            ->download($path, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function writeToTemp(array $filters = [], ?string $sortColumn = null, ?string $sortDirection = null): string
    {
        $path = sys_get_temp_dir().'/rapport-formations-'.Str::uuid().'.xlsx';

        $writer = new Writer;
        $writer->setCreator('APCQ');
        $writer->openToFile($path);

        $headerStyle = (new Style)->setFontBold();

        $writer->getCurrentSheet()->setName('Suivi');
        $writer->addRow(Row::fromValues($this->rowHeaders(), $headerStyle));

        foreach ($this->report->rows($filters, $sortColumn, $sortDirection)->cursor() as $row) {
            $writer->addRow($this->dataRow($this->rowValues(TrainingReportQuery::present($row))));
        }

        $writer->addNewSheetAndMakeItCurrent()->setName('Sommaire par cinéma');
        $writer->addRow(Row::fromValues($this->summaryHeaders(), $headerStyle));

        foreach ($this->report->summaryByCinema($filters) as $summary) {
            $writer->addRow($this->dataRow([
                $summary['cinema_name'],
                $summary['employees_count'],
                $summary['assignments_count'],
                $summary['completed_count'],
                $summary['in_progress_count'],
                $summary['completion_rate'],
            ]));
        }

        $writer->close();

        return $path;
    }

    /**
     * Writes text as plain strings: OpenSpout would otherwise turn a value starting with "=" (an
     * employee name, a training title) into a spreadsheet formula.
     *
     * @param  list<string|int|null>  $values
     */
    protected function dataRow(array $values): Row
    {
        return new Row(array_map(
            fn (string|int|null $value): Cell => is_string($value) ? new StringCell($value, null) : Cell::fromValue($value),
            $values,
        ));
    }

    /**
     * @return list<string>
     */
    protected function rowHeaders(): array
    {
        return [
            'Employé',
            'Courriel',
            'Cinéma',
            'Module',
            'Capsule',
            'Formation',
            'Statut',
            'Progression (%)',
            'Complétée le',
            'Documents téléchargés',
            'Documents disponibles',
            'Dernière activité',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string|int|null>
     */
    protected function rowValues(array $row): array
    {
        /** @var TrainingProgressStatus $status */
        $status = $row['status'];

        return [
            $row['employee_name'],
            $row['employee_email'],
            $row['cinema_name'],
            $row['module_number'],
            $row['capsule_number'],
            $row['training_title'],
            $status->getLabel(),
            $row['progress_percent'],
            $row['completed_at']?->timezone('America/Toronto')->format('Y-m-d'),
            $row['downloaded_documents_count'],
            $row['documents_count'],
            $row['last_activity_at']?->timezone('America/Toronto')->format('Y-m-d H:i'),
        ];
    }

    /**
     * @return list<string>
     */
    protected function summaryHeaders(): array
    {
        return [
            'Cinéma',
            'Employés',
            'Formations attribuées',
            'Formations complétées',
            'Formations en cours',
            'Taux de complétion (%)',
        ];
    }
}
