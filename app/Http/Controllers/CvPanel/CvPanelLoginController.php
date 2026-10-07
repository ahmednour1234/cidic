<?php

namespace App\Http\Controllers\CvPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CvPanelLoginController extends Controller
{
    public function create(): View
    {
        return view('cv-panel.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('cv-panel.login.failed'),
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            $this->logout($request);

            throw ValidationException::withMessages([
                'email' => __('cv-panel.login.inactive'),
            ]);
        }

        // Non-panel users are refused here rather than at the first page, so
        // they never hold a panel session at all.
        if (! $user->canAccessCvPanel()) {
            $this->logout($request);

            throw ValidationException::withMessages([
                'email' => __('cv-panel.login.denied'),
            ]);
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended(route('cv-panel.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->logout($request);

        return redirect()->route('cv-panel.login');
    }

    private function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
