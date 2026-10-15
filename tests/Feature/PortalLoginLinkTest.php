<?php

use App\Models\Employee;
use App\Notifications\EmployeeLoginLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

/**
 * Requests a login link for the employee and returns the URL received by email.
 */
function requestLoginLink(Employee $employee): string
{
    test()->post(route('portal.login-link.store'), ['email' => $employee->email]);

    $url = null;
    Notification::assertSentTo($employee, EmployeeLoginLink::class, function (EmployeeLoginLink $notification) use (&$url): bool {
        $url = $notification->loginUrl;

        return true;
    });

    return $url;
}

describe('requesting a link', function () {
    it('emails a login link to an active employee', function () {
        $employee = Employee::factory()->create();

        $this->post(route('portal.login-link.store'), ['email' => $employee->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($employee, EmployeeLoginLink::class);
        expect($employee->refresh()->login_token_hash)->toHaveLength(64);
    });

    it('answers the same way without emailing unknown, invited or deactivated accounts', function (string $email) {
        $this->post(route('portal.login-link.store'), ['email' => $email])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    })->with([
        'unknown' => fn () => 'personne@example.com',
        'invited' => fn () => Employee::factory()->invited()->create()->email,
        'deactivated' => fn () => Employee::factory()->deactivated()->create()->email,
    ]);

    it('sends at most one link per minute to the same address', function () {
        $employee = Employee::factory()->create();

        $this->post(route('portal.login-link.store'), ['email' => $employee->email]);
        $this->post(route('portal.login-link.store'), ['email' => strtoupper($employee->email)])
            ->assertSessionHas('status');

        Notification::assertSentToTimes($employee, EmployeeLoginLink::class, 1);
    });

    it('never stores the token itself', function () {
        $employee = Employee::factory()->create();

        $url = requestLoginLink($employee);
        $token = basename($url);

        expect($employee->refresh()->login_token_hash)
            ->not->toBe($token)
            ->toBe(hash('sha256', $token));
    });
});

describe('using a link', function () {
    it('asks for a click before logging in, so mail scanners cannot use the link', function () {
        $employee = Employee::factory()->create(['first_name' => 'Julie']);
        $url = requestLoginLink($employee);

        $this->get($url)->assertOk()->assertSee('Bonjour, Julie');

        $this->assertGuest('employee');
        expect($employee->refresh()->login_token_hash)->not->toBeNull();
    });

    it('logs the employee in', function () {
        $employee = Employee::factory()->create();
        $url = requestLoginLink($employee);

        $this->post($url)->assertRedirect(route('portal.home'));

        $this->assertAuthenticatedAs($employee, 'employee');
        expect($employee->refresh()->last_login_at)->not->toBeNull();
    });

    it('only works once', function () {
        $employee = Employee::factory()->create();
        $url = requestLoginLink($employee);

        $this->post($url);
        $this->post(route('portal.logout'));

        $this->post($url)->assertRedirect(route('portal.login'))->assertSessionHasErrors('email');
        $this->assertGuest('employee');
    });

    it('expires', function () {
        $employee = Employee::factory()->create();
        $url = requestLoginLink($employee);

        $this->travel(config('trainings.login_link_expires_in_minutes') + 1)->minutes();

        $this->get($url)->assertRedirect(route('portal.login'));
        $this->post($url)->assertRedirect(route('portal.login'));
        $this->assertGuest('employee');
    });

    it('is replaced by a newer link', function () {
        $employee = Employee::factory()->create();
        $firstUrl = requestLoginLink($employee);

        $this->travel(2)->minutes();
        Notification::fake();
        $secondUrl = requestLoginLink($employee);

        $this->post($firstUrl)->assertRedirect(route('portal.login'));
        $this->assertGuest('employee');

        $this->post($secondUrl)->assertRedirect(route('portal.home'));
    });

    it('rejects a wrong token or another employee', function () {
        $employee = Employee::factory()->create();
        $other = Employee::factory()->create();
        $url = requestLoginLink($employee);
        $token = basename($url);

        $this->post(route('portal.login-link.consume', ['employee' => $employee, 'token' => str_repeat('a', 64)]))
            ->assertRedirect(route('portal.login'));
        $this->post(route('portal.login-link.consume', ['employee' => $other, 'token' => $token]))
            ->assertRedirect(route('portal.login'));

        $this->assertGuest('employee');
    });

    it('stops working once the employee is deactivated', function () {
        $employee = Employee::factory()->create();
        $url = requestLoginLink($employee);

        $employee->deactivate();

        $this->post($url)->assertRedirect(route('portal.login'));
        $this->assertGuest('employee');
    });
});
