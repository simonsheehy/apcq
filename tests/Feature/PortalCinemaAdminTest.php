<?php

use App\Enums\EmployeeStatus;
use App\Models\Cinema;
use App\Models\Employee;
use App\Models\Training;
use App\Models\TrainingProgress;
use App\Notifications\EmployeeInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();

    $this->cinema = Cinema::factory()->create(['name' => 'Cinéma Beloeil']);
    $this->administrator = Employee::factory()->for($this->cinema)->create(['is_cinema_admin' => true, 'first_name' => 'Annie']);
    $this->colleague = Employee::factory()->for($this->cinema)->create(['first_name' => 'Julie', 'last_name' => 'Tremblay']);
    $this->stranger = Employee::factory()->create(['first_name' => 'Marc', 'last_name' => 'Gagnon']);
});

describe('access', function () {
    it('is limited to cinema administrators', function (string $routeName) {
        $this->actingAs($this->colleague, 'employee')
            ->get(route($routeName))
            ->assertForbidden();
    })->with(['portal.cinema.employees.index', 'portal.cinema.employees.create', 'portal.cinema.report']);

    it('requires a login', function () {
        $this->get(route('portal.cinema.employees.index'))->assertRedirect(route('portal.login'));
    });

    it('is closed to a deactivated administrator', function () {
        $this->administrator->deactivate();

        $this->actingAs($this->administrator, 'employee')
            ->get(route('portal.cinema.employees.index'))
            ->assertRedirect(route('portal.login'));
    });

    it('shows the cinema link to administrators only', function () {
        $this->actingAs($this->administrator, 'employee')->get(route('portal.home'))->assertSee('Mon cinéma');
        $this->actingAs($this->colleague, 'employee')->get(route('portal.home'))->assertDontSee('Mon cinéma');
    });
});

describe('employee list', function () {
    it('lists the employees of the cinema with their progress', function () {
        $audio = Training::factory()->create();
        Training::factory()->create();
        TrainingProgress::factory()->for($this->colleague)->for($audio)->completed()->create();

        $this->actingAs($this->administrator, 'employee')
            ->get(route('portal.cinema.employees.index'))
            ->assertOk()
            ->assertSee('Cinéma Beloeil')
            ->assertSee('Julie Tremblay')
            ->assertSee('1 / 2 complétée(s)')
            ->assertSee('50 %')
            ->assertDontSee('Marc Gagnon');
    });

    it('shows the training progress of an employee', function () {
        $training = Training::factory()->create(['title' => 'Qualité audio']);
        TrainingProgress::factory()->for($this->colleague)->for($training)->create(['watched_seconds' => 300, 'video_duration' => 600]);

        $this->actingAs($this->administrator, 'employee')
            ->get(route('portal.cinema.employees.show', $this->colleague))
            ->assertOk()
            ->assertSee('Qualité audio')
            ->assertSee('En cours · 50 %');
    });

    it('hides employees of other cinemas', function (string $routeName) {
        $this->actingAs($this->administrator, 'employee')
            ->get(route($routeName, $this->stranger))
            ->assertNotFound();
    })->with(['portal.cinema.employees.show', 'portal.cinema.employees.edit']);
});

