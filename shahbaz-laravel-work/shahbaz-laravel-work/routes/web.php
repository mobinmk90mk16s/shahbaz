<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;

// Every web route validates the status of an already authenticated account.
Route::middleware('account.active')->group(function (): void {
    require __DIR__.'/storefront.php';
    require __DIR__.'/auth.php';

    // Backward compatibility for the original static storefront URLs.
    Route::redirect('/index.html', '/', 301);
    Route::get('/product.html', function () {
        $legacyId = (string) request()->query('id', '');
        if ($legacyId === '') return redirect()->route('storefront.home', [], 301);
        $product = Product::query()->where('slug', $legacyId)->orWhere('id', ctype_digit($legacyId) ? (int) $legacyId : 0)->firstOrFail();
        return redirect()->route('storefront.products.show', $product->slug, 301);
    });
    foreach (['men', 'women', 'kids', 'bags-shoes'] as $legacyCategory) {
        Route::get('/'.$legacyCategory.'/index.html', fn () => redirect()->route('storefront.categories.'.$legacyCategory, [], 301));
    }
    Route::get('/cart/index.html', fn () => redirect()->route('cart.index', [], 301));
    Route::get('/wishlist/index.html', fn () => redirect()->route('wishlist.index', [], 301));
    Route::get('/account/index.html', fn () => redirect()->route('account.index', [], 301));
    Route::get('/search/index.html', fn () => redirect()->route('storefront.search', request()->query(), 301));
});
