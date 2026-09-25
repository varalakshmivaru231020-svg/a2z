<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
*/

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/recruitment', [RecruitmentController::class, 'index'])->name('recruitment.index');
Route::get('/recruitment/{slug}', [RecruitmentController::class, 'show'])->name('recruitment.show');
Route::post('/recruitment/{slug}/apply', [RecruitmentController::class, 'apply'])
    ->middleware('throttle:6,1')
    ->name('recruitment.apply');

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('contact.store');

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'create'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'destroy'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::resource('services', Admin\ServiceController::class)->except('show');

        // "leadership" does not singularise, so name the parameter to match the controller's Leader $leader.
        Route::resource('leadership', Admin\LeaderController::class)->parameters(['leadership' => 'leader'])->except('show');

        Route::resource('jobs', Admin\JobOpeningController::class)->except('show');
        Route::patch('jobs/{job}/close', [Admin\JobOpeningController::class, 'close'])->name('jobs.close');
        Route::patch('jobs/{job}/reopen', [Admin\JobOpeningController::class, 'reopen'])->name('jobs.reopen');

        // "export" must be declared before the {application} wildcard.
        Route::get('applications/export', [Admin\ApplicationController::class, 'export'])->name('applications.export');
        Route::get('applications', [Admin\ApplicationController::class, 'index'])->name('applications.index');
        Route::get('applications/{application}', [Admin\ApplicationController::class, 'show'])->name('applications.show');
        Route::patch('applications/{application}', [Admin\ApplicationController::class, 'update'])->name('applications.update');
        Route::delete('applications/{application}', [Admin\ApplicationController::class, 'destroy'])->name('applications.destroy');
        Route::get('applications/{application}/resume', [Admin\ApplicationController::class, 'resume'])->name('applications.resume');

        Route::get('gallery', [Admin\GalleryController::class, 'index'])->name('gallery.index');
        Route::post('gallery', [Admin\GalleryController::class, 'store'])->name('gallery.store');
        Route::patch('gallery/{item}', [Admin\GalleryController::class, 'update'])->name('gallery.update');
        Route::delete('gallery/{item}', [Admin\GalleryController::class, 'destroy'])->name('gallery.destroy');

        Route::get('enquiries', [Admin\EnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('enquiries/{enquiry}', [Admin\EnquiryController::class, 'show'])->name('enquiries.show');
        Route::delete('enquiries/{enquiry}', [Admin\EnquiryController::class, 'destroy'])->name('enquiries.destroy');

        Route::get('seo', [Admin\SeoPageController::class, 'edit'])->name('seo.edit');
        Route::put('seo', [Admin\SeoPageController::class, 'update'])->name('seo.update');

        Route::get('images', [Admin\PageImageController::class, 'edit'])->name('images.edit');
        Route::put('images', [Admin\PageImageController::class, 'update'])->name('images.update');

        Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
    });
});
