<?php

use App\Models\Employee;
use App\Models\User;
use App\Notifications\EmployeeResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

describe('access', function () {
    it('redirects guests to the portal login', function () {
        $this->get(route('portal.home'))->assertRedirect(route('portal.login'));
    });

    it('does not let an admin user into the portal', function () {
        $this->actingAs(User::factory()->create())
            ->get(route('portal.home'))
            ->assertRedirect(route('portal.login'));
    });

    it('shows the portal home to an active employee', function () {
        $employee = Employee::factory()->create(['first_name' => 'Julie']);

        $this->actingAs($employee, 'employee')
            ->get(route('portal.home'))
            ->assertOk()
            ->assertSee('Bonjour Julie');
    });

    it('logs out an employee deactivated during their session', function () {
        $employee = Employee::factory()->deactivated()->create();

        $this->actingAs($employee, 'employee')
            ->get(route('portal.home'))
            ->assertRedirect(route('portal.login'));

        $this->assertGuest('employee');
    });
});

describe('login', function () {
    it('logs in an active employee', function () {
        $employee = Employee::factory()->create();

        $this->post(route('portal.login.store'), [
            'email' => $employee->email,
            'password' => 'password',
        ])->assertRedirect(route('portal.home'));

        $this->assertAuthenticatedAs($employee, 'employee');
        expect($employee->refresh()->last_login_at)->not->toBeNull();
    });

    it('rejects a wrong password', function () {
        $employee = Employee::factory()->create();

        $this->post(route('portal.login.store'), [
            'email' => $employee->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('employee');
    });

    it('rejects a deactivated employee', function () {
        $employee = Employee::factory()->deactivated()->create();

        $this->post(route('portal.login.store'), [
            'email' => $employee->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('employee');
    });

    it('logs out', function () {
        $this->actingAs(Employee::factory()->create(), 'employee')
            ->post(route('portal.logout'))
            ->assertRedirect(route('portal.login'));

        $this->assertGuest('employee');
    });
});

describe('invitation', function () {
    it('lets an invited employee choose a password', function () {
        $employee = Employee::factory()->invited()->create();
        $url = $employee->invitationUrl();

        $this->get($url)->assertOk()->assertSee($employee->email);

        $this->post($url, [
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertRedirect(route('portal.home'));

        $employee->refresh();
        expect($employee->hasAcceptedInvitation())->toBeTrue()
            ->and(Hash::check('nouveau-mot-de-passe', $employee->password))->toBeTrue();
        $this->assertAuthenticatedAs($employee, 'employee');
    });

    it('rejects a tampered invitation link', function () {
        $employee = Employee::factory()->invited()->create();
        $other = Employee::factory()->invited()->create();

        $tamperedUrl = str_replace("/invitation/{$employee->id}", "/invitation/{$other->id}", $employee->invitationUrl());

        $this->get($tamperedUrl)->assertForbidden();
    });

    it('rejects an expired invitation link', function () {
        $employee = Employee::factory()->invited()->create();
        $url = $employee->invitationUrl();

        $this->travel(config('trainings.invitation_expires_in_days') + 1)->days();

        $this->get($url)->assertForbidden();
    });

    it('cannot be reused once accepted', function () {
        $employee = Employee::factory()->invited()->create();
        $url = $employee->invitationUrl();
        $employee->acceptInvitation('premier-mot-de-passe');

        $this->post($url, [
            'password' => 'pirate-mot-de-passe',
            'password_confirmation' => 'pirate-mot-de-passe',
        ])->assertRedirect(route('portal.login'));

        expect(Hash::check('premier-mot-de-passe', $employee->refresh()->password))->toBeTrue();
    });

    it('cannot be used by a deactivated employee', function () {
        $employee = Employee::factory()->invited()->deactivated()->create();

        $this->post($employee->invitationUrl(), [
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertRedirect(route('portal.login'));

        expect($employee->refresh()->hasAcceptedInvitation())->toBeFalse();
    });

    it('requires a confirmed password', function () {
        $employee = Employee::factory()->invited()->create();

        $this->post($employee->invitationUrl(), [
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'autre-chose',
        ])->assertSessionHasErrors('password');
    });
});

describe('password reset', function () {
    it('emails a reset link to an active employee', function () {
        Notification::fake();
        $employee = Employee::factory()->create();

        $this->post(route('portal.password.email'), ['email' => $employee->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($employee, EmployeeResetPassword::class);
    });

    it('answers the same way without emailing unknown, invited or deactivated accounts', function (string $email) {
        Notification::fake();

        $this->post(route('portal.password.email'), ['email' => $email])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    })->with([
        'unknown' => fn () => 'personne@example.com',
        'invited' => fn () => Employee::factory()->invited()->create()->email,
        'deactivated' => fn () => Employee::factory()->deactivated()->create()->email,
    ]);

    it('resets the password with a valid token', function () {
        $employee = Employee::factory()->create();
        $token = Password::broker('employees')->createToken($employee);

        $this->get(route('portal.password.reset', ['token' => $token, 'email' => $employee->email]))
            ->assertOk();

        $this->post(route('portal.password.update'), [
            'token' => $token,
            'email' => $employee->email,
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertRedirect(route('portal.login'));

        expect(Hash::check('nouveau-mot-de-passe', $employee->refresh()->password))->toBeTrue();
    });

    it('rejects an invalid token', function () {
        $employee = Employee::factory()->create();

        $this->post(route('portal.password.update'), [
            'token' => 'invalide',
            'email' => $employee->email,
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertSessionHasErrors('email');

        expect(Hash::check('password', $employee->refresh()->password))->toBeTrue();
    });
});
