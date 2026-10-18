<?php

namespace App\Http\Controllers\CvPanel;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetCvPanelLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, SetCvPanelLocale::LOCALES), 404);

        $request->session()->put(SetCvPanelLocale::SESSION_KEY, $locale);

        return back();
    }
}
