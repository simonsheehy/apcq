<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs out an employee whose access was deactivated while their session was open.
 */
class EnsureEmployeeIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $employee = Auth::guard('employee')->user();

        if ($employee instanceof Employee && ! $employee->canLogIn()) {
            Auth::guard('employee')->logout();
            $request->session()->regenerate();
            $request->session()->regenerateToken();

            return redirect()->route('portal.login')
                ->withErrors(['email' => 'Votre accès au Centre d’expertise n’est plus actif.']);
        }

        return $next($request);
    }
}
