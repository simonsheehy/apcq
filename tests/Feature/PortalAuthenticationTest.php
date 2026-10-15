<?php

use App\Models\Employee;
use App\Models\Training;
use App\Models\User;
use App\Notifications\EmployeeInvitation;
use App\Notifications\EmployeeResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

describe('access', function () {
    it('shows the list of trainings to everyone', function () {
        $training = Training::factory()->create(['title' => 'Qualité audio']);

        $this->get(route('portal.home'))
            ->assertOk()
            ->assertSee('Qualité audio')
            ->assertSee($training->portalUrl(), escape: false)
            ->assertSee('Connexion requise')
            ->assertSee(route('portal.login'), escape: false);
    });

    it('requires a login to open a training', function () {
        $training = Training::factory()->create();

        $this->get($training->portalUrl())->assertRedirect(route('portal.login'));
    });

    it('does not let an admin user into a training', function () {
        $training = Training::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get($training->portalUrl())
            ->assertRedirect(route('portal.login'));
    });

    it('greets a logged in employee and hides the login prompts', function () {
        Training::factory()->create();
        $employee = Employee::factory()->create(['first_name' => 'Julie']);

        $this->actingAs($employee, 'employee')
            ->get(route('portal.home'))
            ->assertOk()
            ->assertSee('Bonjour Julie')
            ->assertDontSee('Connexion requise')
            ->assertDontSee('Se connecter');
    });

    it('logs out an employee deactivated during their session', function () {
        $employee = Employee::factory()->deactivated()->create();

        $this->actingAs($employee, 'employee')
            ->get(Training::factory()->create()->portalUrl())
            ->assertRedirect(route('portal.login'));

        $this->assertGuest('employee');
    });

    it('brings the employee back to the training they wanted after logging in', function (string $method) {
        $employee = Employee::factory()->create();
        $training = Training::factory()->create();

        $this->get($training->portalUrl())->assertRedirect(route('portal.login'));

        $response = match ($method) {
            'password' => $this->post(route('portal.login.store'), [
                'password_email' => $employee->email,
                'password' => 'password',
            ]),
            'login link' => $this->post($employee->createLoginUrl(now()->addMinutes(15))),
        };

        $response->assertRedirect($training->portalUrl());
    })->with(['password', 'login link']);
});

describe('login', function () {
    it('logs in an active employee', function () {
        $employee = Employee::factory()->create();

        $this->post(route('portal.login.store'), [
            'password_email' => $employee->email,
            'password' => 'password',
        ])->assertRedirect(route('portal.home'));

        $this->assertAuthenticatedAs($employee, 'employee');
        expect($employee->refresh()->last_login_at)->not->toBeNull();
    });

    it('rejects a wrong password', function () {
        $employee = Employee::factory()->create();

        $this->post(route('portal.login.store'), [
            'password_email' => $employee->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('password');

        $this->assertGuest('employee');
    });

    it('rejects a deactivated employee', function () {
        $employee = Employee::factory()->deactivated()->create();

        $this->post(route('portal.login.store'), [
            'password_email' => $employee->email,
            'password' => 'password',
        ])->assertSessionHasErrors('password');

        $this->assertGuest('employee');
    });

    it('logs out', function () {
        $this->actingAs(Employee::factory()->create(), 'employee')
            ->post(route('portal.logout'))
            ->assertRedirect(route('portal.home'));

        $this->assertGuest('employee');
    });
});

describe('invitation', function () {
    it('logs an invited employee in directly, without a password', function () {
        $employee = Employee::factory()->invited()->create();
        $url = $employee->invitationUrl();

        $this->get($url)->assertOk()->assertSee($employee->email);
        $this->assertGuest('employee');

        $this->post($url)->assertRedirect(route('portal.home'));

        $employee->refresh();
        expect($employee->hasAcceptedInvitation())->toBeTrue()
            ->and($employee->password)->toBeNull()
            ->and($employee->last_login_at)->not->toBeNull();
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

    it('only works once', function () {
        $employee = Employee::factory()->invited()->create();
        $url = $employee->invitationUrl();

        $this->post($url)->assertRedirect(route('portal.home'));
        $this->post(route('portal.logout'));

        $this->post($url)->assertRedirect(route('portal.login'));
        $this->assertGuest('employee');
    });

    it('cannot be used by a deactivated employee', function () {
        $employee = Employee::factory()->invited()->deactivated()->create();

        $this->post($employee->invitationUrl())->assertRedirect(route('portal.login'));

        expect($employee->refresh()->hasAcceptedInvitation())->toBeFalse();
        $this->assertGuest('employee');
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

describe('invitation link fingerprint', function () {
    it('invalidates earlier links when the invitation is resent', function () {
        Notification::fake();
        $employee = Employee::factory()->invited()->create(['invited_at' => now()->subDay()]);
        $oldUrl = $employee->invitationUrl();

        $employee->sendInvitation();

        $this->get($oldUrl)->assertRedirect(route('portal.login'))->assertSessionHasErrors('email');
        $this->get($employee->invitationUrl())->assertOk();
    });

    it('invalidates earlier links when the email is corrected', function () {
        $employee = Employee::factory()->invited()->create(['email' => 'erreur@example.com']);
        $oldUrl = $employee->invitationUrl();

        $employee->update(['email' => 'julie@example.com']);

        $this->post($oldUrl)->assertRedirect(route('portal.login'));

        expect($employee->refresh()->hasAcceptedInvitation())->toBeFalse();
    });
});

describe('deleted cinema', function () {
    it('blocks login for employees of a deleted cinema', function () {
        $employee = Employee::factory()->create();
        $employee->cinema->delete();

        $this->post(route('portal.login.store'), [
            'password_email' => $employee->email,
            'password' => 'password',
        ])->assertSessionHasErrors('password');

        $this->assertGuest('employee');
    });

    it('still sends the invitation email when the cinema was deleted', function () {
        $employee = Employee::factory()->invited()->create();
        $employee->cinema->delete();

        $mail = (new EmployeeInvitation)->toMail($employee->refresh());

        expect(implode(' ', $mail->introLines))->toContain($employee->cinema()->withTrashed()->value('name'));
    });
});

describe('session sharing with the administration', function () {
    it('keeps the administrator logged in when an employee logs out', function () {
        $admin = User::factory()->create();
        $adminSessionKey = auth()->guard('web')->getName();

        $this->withSession([$adminSessionKey => $admin->id])
            ->actingAs(Employee::factory()->create(), 'employee')
            ->post(route('portal.logout'))
            ->assertSessionHas($adminSessionKey, $admin->id);

        $this->assertGuest('employee');
    });

    it('does not send an employee to an administration page after login', function () {
        $employee = Employee::factory()->create();

        $this->withSession(['url.intended' => url('/admin/rapport-formations')])
            ->post(route('portal.login.store'), [
                'password_email' => $employee->email,
                'password' => 'password',
            ])->assertRedirect(route('portal.home'));
    });
});
