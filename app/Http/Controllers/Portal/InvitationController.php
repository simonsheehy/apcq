<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\RedirectsToIntendedPortalPage;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvitationController extends Controller
{
    use RedirectsToIntendedPortalPage;

    /**
     * Shows a confirmation button instead of logging in right away: mail scanners that open
     * links ahead of the recipient must not use up the invitation.
     */
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

        $employee->acceptInvitation();

        Auth::guard('employee')->login($employee);
        $request->session()->regenerate();

        $employee->recordLogin();

        return $this->redirectToIntendedPortalPage($request);
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
                ->with('status', 'Votre accès est déjà activé. Demandez un lien de connexion ci-dessous pour accéder à vos formations.');
        }

        return null;
    }
}
