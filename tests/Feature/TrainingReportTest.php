<?php

use App\Enums\TrainingProgressStatus;
use App\Exports\TrainingReportExport;
use App\Filament\Pages\TrainingReport\CinemaCompletionSummary;
use App\Filament\Pages\TrainingReport\TrainingReport;
use App\Filament\Pages\TrainingReport\TrainingReportQuery;
use App\Models\Cinema;
use App\Models\Employee;
use App\Models\Training;
use App\Models\TrainingDocument;
use App\Models\TrainingDocumentDownload;
use App\Models\TrainingProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());

    $this->cinema = Cinema::factory()->create(['name' => 'Cinéma Beloeil']);
    $this->otherCinema = Cinema::factory()->create(['name' => 'Cinéma Saguenay']);

    $this->audio = Training::factory()->create(['title' => 'Qualité audio', 'sort_order' => 1]);
    $this->cleaning = Training::factory()->create(['title' => 'Propreté', 'sort_order' => 2]);
    Training::factory()->draft()->create(['title' => 'Brouillon']);

    $this->julie = Employee::factory()->for($this->cinema)->create(['first_name' => 'Julie', 'last_name' => 'Tremblay']);
    $this->marc = Employee::factory()->for($this->otherCinema)->create(['first_name' => 'Marc', 'last_name' => 'Gagnon']);

    TrainingProgress::factory()->for($this->julie)->for($this->audio)->completed()->create(['completed_at' => '2026-10-01 14:00:00']);
    TrainingProgress::factory()->for($this->julie)->for($this->cleaning)->create(['watched_seconds' => 300, 'video_duration' => 600]);
});

function reportKey(Employee $employee, Training $training): string
{
    return "{$employee->id}-{$training->id}";
}

it('lists every employee for every published training, including not started ones', function () {
    $component = Livewire::test(TrainingReport::class)->assertOk();

    $records = $component->instance()->getTableRecords();

    expect($records->keys()->all())->toEqualCanonicalizing([
        reportKey($this->julie, $this->audio),
        reportKey($this->julie, $this->cleaning),
        reportKey($this->marc, $this->audio),
        reportKey($this->marc, $this->cleaning),
    ]);

    expect($records[reportKey($this->julie, $this->audio)])
        ->status->toBe(TrainingProgressStatus::Completed)
        ->and($records[reportKey($this->julie, $this->audio)]['completed_at']->format('Y-m-d'))->toBe('2026-10-01')
        ->and($records[reportKey($this->julie, $this->cleaning)])
        ->status->toBe(TrainingProgressStatus::InProgress)
        ->progress_percent->toBe(50)
        ->and($records[reportKey($this->marc, $this->audio)])
        ->status->toBe(TrainingProgressStatus::NotStarted)
        ->progress_percent->toBe(0);
});

it('filters by cinema, training and status', function () {
    Livewire::test(TrainingReport::class)
        ->filterTable('cinema', $this->otherCinema->id)
        ->assertCanSeeTableRecords([reportKey($this->marc, $this->audio), reportKey($this->marc, $this->cleaning)])
        ->assertCanNotSeeTableRecords([reportKey($this->julie, $this->audio)]);

    Livewire::test(TrainingReport::class)
        ->filterTable('training', $this->cleaning->id)
        ->assertCanSeeTableRecords([reportKey($this->julie, $this->cleaning), reportKey($this->marc, $this->cleaning)])
        ->assertCanNotSeeTableRecords([reportKey($this->julie, $this->audio)]);

    Livewire::test(TrainingReport::class)
        ->filterTable('status', TrainingProgressStatus::NotStarted->value)
        ->assertCanSeeTableRecords([reportKey($this->marc, $this->audio), reportKey($this->marc, $this->cleaning)])
        ->assertCanNotSeeTableRecords([reportKey($this->julie, $this->audio), reportKey($this->julie, $this->cleaning)]);
});

it('excludes deactivated employees unless asked', function () {
    $this->marc->deactivate();

    Livewire::test(TrainingReport::class)
        ->assertCanNotSeeTableRecords([reportKey($this->marc, $this->audio)])
        ->filterTable('include_deactivated', true)
        ->assertCanSeeTableRecords([reportKey($this->marc, $this->audio)]);
});

