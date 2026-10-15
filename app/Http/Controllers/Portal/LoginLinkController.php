<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\RedirectsToIntendedPortalPage;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class LoginLinkController extends Controller
{
    use RedirectsToIntendedPortalPage;

    /**
     * Always answers with the same message so the form cannot be used to discover which emails
     * have an account. Each address can receive at most one link per minute.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = mb_strtolower($validated['email']);

        RateLimiter::attempt("portal-login-link:{$email}", 1, function () use ($email): void {
            Employee::active()->where('email', $email)->first()?->sendLoginLink();
        }, 60);

        return back()->with('status', 'Si un compte actif correspond à cette adresse, un lien de connexion vient d’y être envoyé. Il est valide '.config('trainings.login_link_expires_in_minutes').' minutes.');
    }

    /**
     * Shows a confirmation button instead of logging in right away: mail scanners that open
     * links ahead of the recipient must not use up the single-use token.
     */
    public function show(Employee $employee, string $token): View|RedirectResponse
    {
        if (! $this->isUsable($employee, $token)) {
            return $this->invalidLinkResponse();
        }

        return view('portal.auth.login-link', [
            'employee' => $employee,
            'token' => $token,
        ]);
    }

    public function consume(Request $request, Employee $employee, string $token): RedirectResponse
    {
        if (! $this->isUsable($employee, $token)) {
            return $this->invalidLinkResponse();
        }

        $employee->consumeLoginToken();

        Auth::guard('employee')->login($employee);
        $request->session()->regenerate();

        $employee->recordLogin();

        return $this->redirectToIntendedPortalPage($request);
    }

    private function isUsable(Employee $employee, string $token): bool
    {
        return $employee->canLogIn() && $employee->hasValidLoginToken($token);
    }

    private function invalidLinkResponse(): RedirectResponse
    {
        return redirect()->route('portal.login')
            ->withErrors(['email' => 'Ce lien de connexion est expiré ou a déjà été utilisé. Demandez-en un nouveau.']);
    }
}
