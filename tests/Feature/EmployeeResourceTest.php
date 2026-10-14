<?php

use App\Enums\EmployeeStatus;
use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Models\Cinema;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\EmployeeInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('creates an employee and sends the invitation', function () {
    Notification::fake();
    $cinema = Cinema::factory()->create();

    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'cinema_id' => $cinema->id,
            'first_name' => 'Julie',
            'last_name' => 'Tremblay',
            'email' => 'julie@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $employee = Employee::sole();

    expect($employee)
        ->cinema_id->toBe($cinema->id)
        ->status->toBe(EmployeeStatus::Invited)
        ->invited_at->not->toBeNull();

    Notification::assertSentTo($employee, EmployeeInvitation::class);
});

it('requires a cinema and a unique email', function () {
    Employee::factory()->create(['email' => 'julie@example.com']);

    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'first_name' => 'Julie',
            'last_name' => 'Tremblay',
            'email' => 'julie@example.com',
        ])
        ->call('create')
        ->assertHasFormErrors(['cinema_id' => 'required', 'email' => 'unique']);
});

it('resends an invitation to a pending employee', function () {
    Notification::fake();
    $employee = Employee::factory()->invited()->create();

    Livewire::test(ListEmployees::class)
        ->callTableAction('resendInvitation', $employee);

    Notification::assertSentTo($employee, EmployeeInvitation::class);
});

it('only offers to resend invitations that are still pending', function () {
    $employee = Employee::factory()->create();

    Livewire::test(ListEmployees::class)
        ->assertTableActionHidden('resendInvitation', $employee);
});

it('deactivates and reactivates an employee', function () {
    $employee = Employee::factory()->create();

    Livewire::test(ListEmployees::class)
        ->callTableAction('deactivate', $employee);

    expect($employee->refresh()->status)->toBe(EmployeeStatus::Deactivated);

    Livewire::test(ListEmployees::class)
        ->callTableAction('reactivate', $employee);

    expect($employee->refresh()->status)->toBe(EmployeeStatus::Active);
});

it('filters employees by cinema and status', function () {
    $cinema = Cinema::factory()->create();
    $active = Employee::factory()->for($cinema)->create();
    $invited = Employee::factory()->for($cinema)->invited()->create();
    $deactivated = Employee::factory()->for($cinema)->deactivated()->create();
    $otherCinema = Employee::factory()->create();

    Livewire::test(ListEmployees::class)
        ->filterTable('cinema', $cinema->id)
        ->assertCanSeeTableRecords([$active, $invited, $deactivated])
        ->assertCanNotSeeTableRecords([$otherCinema])
        ->filterTable('status', EmployeeStatus::Invited->value)
        ->assertCanSeeTableRecords([$invited])
        ->assertCanNotSeeTableRecords([$active, $deactivated]);
});