it('searches employees by name or email', function () {
    Livewire::test(TrainingReport::class)
        ->searchTable('gagnon')
        ->assertCanSeeTableRecords([reportKey($this->marc, $this->audio)])
        ->assertCanNotSeeTableRecords([reportKey($this->julie, $this->audio)]);
});

it('shows which documents each employee downloaded', function () {
    $document = TrainingDocument::factory()->for($this->audio)->create();
    TrainingDocument::factory()->for($this->audio)->create();
    TrainingDocumentDownload::record($this->julie, $document);

    $records = Livewire::test(TrainingReport::class)->instance()->getTableRecords();

    expect($records[reportKey($this->julie, $this->audio)])
        ->downloaded_documents_count->toBe(1)
        ->documents_count->toBe(2)
        ->and($records[reportKey($this->marc, $this->audio)]['downloaded_documents_count'])->toBe(0);
});

it('summarizes the completion rate per cinema', function () {
    $summary = Livewire::test(CinemaCompletionSummary::class)->instance()->getTableRecords();

    expect($summary[(string) $this->cinema->id])
        ->employees_count->toBe(1)
        ->assignments_count->toBe(2)
        ->completed_count->toBe(1)
        ->in_progress_count->toBe(1)
        ->completion_rate->toBe(50)
        ->and($summary[(string) $this->otherCinema->id]['completion_rate'])->toBe(0);
});

it('exports the filtered report and the summary to excel', function () {
    $path = app(TrainingReportExport::class)->writeToTemp(['cinema_id' => $this->cinema->id]);

    $reader = new Reader;
    $reader->open($path);

    $sheets = [];
    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $row) {
            $sheets[$sheet->getName()][] = $row->toArray();
        }
    }
    $reader->close();
    unlink($path);

    expect($sheets['Suivi'])->toHaveCount(3)
        ->and($sheets['Suivi'][1])->toMatchArray([0 => 'Julie Tremblay', 3 => 'Qualité audio', 4 => 'Complétée', 5 => 100, 6 => '2026-10-01'])
        ->and($sheets['Suivi'][2])->toMatchArray([3 => 'Propreté', 4 => 'En cours', 5 => 50])
        ->and($sheets['Sommaire par cinéma'])->toHaveCount(2)
        ->and($sheets['Sommaire par cinéma'][1])->toBe(['Cinéma Beloeil', 1, 2, 1, 1, 50]);
});

it('downloads the excel export from the page', function () {
    Livewire::test(TrainingReport::class)
        ->callAction('downloadExcel')
        ->assertFileDownloaded();
});

it('leaves out employees of deleted cinemas', function () {
    $this->otherCinema->delete();

    Livewire::test(TrainingReport::class)
        ->assertCanNotSeeTableRecords([reportKey($this->marc, $this->audio)]);

    expect(Livewire::test(CinemaCompletionSummary::class)->instance()->getTableRecords()->pluck('cinema_name')->all())
        ->toBe(['Cinéma Beloeil']);
});

it('only counts employees who activated their access in the completion rate', function () {
    Employee::factory()->for($this->cinema)->invited()->create();

    $summary = Livewire::test(CinemaCompletionSummary::class)->instance()->getTableRecords();

    expect($summary[(string) $this->cinema->id])
        ->employees_count->toBe(1)
        ->completion_rate->toBe(50);
});

it('keeps cinemas with the same name apart in the summary', function () {
    $homonym = Cinema::factory()->create(['name' => 'Cinéma Beloeil']);
    Employee::factory()->for($homonym)->create();

    expect(Livewire::test(CinemaCompletionSummary::class)->instance()->getTableRecords())->toHaveCount(3);
});

it('ignores an unknown status filter', function () {
    expect(app(TrainingReportQuery::class)->rows(['status' => 'pirate'])->count())
        ->toBe(4);
});

it('writes names starting with an equals sign as plain text', function () {
    $this->julie->update(['first_name' => '=HYPERLINK("http://example.com")', 'last_name' => '']);

    $path = app(TrainingReportExport::class)->writeToTemp(['cinema_id' => $this->cinema->id]);

    $zip = new ZipArchive;
    $zip->open($path);
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();
    unlink($path);

    expect($sheetXml)
        ->toContain('t="inlineStr"><is><t>=HYPERLINK(&quot;http://example.com&quot;)</t>')
        ->not->toContain('<f>');
});
