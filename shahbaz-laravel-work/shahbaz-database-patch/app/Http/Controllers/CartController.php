<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $carts) {}
    public function index(): View
    {
        $cart = $this->carts->current();
        $cart?->load('items.product.category');
        return view('storefront.cart', ['cart' => $cart]);
    }
    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $product = Product::query()->active()->findOrFail($data['product_id']);
        $this->carts->add($product, (int) ($data['quantity'] ?? 1), $data['size'] ?? null, $data['color'] ?? null);
        return back()->with('success', 'محصول به سبد خرید اضافه شد.');
    }
    public function update(UpdateCartItemRequest $request, CartItem $item): RedirectResponse
    {
        $this->carts->update($item, (int) $request->validated('quantity'));
        return back()->with('success', 'تعداد محصول به‌روزرسانی شد.');
    }
    public function destroy(CartItem $item): RedirectResponse
    {
        $this->carts->remove($item);
        return back()->with('success', 'محصول از سبد حذف شد.');
    }
}
