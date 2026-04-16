<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/'.app()->getLocale());
});

// Breeze dashboard + profile
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public locale-prefixed routes
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

        // Product catalog — public
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/category/{slug}', [ProductController::class, 'byCategory'])
            ->where('slug', '[a-z0-9\-]+')
            ->name('products.category');
        Route::get('/product/{slug}', [ProductController::class, 'show'])
            ->where('slug', '[a-z0-9\-]+')
            ->name('products.show');
    });

// API (no auth — public variant price lookup)
Route::get('/api/product/{product}/variant-price', [\App\Http\Controllers\Api\VariantPriceController::class, '__invoke'])
    ->name('api.variant-price');

// Admin CMS — requires authenticated user
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        // Sites
        Route::get('sites', [SiteController::class, 'index'])->name('sites.index');
        Route::get('sites/create', [SiteController::class, 'create'])->name('sites.create');
        Route::post('sites', [SiteController::class, 'store'])->name('sites.store');
        Route::get('sites/{site}/edit', [SiteController::class, 'edit'])->name('sites.edit');
        Route::put('sites/{site}', [SiteController::class, 'update'])->name('sites.update');
        Route::get('sites/{site}/preview', [SiteController::class, 'preview'])->name('sites.preview');
        Route::post('sites/{site}/images', [SiteController::class, 'uploadImage'])->name('sites.images.upload');

        // Categories
        Route::resource('categories', CategoryController::class);

        // Attributes
        Route::resource('attributes', AttributeController::class);

        // Products
        Route::resource('products', AdminProductController::class);
        Route::post('products/{product}/variants/generate', [AdminProductController::class, 'generateVariants'])
            ->name('products.variants.generate');
        Route::post('products/{product}/images', [AdminProductController::class, 'uploadImage'])
            ->name('products.images.upload');
        Route::post('products/{product}/images/{image}/primary', [AdminProductController::class, 'setPrimaryImage'])
            ->name('products.images.primary');
        Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'deleteImage'])
            ->name('products.images.destroy');
    });

require __DIR__.'/auth.php';
