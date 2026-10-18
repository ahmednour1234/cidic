<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the CV panel language chosen with the header switcher. The choice
 * lives in the session so it survives login/logout within the same browser.
 */
class SetCvPanelLocale
{
    /** Supported panel languages: code => [native name, text direction]. */
    public const LOCALES = [
        'ar' => ['العربية', 'rtl'],
        'en' => ['English', 'ltr'],
        'fil' => ['Filipino', 'ltr'],
        'si' => ['සිංහල', 'ltr'],
    ];

    public const SESSION_KEY = 'cv_panel_locale';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get(self::SESSION_KEY, config('app.locale'));

        if (array_key_exists($locale, self::LOCALES)) {
            app()->setLocale($locale);
            Carbon::setLocale($locale);
        }

        return $next($request);
    }

    public static function direction(?string $locale = null): string
    {
        return self::LOCALES[$locale ?? app()->getLocale()][1] ?? 'rtl';
    }
}
