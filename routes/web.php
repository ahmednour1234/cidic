<?php

use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CandidateRequestController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NationalityController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RecruitmentRequestController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/nationalities', [NationalityController::class, 'index'])->name('nationalities.index');

Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');
Route::get('/candidates/{candidate}', [CandidateController::class, 'show'])->name('candidates.show');

/*
| Customer requests — guests may submit; writes are rate limited.
*/
Route::get('/request', [RecruitmentRequestController::class, 'create'])->name('recruitment-requests.create');
Route::post('/request', [RecruitmentRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('recruitment-requests.store');

Route::get('/request/candidate/{candidate}', [CandidateRequestController::class, 'create'])
    ->name('candidate-requests.create');
Route::post('/request/candidate/{candidate}', [CandidateRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('candidate-requests.store');

Route::get('/request/success/{number}', [RecruitmentRequestController::class, 'success'])
    ->name('requests.success');

/*
| Contact
*/
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('contact.store');

/*
| Static / CMS pages
*/
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

/*
| SEO
*/
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
