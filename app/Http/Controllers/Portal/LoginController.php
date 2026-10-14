<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('portal.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $isAuthenticated = Auth::guard('employee')->attempt([
            ...$credentials,
            fn (Builder $query) => $query->active(),
        ], $request->boolean('remember'));

        if (! $isAuthenticated) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        Auth::guard('employee')->user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->to($this->intendedPortalUrl($request));
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

        return redirect()->route('portal.login');
    }

    /**
     * The intended URL is shared with the administration panel, so it is only followed when it
     * points into the portal.
     */
    private function intendedPortalUrl(Request $request): string
    {
        $intendedUrl = $request->session()->pull('url.intended');
        $portalUrl = route('portal.home');

        return is_string($intendedUrl) && str_starts_with($intendedUrl, $portalUrl)
            ? $intendedUrl
            : $portalUrl;
    }
}
