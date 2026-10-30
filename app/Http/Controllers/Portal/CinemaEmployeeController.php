<?php

namespace App\Http\Controllers\Portal;

use App\Enums\EmployeeStatus;
use App\Enums\TrainingProgressStatus;
use App\Filament\Pages\TrainingReport\TrainingReportQuery;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Lets a cinema administrator manage the employees of their own cinema from the portal.
 */
class CinemaEmployeeController extends Controller
{
    public function __construct(protected TrainingReportQuery $report) {}

    public function index(Request $request): View
    {
        $administrator = $request->user('employee');

        $progressByEmployee = $this->reportRows(['cinema_id' => $administrator->cinema_id])
            ->groupBy('employee_id')
            ->map(fn ($rows): array => [
                'completed' => $rows->where('status', TrainingProgressStatus::Completed)->count(),
                'in_progress' => $rows->where('status', TrainingProgressStatus::InProgress)->count(),
                'total' => $rows->count(),
            ]);

        return view('portal.cinema.employees.index', [
            'administrator' => $administrator,
            'cinema' => $administrator->cinema,
            'employees' => $administrator->cinema->employees()->orderBy('last_name')->orderBy('first_name')->get(),
            'progressByEmployee' => $progressByEmployee,
        ]);
    }

    public function show(Request $request, Employee $employee): View
    {
        $administrator = $request->user('employee');

        abort_unless($employee->cinema_id === $administrator->cinema_id, 404);

        return view('portal.cinema.employees.show', [
            'administrator' => $administrator,
            'employee' => $employee,
            'rows' => $this->reportRows(['cinema_id' => $administrator->cinema_id, 'employee_id' => $employee->id]),
        ]);
    }

    public function create(): View
    {
        return view('portal.cinema.employees.form', ['employee' => new Employee]);
    }

    public function store(Request $request): RedirectResponse
    {
        $administrator = $request->user('employee');

        $employee = Employee::create([
            ...$this->validateEmployee($request),
            'cinema_id' => $administrator->cinema_id,
        ]);

        $employee->sendInvitation();

        return redirect()->route('portal.cinema.employees.index')
            ->with('status', "{$employee->full_name} a été ajouté·e et a reçu son invitation par courriel.");
    }

    public function edit(Request $request, Employee $employee): View
    {
        $this->authorizeManagement($request, $employee);

        return view('portal.cinema.employees.form', ['employee' => $employee]);
    }

    /**
     * Changing the email invalidates the links already sent to the previous address. A pending
     * invitation is sent again to the new address.
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeManagement($request, $employee);

        $employee->fill($this->validateEmployee($request, $employee));
        $emailChanged = $employee->isDirty('email');
        $employee->save();

        if ($emailChanged) {
            $employee->consumeLoginToken();

            if ($employee->status === EmployeeStatus::Invited) {
                $employee->sendInvitation();
            }
        }

        return redirect()->route('portal.cinema.employees.index')
            ->with('status', "Les informations de {$employee->full_name} ont été mises à jour.");
    }

    public function deactivate(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeManagement($request, $employee);

        $employee->deactivate();
        $employee->consumeLoginToken();

        return back()->with('status', "L’accès de {$employee->full_name} a été désactivé. Son historique de formation est conservé.");
    }

    public function reactivate(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeManagement($request, $employee);

        $employee->reactivate();

        return back()->with('status', "L’accès de {$employee->full_name} a été réactivé.");
    }

    /**
     * Deletes the employee for good, with their training history.
     */
    public function destroy(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeManagement($request, $employee);

        $fullName = $employee->full_name;
        $employee->delete();

        return redirect()->route('portal.cinema.employees.index')
            ->with('status', "{$fullName} a été supprimé·e, avec son historique de formation.");
    }

    public function resendInvitation(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeManagement($request, $employee);

        abort_unless($employee->status === EmployeeStatus::Invited, 404);

        $employee->sendInvitation();

        return back()->with('status', "L’invitation a été renvoyée à {$employee->email}.");
    }

    /**
     * Employees of another cinema are reported as not found, so their existence is not revealed.
     */
    private function authorizeManagement(Request $request, Employee $employee): void
    {
        abort_unless($request->user('employee')->canManage($employee), 404);
    }

    /**
     * @return array{first_name: string, last_name: string, email: string}
     */
    private function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employee)],
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function reportRows(array $filters): Collection
    {
        return $this->report->rows([...$filters, 'include_deactivated' => true])
            ->get()
            ->map(fn (object $row): array => TrainingReportQuery::present($row));
    }
}
