<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the cinema management pages of the portal to cinema administrators.
 */
class EnsureEmployeeIsCinemaAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $employee = $request->user('employee');

        abort_unless($employee instanceof Employee && $employee->isCinemaAdmin(), 403);

        return $next($request);
    }
}