describe('managing employees', function () {
    it('adds an employee to the cinema and sends the invitation', function () {
        $this->actingAs($this->administrator, 'employee')
            ->post(route('portal.cinema.employees.store'), [
                'first_name' => 'Léa',
                'last_name' => 'Roy',
                'email' => 'lea@example.com',
                'cinema_id' => $this->stranger->cinema_id,
                'is_cinema_admin' => true,
            ])
            ->assertRedirect(route('portal.cinema.employees.index'));

        $employee = Employee::firstWhere('email', 'lea@example.com');

        expect($employee)
            ->cinema_id->toBe($this->cinema->id)
            ->is_cinema_admin->toBeFalse()
            ->status->toBe(EmployeeStatus::Invited);
        Notification::assertSentTo($employee, EmployeeInvitation::class);
    });

    it('requires a unique email', function () {
        $this->actingAs($this->administrator, 'employee')
            ->post(route('portal.cinema.employees.store'), [
                'first_name' => 'Léa',
                'last_name' => 'Roy',
                'email' => $this->stranger->email,
            ])
            ->assertSessionHasErrors('email');
    });

    it('updates the name and email but never the cinema or the admin role', function () {
        $this->actingAs($this->administrator, 'employee')
            ->put(route('portal.cinema.employees.update', $this->colleague), [
                'first_name' => 'Julie',
                'last_name' => 'Bouchard',
                'email' => 'julie.bouchard@example.com',
                'cinema_id' => $this->stranger->cinema_id,
                'is_cinema_admin' => true,
            ])
            ->assertRedirect(route('portal.cinema.employees.index'));

        expect($this->colleague->refresh())
            ->last_name->toBe('Bouchard')
            ->email->toBe('julie.bouchard@example.com')
            ->cinema_id->toBe($this->cinema->id)
            ->is_cinema_admin->toBeFalse();
    });

    it('cancels login links sent to the previous email', function () {
        $loginUrl = $this->colleague->createLoginUrl(now()->addMinutes(15));

        $this->actingAs($this->administrator, 'employee')
            ->put(route('portal.cinema.employees.update', $this->colleague), [
                'first_name' => 'Julie',
                'last_name' => 'Tremblay',
                'email' => 'nouvelle@example.com',
            ]);
        $this->post(route('portal.logout'));

        $this->post($loginUrl)->assertRedirect(route('portal.login'));
        $this->assertGuest('employee');
    });

    it('resends a pending invitation to the corrected email', function () {
        $invited = Employee::factory()->for($this->cinema)->invited()->create(['email' => 'erreur@example.com']);

        $this->actingAs($this->administrator, 'employee')
            ->put(route('portal.cinema.employees.update', $invited), [
                'first_name' => $invited->first_name,
                'last_name' => $invited->last_name,
                'email' => 'correct@example.com',
            ]);

        Notification::assertSentTo($invited->refresh(), EmployeeInvitation::class);
    });

    it('deactivates and reactivates an employee', function () {
        $this->actingAs($this->administrator, 'employee')
            ->post(route('portal.cinema.employees.deactivate', $this->colleague))
            ->assertRedirect();

        expect($this->colleague->refresh()->status)->toBe(EmployeeStatus::Deactivated);

        $this->post(route('portal.cinema.employees.reactivate', $this->colleague));

        expect($this->colleague->refresh()->status)->toBe(EmployeeStatus::Active);
    });

    it('resends a pending invitation', function () {
        $invited = Employee::factory()->for($this->cinema)->invited()->create();

        $this->actingAs($this->administrator, 'employee')
            ->post(route('portal.cinema.employees.resend-invitation', $invited))
            ->assertRedirect();

        Notification::assertSentTo($invited, EmployeeInvitation::class);
    });

    it('cannot manage employees of another cinema', function (string $method, string $routeName) {
        $this->actingAs($this->administrator, 'employee')
            ->{$method}(route($routeName, $this->stranger), [
                'first_name' => 'Pirate',
                'last_name' => 'Pirate',
                'email' => 'pirate@example.com',
            ])
            ->assertNotFound();

        expect($this->stranger->refresh())
            ->first_name->toBe('Marc')
            ->status->toBe(EmployeeStatus::Active);
    })->with([
        'update' => ['put', 'portal.cinema.employees.update'],
        'deactivate' => ['post', 'portal.cinema.employees.deactivate'],
        'resend invitation' => ['post', 'portal.cinema.employees.resend-invitation'],
    ]);

    it('cannot deactivate or edit their own account', function (string $method, string $routeName) {
        $this->actingAs($this->administrator, 'employee')
            ->{$method}(route($routeName, $this->administrator))
            ->assertNotFound();

        expect($this->administrator->refresh()->isDeactivated())->toBeFalse();
    })->with([
        'deactivate' => ['post', 'portal.cinema.employees.deactivate'],
        'edit' => ['get', 'portal.cinema.employees.edit'],
    ]);
});

describe('deleting employees', function () {
    it('deletes an employee with their training history', function () {
        TrainingProgress::factory()->for($this->colleague)->create();

        $this->actingAs($this->administrator, 'employee')
            ->delete(route('portal.cinema.employees.destroy', $this->colleague))
            ->assertRedirect(route('portal.cinema.employees.index'))
            ->assertSessionHas('status');

        expect(Employee::find($this->colleague->id))->toBeNull()
            ->and(TrainingProgress::count())->toBe(0);
    });

    it('cannot delete an employee of another cinema or their own account', function (string $target) {
        $employee = $this->{$target};

        $this->actingAs($this->administrator, 'employee')
            ->delete(route('portal.cinema.employees.destroy', $employee))
            ->assertNotFound();

        expect(Employee::find($employee->id))->not->toBeNull();
    })->with(['stranger', 'administrator']);

    it('is not available to regular employees', function () {
        $this->actingAs($this->colleague, 'employee')
            ->delete(route('portal.cinema.employees.destroy', $this->administrator))
            ->assertForbidden();

        expect(Employee::find($this->administrator->id))->not->toBeNull();
    });

    it('offers the actions in a menu, without managing their own account', function () {
        $this->actingAs($this->administrator, 'employee')
            ->get(route('portal.cinema.employees.index'))
            ->assertSee('data-actions-menu', escape: false)
            ->assertSee('action="'.route('portal.cinema.employees.destroy', $this->colleague).'"', escape: false)
            ->assertDontSee('action="'.route('portal.cinema.employees.destroy', $this->administrator).'"', escape: false);
    });
});

describe('report', function () {
    it('downloads the report of the cinema only', function () {
        Training::factory()->create();

        $response = $this->actingAs($this->administrator, 'employee')
            ->get(route('portal.cinema.report'))
            ->assertOk()
            ->assertDownload('rapport-formations-cinema-beloeil-'.now()->timezone('America/Toronto')->format('Y-m-d').'.xlsx');

        $zip = new ZipArchive;
        $zip->open($response->baseResponse->getFile()->getPathname());
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        expect($sheet)
            ->toContain('Julie Tremblay')
            ->not->toContain('Marc Gagnon');
    });
});
