<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the CV panel. Access is granted to super admins and to the three
 * panel departments; everyone else is refused, even if they hold dashboard
 * permissions. Guests are sent to the panel's own login screen.
 */
class CvPanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->guest(route('cv-panel.login'));
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('cv-panel.login')
                ->withErrors(['email' => 'حسابك غير مفعّل. يرجى التواصل مع المدير.']);
        }

        if (! $user->canAccessCvPanel()) {
            abort(403, 'ليس لديك صلاحية الدخول إلى لوحة السير الذاتية.');
        }

        return $next($request);
    }
}
