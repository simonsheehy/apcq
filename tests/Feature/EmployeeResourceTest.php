<?php

use App\Enums\EmployeeStatus;
use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
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

describe('login link for administrators', function () {
    it('copies a new single-use login link for an active employee', function () {
        $this->freezeSecond();
        $employee = Employee::factory()->create();

        Livewire::test(ListEmployees::class)
            ->callTableAction('copyLoginLink', $employee)
            ->assertNotified('Lien de connexion copié dans le presse-papiers');

        expect($employee->refresh())
            ->login_token_hash->not->toBeNull()
            ->and($employee->login_token_expires_at->equalTo(now()->addHours(24)))->toBeTrue();
    });

    it('gives a link that logs the employee in once', function () {
        $employee = Employee::factory()->create();
        $url = $employee->loginUrlForAdministrator();

        auth()->logout();

        $this->post($url)->assertRedirect(route('portal.home'));
        $this->assertAuthenticatedAs($employee, 'employee');

        $this->post(route('portal.logout'));
        $this->post($url)->assertRedirect(route('portal.login'));
    });

    it('replaces a link the employee requested by email', function () {
        $employee = Employee::factory()->create();
        $emailedUrl = $employee->createLoginUrl(now()->addMinutes(15));

        $employee->loginUrlForAdministrator();

        $this->post($emailedUrl)->assertRedirect(route('portal.login'));
        $this->assertGuest('employee');
    });

    it('gives the invitation link while the invitation is pending', function () {
        $employee = Employee::factory()->invited()->create();

        expect($employee->loginUrlForAdministrator())->toBe($employee->invitationUrl())
            ->and($employee->refresh()->login_token_hash)->toBeNull();
    });

    it('is not offered for deactivated employees or deleted cinemas', function () {
        $deactivated = Employee::factory()->deactivated()->create();
        $withoutCinema = Employee::factory()->create();
        $withoutCinema->cinema->delete();

        Livewire::test(ListEmployees::class)
            ->assertTableActionHidden('copyLoginLink', $deactivated)
            ->assertTableActionHidden('copyLoginLink', $withoutCinema);
    });

    it('is available from the edit page', function () {
        $employee = Employee::factory()->create();

        Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
            ->callAction('copyLoginLink')
            ->assertNotified();

        expect($employee->refresh()->login_token_hash)->not->toBeNull();
    });
});
