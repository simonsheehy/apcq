<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('portal.profile', [
            'employee' => $request->user('employee')->load('cinema'),
        ]);
    }

    /**
     * An employee who logs in with emailed links has no password yet and can add one directly.
     * Changing an existing password requires the current one.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $employee = $request->user('employee');
        $hasPassword = $employee->password !== null;

        $validated = $request->validate([
            'current_password' => $hasPassword ? ['required', 'current_password:employee'] : ['exclude'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $employee->forceFill([
            'password' => $validated['password'],
            'remember_token' => Str::random(60),
        ])->save();

        $request->session()->regenerate();

        return redirect()->route('portal.profile.show')
            ->with('status', $hasPassword ? 'Votre mot de passe a été modifié.' : 'Votre mot de passe a été ajouté. Vous pouvez maintenant l’utiliser pour vous connecter.');
    }
}
