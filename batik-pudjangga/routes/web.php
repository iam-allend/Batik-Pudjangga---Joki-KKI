<?php

// ============================================
// FILE: routes/web.php
// ============================================

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ContactController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\ShippingZoneController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/shipping-info', [HomeController::class, 'shippingInfo'])->name('shipping.info');
Route::get('/return-policy', [HomeController::class, 'returnPolicy'])->name('return.policy');

// Newsletter subscription
Route::post('/subscribe', [HomeController::class, 'subscribe'])->name('subscribe');

/*
|--------------------------------------------------------------------------
| Shop Routes
|--------------------------------------------------------------------------
*/

Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [ShopController::class, 'index'])->name('index');
    Route::get('/men', [ShopController::class, 'men'])->name('men');
    Route::get('/women', [ShopController::class, 'women'])->name('women');
    Route::get('/pants', [ShopController::class, 'pants'])->name('pants');
    Route::get('/oneset', [ShopController::class, 'oneset'])->name('oneset');
    Route::get('/new-collection', [ShopController::class, 'newCollection'])->name('new');
    Route::get('/sale', [ShopController::class, 'sale'])->name('sale');
    Route::get('/search', [ShopController::class, 'search'])->name('search');
});

/*
|--------------------------------------------------------------------------
| Product Routes
|--------------------------------------------------------------------------
*/

Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

/*
|--------------------------------------------------------------------------
| Authentication Required Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Cart Routes
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::put('/update/{cart}', [CartController::class, 'update'])->name('update');
        Route::delete('/remove/{cart}', [CartController::class, 'remove'])->name('remove');
        Route::post('/checkout-selected', [CartController::class, 'checkoutSelected'])->name('checkout.selected');
    });

    // Wishlist Routes
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/add/{product}', [WishlistController::class, 'add'])->name('add');
        Route::delete('/remove/{product}', [WishlistController::class, 'remove'])->name('remove');
    });

    // Checkout Routes
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
        Route::get('/payment/{order}', [CheckoutController::class, 'payment'])->name('payment');
        Route::post('/payment/{order}', [CheckoutController::class, 'confirmPayment'])->name('payment.confirm');
        Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
    });

    // Order Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    });

    // Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/upload-image', [ProfileController::class, 'uploadImage'])->name('upload.image');
    });

    // Address Routes
    Route::prefix('addresses')->name('address.')->group(function () {
        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::get('/create', [AddressController::class, 'create'])->name('create');
        Route::post('/', [AddressController::class, 'store'])->name('store');
        Route::get('/{address}/edit', [AddressController::class, 'edit'])->name('edit');
        Route::put('/{address}', [AddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [AddressController::class, 'destroy'])->name('destroy');
        Route::post('/{address}/set-default', [AddressController::class, 'setDefault'])->name('set.default');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Products Management
    Route::resource('products', AdminProductController::class);
    Route::post('products/{product}/upload-images', [AdminProductController::class, 'uploadImages'])->name('products.upload.images');
    Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'deleteImage'])->name('products.delete.image');

    // Variants Management
    Route::post('products/{product}/variants', [AdminProductController::class, 'storeVariant'])->name('products.variants.store');
    Route::put('products/{product}/variants/{variant}', [AdminProductController::class, 'updateVariant'])->name('products.variants.update');
    Route::delete('products/{product}/variants/{variant}', [AdminProductController::class, 'destroyVariant'])->name('products.variants.destroy');

    // Orders Management
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
    Route::post('orders/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('orders.update.status');
    Route::post('orders/{order}/add-resi', [AdminOrderController::class, 'addResi'])->name('orders.add.resi');

    // Users Management
    Route::resource('users', AdminUserController::class);

    // Contacts
    Route::get('contacts', [AdminContactController::class, 'index'])->name('contacts.index');
    Route::get('contacts/{contact}', [AdminContactController::class, 'show'])->name('contacts.show');
    Route::post('contacts/{contact}/mark-read', [AdminContactController::class, 'markRead'])->name('contacts.mark.read');
    Route::delete('contacts/{contact}', [AdminContactController::class, 'destroy'])->name('contacts.destroy');

    // Shipping Zones
    Route::resource('shipping-zones', ShippingZoneController::class);

    // Reports
    Route::get('reports/sales', [DashboardController::class, 'salesReport'])->name('reports.sales');
    Route::get('reports/products', [DashboardController::class, 'productsReport'])->name('reports.products');
});


// ============ DEFAULT BREEZE ROUTES ============

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// ============ DEFAULT BREEZE ROUTES ============

require __DIR__ . '/auth.php';
