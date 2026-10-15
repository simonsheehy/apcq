<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\RedirectsToIntendedPortalPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    use RedirectsToIntendedPortalPage;

    public function create(): View
    {
        return view('portal.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password_email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $isAuthenticated = Auth::guard('employee')->attempt([
            'email' => $validated['password_email'],
            'password' => $validated['password'],
            fn (Builder $query) => $query->active(),
        ], $request->boolean('remember'));

        if (! $isAuthenticated) {
            throw ValidationException::withMessages([
                'password' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        Auth::guard('employee')->user()->recordLogin();

        return $this->redirectToIntendedPortalPage($request);
    }

    /**
     * Logs out the employee only: invalidating the whole session would also log out an
     * administrator signed in to the panel in the same browser.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('employee')->logout();

        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.home');
    }
}
