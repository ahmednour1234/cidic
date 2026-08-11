<?php

use App\Http\Controllers\Admin\CandidateCategoryController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\CandidateRequestController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\HowItWorksController;
use App\Http\Controllers\Admin\NationalityController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\RecruitmentRequestController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WhyChooseUsController;
use App\Http\Controllers\Auth\AdminLoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin dashboard
|--------------------------------------------------------------------------
| Same monolithic application; separated only by prefix, name and middleware.
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('login', [AdminLoginController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('login.store');
    });

    Route::post('logout', [AdminLoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    // Authenticated dashboard
    Route::middleware(['auth', 'admin'])->group(function () {

        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        /* Candidates */
        Route::middleware('can:manage_candidates')->group(function () {
            // Declared before the resource so "bulk" is not matched as a slug.
            Route::get('candidates/bulk', [CandidateController::class, 'bulkCreate'])
                ->name('candidates.bulk');
            Route::post('candidates/bulk', [CandidateController::class, 'bulkStore'])
                ->name('candidates.bulk.store');

            Route::patch('candidates/{candidate}/availability', [CandidateController::class, 'updateAvailability'])
                ->name('candidates.availability');
            Route::patch('candidates/{candidate}/toggle-featured', [CandidateController::class, 'toggleFeatured'])
                ->name('candidates.toggle-featured');
            Route::patch('candidates/{candidate}/toggle-active', [CandidateController::class, 'toggleActive'])
                ->name('candidates.toggle-active');
            Route::resource('candidates', CandidateController::class);
        });

        /* Requests */
        Route::middleware('can:manage_requests')->group(function () {
            Route::patch('candidate-requests/{candidate_request}/status', [CandidateRequestController::class, 'updateStatus'])
                ->name('candidate-requests.status');
            Route::resource('candidate-requests', CandidateRequestController::class)
                ->only(['index', 'show', 'update', 'destroy']);

            Route::patch('recruitment-requests/{recruitment_request}/status', [RecruitmentRequestController::class, 'updateStatus'])
                ->name('recruitment-requests.status');
            Route::resource('recruitment-requests', RecruitmentRequestController::class)
                ->only(['index', 'show', 'update', 'destroy']);

            Route::patch('contact-messages/{contact_message}/status', [ContactMessageController::class, 'updateStatus'])
                ->name('contact-messages.status');
            Route::resource('contact-messages', ContactMessageController::class)
                ->only(['index', 'show', 'destroy']);
        });

        /* Core data — these modules edit in place, so no show route. */
        Route::middleware('can:manage_services')->group(function () {
            Route::resource('services', ServiceController::class)->except(['show']);
            Route::resource('nationalities', NationalityController::class)->except(['show']);
            Route::resource('categories', CandidateCategoryController::class)->except(['show']);
        });

        /* Content */
        Route::middleware('can:manage_content')->group(function () {
            Route::resource('how-it-works', HowItWorksController::class)
                ->parameters(['how-it-works' => 'record'])
                ->except(['show']);
            Route::resource('why-choose-us', WhyChooseUsController::class)
                ->parameters(['why-choose-us' => 'record'])
                ->except(['show']);
            Route::resource('testimonials', TestimonialController::class)->except(['show']);
            Route::resource('faqs', FaqController::class)->except(['show']);
            Route::resource('pages', PageController::class)->except(['show']);
        });

        /* Settings */
        Route::middleware('can:manage_settings')->group(function () {
            Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        });

        /* Users */
        Route::middleware('can:manage_users')->group(function () {
            Route::resource('users', UserController::class);
        });
    });
});
