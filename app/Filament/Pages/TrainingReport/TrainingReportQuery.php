<?php

namespace App\Filament\Pages\TrainingReport;

use App\Enums\TrainingProgressStatus;
use App\Enums\TrainingStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Builds the training follow-up report: one row per employee and training, including
 * trainings the employee has not started yet.
 *
 * @phpstan-type ReportFilters array{cinema_id?: int|string|null, training_id?: int|string|null, status?: string|null, include_deactivated?: bool, search?: string|null}
 * @phpstan-type ReportRow array{key: string, employee_id: int, employee_name: string, employee_email: string, cinema_id: int, cinema_name: string, training_id: int, training_title: string, status: TrainingProgressStatus, progress_percent: int, completed_at: ?Carbon, last_activity_at: ?Carbon, downloaded_documents_count: int, documents_count: int}
 */
class TrainingReportQuery
{
    public const SORTABLE_COLUMNS = [
        'employee_name' => ['employees.last_name', 'employees.first_name'],
        'cinema_name' => ['cinemas.name'],
        'training_title' => ['trainings.sort_order'],
        'progress_percent' => ['progress_ratio'],
        'completed_at' => ['training_progress.completed_at'],
        'last_activity_at' => ['training_progress.updated_at'],
    ];

    /**
     * @param  ReportFilters  $filters
     */
    public function rows(array $filters = [], ?string $sortColumn = null, ?string $sortDirection = null): Builder
    {
        $query = $this->baseQuery($filters)
            ->select([
                'employees.id as employee_id',
                'employees.first_name',
                'employees.last_name',
                'employees.email as employee_email',
                'cinemas.id as cinema_id',
                'cinemas.name as cinema_name',
                'trainings.id as training_id',
                'trainings.title as training_title',
                'training_progress.id as progress_id',
                'training_progress.watched_seconds',
                'training_progress.video_duration',
                'training_progress.completed_at',
                'training_progress.updated_at as last_activity_at',
            ])
            ->selectRaw('CASE WHEN training_progress.video_duration > 0 THEN training_progress.watched_seconds * 1.0 / training_progress.video_duration ELSE 0 END as progress_ratio')
            ->selectSub($this->downloadedDocumentsCount(), 'downloaded_documents_count')
            ->selectSub($this->documentsCount(), 'documents_count');

        if ($status = TrainingProgressStatus::tryFrom((string) ($filters['status'] ?? ''))) {
            $this->whereStatus($query, $status);
        }

        $direction = $sortDirection === 'desc' ? 'desc' : 'asc';

        foreach (self::SORTABLE_COLUMNS[$sortColumn] ?? [] as $column) {
            $query->orderBy($column, $direction);
        }

        return $query
            ->orderBy('cinemas.name')
            ->orderBy('employees.last_name')
            ->orderBy('employees.first_name')
            ->orderBy('trainings.sort_order')
            ->orderBy('trainings.id');
    }

    /**
     * Completion rate per cinema: completed trainings over (employees × trainings). Only employees
     * who accepted their invitation count, since the others cannot access the trainings yet.
     *
     * @param  ReportFilters  $filters
     * @return Collection<int, array{cinema_id: int, cinema_name: string, employees_count: int, assignments_count: int, completed_count: int, in_progress_count: int, completion_rate: int}>
     */
    public function summaryByCinema(array $filters = []): Collection
    {
        return $this->baseQuery($filters)
            ->whereNotNull('employees.invitation_accepted_at')
            ->select(['cinemas.id as cinema_id', 'cinemas.name as cinema_name'])
            ->selectRaw('COUNT(DISTINCT employees.id) as employees_count')
            ->selectRaw('COUNT(*) as assignments_count')
            ->selectRaw('SUM(CASE WHEN training_progress.completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed_count')
            ->selectRaw('SUM(CASE WHEN training_progress.id IS NOT NULL AND training_progress.completed_at IS NULL THEN 1 ELSE 0 END) as in_progress_count')
            ->groupBy('cinemas.id', 'cinemas.name')
            ->orderBy('cinemas.name')
            ->get()
            ->map(fn (object $row): array => [
                'cinema_id' => (int) $row->cinema_id,
                'cinema_name' => $row->cinema_name,
                'employees_count' => (int) $row->employees_count,
                'assignments_count' => (int) $row->assignments_count,
                'completed_count' => (int) $row->completed_count,
                'in_progress_count' => (int) $row->in_progress_count,
                'completion_rate' => (int) floor($row->completed_count / max(1, $row->assignments_count) * 100),
            ]);
    }

