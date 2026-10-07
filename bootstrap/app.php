<?php

use App\Http\Middleware\CvPanelAccess;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/cv-panel.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'cv.panel' => CvPanelAccess::class,
        ]);

        // There is no public "login" route; unauthenticated users go to the
        // login screen of whichever panel they were reaching for.
        $middleware->redirectGuestsTo(fn ($request) => $request->is('cv-panel*')
            ? route('cv-panel.login')
            : route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
