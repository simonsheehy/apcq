<?php

use App\Models\Cinema;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('requires a logged in employee', function () {
    $this->get(route('portal.profile.show'))->assertRedirect(route('portal.login'));
    $this->put(route('portal.profile.password.update'), [])->assertRedirect(route('portal.login'));
});

it('shows the employee information', function () {
    $employee = Employee::factory()
        ->for(Cinema::factory()->create(['name' => 'Cinéma Beloeil']))
        ->create(['first_name' => 'Julie', 'last_name' => 'Tremblay', 'email' => 'julie@example.com']);

    $this->actingAs($employee, 'employee')
        ->get(route('portal.profile.show'))
        ->assertOk()
        ->assertSeeInOrder(['Julie', 'Tremblay', 'julie@example.com', 'Cinéma Beloeil'])
        ->assertSee('Modifier mon mot de passe');
});

it('links to the profile from the header', function () {
    $this->actingAs(Employee::factory()->create(), 'employee')
        ->get(route('portal.home'))
        ->assertSee(route('portal.profile.show'), escape: false);
});

describe('employee without a password', function () {
    beforeEach(function () {
        $this->employee = Employee::factory()->create(['password' => null]);
        $this->actingAs($this->employee, 'employee');
    });

    it('offers to add a password', function () {
        $this->get(route('portal.profile.show'))
            ->assertSee('Ajouter un mot de passe')
            ->assertDontSee('Mot de passe actuel');
    });

    it('adds a password without asking for a current one', function () {
        $this->put(route('portal.profile.password.update'), [
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertRedirect(route('portal.profile.show'))->assertSessionHas('status');

        expect(Hash::check('nouveau-mot-de-passe', $this->employee->refresh()->password))->toBeTrue();
    });

    it('can then log in with that password', function () {
        $this->put(route('portal.profile.password.update'), [
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ]);
        $this->post(route('portal.logout'));

        $this->post(route('portal.login.store'), [
            'password_email' => $this->employee->email,
            'password' => 'nouveau-mot-de-passe',
        ])->assertRedirect(route('portal.home'));

        $this->assertAuthenticatedAs($this->employee, 'employee');
    });

    it('requires a confirmed password of at least 8 characters', function (array $input) {
        $this->put(route('portal.profile.password.update'), $input)->assertSessionHasErrors('password');

        expect($this->employee->refresh()->password)->toBeNull();
    })->with([
        'not confirmed' => [['password' => 'nouveau-mot-de-passe', 'password_confirmation' => 'autre-chose']],
        'too short' => [['password' => 'court', 'password_confirmation' => 'court']],
    ]);
});

describe('employee with a password', function () {
    beforeEach(function () {
        $this->employee = Employee::factory()->create();
        $this->actingAs($this->employee, 'employee');
    });

    it('changes the password when the current one is right', function () {
        $this->put(route('portal.profile.password.update'), [
            'current_password' => 'password',
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertRedirect(route('portal.profile.show'))->assertSessionHasNoErrors();

        expect(Hash::check('nouveau-mot-de-passe', $this->employee->refresh()->password))->toBeTrue();
    });

    it('refuses to change it without the right current password', function (?string $currentPassword) {
        $this->put(route('portal.profile.password.update'), [
            'current_password' => $currentPassword,
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertSessionHasErrors('current_password');

        expect(Hash::check('password', $this->employee->refresh()->password))->toBeTrue();
    })->with([
        'missing' => [null],
        'wrong' => ['mauvais-mot-de-passe'],
    ]);
});

it('ignores any attempt to change the cinema, name or email', function () {
    $cinema = Cinema::factory()->create();
    $otherCinema = Cinema::factory()->create();
    $employee = Employee::factory()->for($cinema)->create([
        'first_name' => 'Julie',
        'last_name' => 'Tremblay',
        'email' => 'julie@example.com',
        'password' => null,
    ]);

    $this->actingAs($employee, 'employee')
        ->put(route('portal.profile.password.update'), [
            'cinema_id' => $otherCinema->id,
            'first_name' => 'Pirate',
            'last_name' => 'Pirate',
            'email' => 'pirate@example.com',
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])
        ->assertRedirect(route('portal.profile.show'));

    expect($employee->refresh())
        ->cinema_id->toBe($cinema->id)
        ->first_name->toBe('Julie')
        ->last_name->toBe('Tremblay')
        ->email->toBe('julie@example.com');
});