    /**
     * @return ReportRow
     */
    public static function present(object $row): array
    {
        $status = match (true) {
            $row->completed_at !== null => TrainingProgressStatus::Completed,
            $row->progress_id !== null => TrainingProgressStatus::InProgress,
            default => TrainingProgressStatus::NotStarted,
        };

        return [
            'key' => "{$row->employee_id}-{$row->training_id}",
            'employee_id' => (int) $row->employee_id,
            'employee_name' => trim("{$row->first_name} {$row->last_name}"),
            'employee_email' => $row->employee_email,
            'cinema_id' => (int) $row->cinema_id,
            'cinema_name' => $row->cinema_name,
            'training_id' => (int) $row->training_id,
            'training_title' => $row->training_title,
            'status' => $status,
            'progress_percent' => (int) min(100, floor($row->progress_ratio * 100)),
            'completed_at' => $row->completed_at ? Carbon::parse($row->completed_at) : null,
            'last_activity_at' => $row->last_activity_at ? Carbon::parse($row->last_activity_at) : null,
            'downloaded_documents_count' => (int) $row->downloaded_documents_count,
            'documents_count' => (int) $row->documents_count,
        ];
    }

    /**
     * @param  ReportFilters  $filters
     */
    protected function baseQuery(array $filters): Builder
    {
        $query = DB::table('employees')
            ->join('cinemas', function (JoinClause $join): void {
                $join->on('cinemas.id', '=', 'employees.cinema_id')
                    ->whereNull('cinemas.deleted_at');
            })
            ->crossJoin('trainings')
            ->leftJoin('training_progress', function (JoinClause $join): void {
                $join->on('training_progress.employee_id', '=', 'employees.id')
                    ->on('training_progress.training_id', '=', 'trainings.id');
            });

        if (filled($trainingId = $filters['training_id'] ?? null)) {
            $query->where('trainings.id', $trainingId);
        } else {
            $query->where('trainings.status', TrainingStatus::Published->value);
        }

        if (filled($cinemaId = $filters['cinema_id'] ?? null)) {
            $query->where('cinemas.id', $cinemaId);
        }

        if (! ($filters['include_deactivated'] ?? false)) {
            $query->whereNull('employees.deactivated_at');
        }

        if (filled($search = $filters['search'] ?? null)) {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('employees.first_name', 'like', "%{$search}%")
                    ->orWhere('employees.last_name', 'like', "%{$search}%")
                    ->orWhere('employees.email', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    protected function whereStatus(Builder $query, TrainingProgressStatus $status): void
    {
        match ($status) {
            TrainingProgressStatus::NotStarted => $query->whereNull('training_progress.id'),
            TrainingProgressStatus::InProgress => $query->whereNotNull('training_progress.id')->whereNull('training_progress.completed_at'),
            TrainingProgressStatus::Completed => $query->whereNotNull('training_progress.completed_at'),
        };
    }

    protected function downloadedDocumentsCount(): Builder
    {
        return DB::table('training_document_downloads')
            ->selectRaw('COUNT(*)')
            ->whereColumn('training_document_downloads.employee_id', 'employees.id')
            ->whereColumn('training_document_downloads.training_id', 'trainings.id');
    }

    protected function documentsCount(): Builder
    {
        return DB::table('training_documents')
            ->selectRaw('COUNT(*)')
            ->whereColumn('training_documents.training_id', 'trainings.id');
    }
}
