<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BlockEditorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
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

// Cart (no auth, no locale prefix — session-based)
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

// Checkout (no auth)
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');

// Payment callbacks (no auth, no CSRF)
Route::withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->group(function () {
    Route::get('/payment/vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('payment.vnpay.return');
    Route::get('/payment/vnpay/ipn', [PaymentController::class, 'vnpayIpn'])->name('payment.vnpay.ipn');
    Route::get('/payment/paypal/return', [PaymentController::class, 'paypalReturn'])->name('payment.paypal.return');
    Route::get('/payment/paypal/cancel', [PaymentController::class, 'paypalCancel'])->name('payment.paypal.cancel');
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

        // Cart page
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

        // Checkout page
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');

        // Order result pages
        Route::get('/order/success/{order}', [OrderController::class, 'success'])->name('orders.success');
        Route::get('/order/bank-transfer/{order}', [OrderController::class, 'bankTransfer'])->name('orders.bank-transfer');

        // User order history (auth)
        Route::middleware('auth')->group(function () {
            Route::get('/orders', [OrderController::class, 'history'])->name('orders.history');
            Route::get('/orders/{order}', [OrderController::class, 'detail'])->name('orders.detail');
        });
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

        // Orders
        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

        // Block editor
        Route::get('sites/{site}/editor', [BlockEditorController::class, 'edit'])->name('sites.editor');
        Route::put('sites/{site}/blocks', [BlockEditorController::class, 'update'])->name('sites.blocks.update');
        Route::post('sites/{site}/blocks/upload', [BlockEditorController::class, 'uploadImage'])->name('sites.blocks.upload');

        // Settings
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
    });

require __DIR__.'/auth.php';
