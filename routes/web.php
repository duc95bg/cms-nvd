<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/'.app()->getLocale());
});

Route::prefix('{locale}')
    ->where(['locale' => 'en|vi'])
    ->group(function () {
        Route::get('/', function () {
            return view('home');
        })->name('home');

        Route::get('/about', function () {
            return view('about');
        })->name('about');

        Route::get('/site/{slug}', [SiteController::class, 'show'])
            ->where('slug', '[a-z0-9\-]+')
            ->name('site.show');
    });

// Admin (CMS). Auth middleware lands with the future auth epic (Breeze/Jetstream).
// Until then the controller enforces ownership manually via authorizeSite().
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['web' /*, 'auth' */])
    ->group(function () {
        Route::get('sites', [SiteController::class, 'index'])->name('sites.index');
        Route::get('sites/create', [SiteController::class, 'create'])->name('sites.create');
        Route::post('sites', [SiteController::class, 'store'])->name('sites.store');
        Route::get('sites/{site}/edit', [SiteController::class, 'edit'])->name('sites.edit');
        Route::put('sites/{site}', [SiteController::class, 'update'])->name('sites.update');
        Route::get('sites/{site}/preview', [SiteController::class, 'preview'])->name('sites.preview');
        Route::post('sites/{site}/images', [SiteController::class, 'uploadImage'])->name('sites.images.upload');
    });
