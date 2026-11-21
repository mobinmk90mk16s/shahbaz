<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Wishlist\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlists) {}
    public function index(): View
    {
        $wishlist = $this->wishlists->current();
        $products = $wishlist?->items()->active()->with('category')->orderBy('products.sort_order')->get() ?? collect();
        return view('storefront.wishlist', ['products' => $products, 'savedProductIds' => $products->pluck('id')->map(fn ($id) => (int) $id)->all()]);
    }
    public function store(Product $product): RedirectResponse
    {
        $product = Product::query()->active()->findOrFail($product->id);
        $added = $this->wishlists->toggle($product);
        return back()->with('success', $added ? 'به علاقه‌مندی‌ها اضافه شد.' : 'از علاقه‌مندی‌ها حذف شد.');
    }
    public function destroy(Product $product): RedirectResponse
    {
        $this->wishlists->remove($product);
        return back()->with('success', 'از علاقه‌مندی‌ها حذف شد.');
    }
}
