<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('portal.auth.forgot-password');
    }

    /**
     * Always answers with the same message so the form cannot be used to discover which emails have an account.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::broker('employees')->sendResetLink([
            'email' => $validated['email'],
            fn (Builder $query) => $query->active(),
        ]);

        return back()->with('status', 'Si un compte actif correspond à cette adresse, un lien de réinitialisation vient d’y être envoyé.');
    }
}
