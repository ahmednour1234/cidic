<?php

use App\Http\Controllers\CvPanel\CvController;
use App\Http\Controllers\CvPanel\CvPanelLoginController;
use App\Http\Controllers\CvPanel\DashboardController;
use App\Http\Controllers\CvPanel\NotificationController;
use App\Http\Controllers\CvPanel\PanelUserController;
use App\Http\Controllers\CvPanel\ReservationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CV panel
|--------------------------------------------------------------------------
| Self-contained panel for coordinators, customer service and managers.
| Deliberately outside the generic admin permission middleware: access is
| governed solely by CvPanelAccess.
*/

Route::prefix('cv-panel')->name('cv-panel.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('login', [CvPanelLoginController::class, 'create'])->name('login');
        Route::post('login', [CvPanelLoginController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('login.store');
    });

    Route::post('logout', [CvPanelLoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    Route::middleware(['auth', 'cv.panel'])->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        /* CV list and file streaming */
        // Declared before {id} so the literal segments are not read as ids.
        Route::get('cvs/reserved', [CvController::class, 'reserved'])->name('cvs.reserved');
        Route::get('cvs', [CvController::class, 'index'])->name('cvs.index');
        Route::get('cvs/{id}/file', [CvController::class, 'file'])->name('cvs.file');
        Route::delete('cvs/{id}', [CvController::class, 'destroy'])->name('cvs.destroy');
        Route::delete('cvs', [CvController::class, 'bulkDestroy'])->name('cvs.bulk-destroy');
        Route::post('cvs/{id}/assigned', [CvController::class, 'markAssigned'])->name('cvs.assigned');

        /* Upload */
        Route::get('upload', [CvController::class, 'uploadForm'])->name('upload');
        Route::post('upload', [CvController::class, 'upload'])->name('upload.store');

        /* Reservation */
        Route::get('clients/search', [ReservationController::class, 'searchClients'])->name('clients.search');
        Route::get('cvs/{id}/reserve', [ReservationController::class, 'create'])->name('cvs.reserve');
        Route::post('cvs/{id}/reserve', [ReservationController::class, 'store'])->name('cvs.reserve.store');
        Route::delete('cvs/{id}/reserve', [ReservationController::class, 'destroy'])->name('cvs.reserve.cancel');
        Route::post('cvs/{id}/tamara', [ReservationController::class, 'tamara'])->name('cvs.tamara');
        Route::post('cvs/{id}/contract', [ReservationController::class, 'createContract'])->name('cvs.contract');

        /* Notifications */
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::get('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

        /* Panel staff and coordinator assignment */
        Route::get('coordinators', [PanelUserController::class, 'coordinators'])->name('coordinators');
        Route::put('coordinators/{user}', [PanelUserController::class, 'syncNationalities'])->name('coordinators.update');
        Route::get('users', [PanelUserController::class, 'index'])->name('users.index');
        Route::post('users', [PanelUserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [PanelUserController::class, 'update'])->name('users.update');
        Route::post('users/{user}/toggle', [PanelUserController::class, 'toggle'])->name('users.toggle');

        /* Guide */
        Route::view('guide', 'cv-panel.guide')->name('guide');
    });
});
