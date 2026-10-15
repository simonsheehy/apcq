<?php

namespace App\Http\Controllers\Portal\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RedirectsToIntendedPortalPage
{
    /**
     * Sends the employee back to the portal page they tried to open before logging in. The
     * intended URL is shared with the administration panel, so it is only followed when it
     * points into the portal.
     */
    protected function redirectToIntendedPortalPage(Request $request): RedirectResponse
    {
        $intendedUrl = $request->session()->pull('url.intended');
        $portalUrl = route('portal.home');

        return redirect()->to(
            is_string($intendedUrl) && str_starts_with($intendedUrl, $portalUrl)
                ? $intendedUrl
                : $portalUrl,
        );
    }
}
