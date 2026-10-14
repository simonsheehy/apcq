<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(Request $request, Employee $employee): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfUnusable($request, $employee)) {
            return $redirect;
        }

        return view('portal.auth.accept-invitation', [
            'employee' => $employee,
            'action' => $request->fullUrl(),
        ]);
    }

    public function store(Request $request, Employee $employee): RedirectResponse
    {
        if ($redirect = $this->redirectIfUnusable($request, $employee)) {
            return $redirect;
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $employee->acceptInvitation($validated['password']);

        Auth::guard('employee')->login($employee);
        $request->session()->regenerate();

        $employee->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('portal.home');
    }

    private function redirectIfUnusable(Request $request, Employee $employee): ?RedirectResponse
    {
        if (! $employee->hasInvitationFingerprint($request->query('fingerprint'))) {
            return redirect()->route('portal.login')
                ->withErrors(['email' => 'Ce lien d’invitation n’est plus valide. Demandez à l’APCQ de vous en envoyer un nouveau.']);
        }

        if ($employee->isDeactivated() || ! $employee->canAccessCinema()) {
            return redirect()->route('portal.login')
                ->withErrors(['email' => 'Votre accès au Centre d’expertise a été désactivé.']);
        }

        if ($employee->hasAcceptedInvitation()) {
            return redirect()->route('portal.login')
                ->with('status', 'Votre accès est déjà activé. Connectez-vous avec votre mot de passe.');
        }

        return null;
    }
}
