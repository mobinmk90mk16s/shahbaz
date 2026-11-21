<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\GuestMigrationController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('storefront.home');
foreach (['men', 'women', 'kids', 'bags-shoes'] as $categorySlug) {
    Route::get('/'.$categorySlug, [StorefrontController::class, 'category'])
        ->defaults('slug', $categorySlug)
        ->name('storefront.categories.'.$categorySlug);
}
Route::get('/product/{slug}', [StorefrontController::class, 'product'])
    ->where('slug', '[A-Za-z0-9-]+')
    ->name('storefront.products.show');
Route::get('/search', [StorefrontController::class, 'search'])->name('storefront.search');
Route::get('/search/suggestions', [StorefrontController::class, 'suggest'])->middleware('throttle:60,1')->name('storefront.search.suggest');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'store'])->middleware('throttle:20,1')->name('cart.items.store');
Route::patch('/cart/items/{item}', [CartController::class, 'update'])->middleware('throttle:30,1')->name('cart.items.update');
Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->middleware('throttle:30,1')->name('cart.items.destroy');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->middleware('throttle:30,1')->name('wishlist.store');
Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->middleware('throttle:30,1')->name('wishlist.destroy');

Route::post('/guest/migrate', [GuestMigrationController::class, 'store'])->middleware('throttle:5,1')->name('guest.migrate');
Route::get('/account', [AccountController::class, 'index'])->name('account.index');
Route::post('/account/guest-profile', [AccountController::class, 'saveGuestProfile'])->middleware('throttle:10,1')->name('account.guest-profile');
Route::middleware('auth')->group(function () {
    Route::put('/account/profile', [AccountController::class, 'update'])->name('account.profile.update');
});
