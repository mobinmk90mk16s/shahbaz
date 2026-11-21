<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(private readonly GuestIdentity $guest) {}

    public function current(bool $create = false): ?Cart
    {
        if ($user = auth()->user()) {
            if (!$create) return Cart::query()->where('user_id', $user->id)->with('items.product.category')->first();
            return Cart::firstOrCreate(['user_id' => $user->id], ['guest_token_hash' => null])->load('items.product.category');
        }
        $hash = $this->guest->hash($create);
        if (!$hash) return null;
        $cart = Cart::query()->where('guest_token_hash', $hash)->with('items.product.category')->first();
        if (!$cart && $create) $cart = Cart::create(['guest_token_hash' => $hash]);
        return $cart?->load('items.product.category');
    }

    public function add(Product $product, int $quantity = 1, ?string $size = null, ?string $color = null): Cart
    {
        if (!$product->is_active) throw ValidationException::withMessages(['product_id' => 'این محصول در دسترس نیست.']);
        $size = $this->allowedChoice($size, $product->sizes, 'size');
        $color = $this->allowedChoice($color, $product->colors, 'color');
        $cart = $this->current(true);
        $lineHash = hash('sha256', $product->id.'|'.($size ?? '').'|'.($color ?? ''));
        $item = CartItem::query()->firstOrNew(['cart_id' => $cart->id, 'line_hash' => $lineHash]);
        $item->product_id = $product->id;
        $item->selected_size = $size;
        $item->selected_color = $color;
        $item->quantity = min(99, ((int) $item->quantity) + max(1, min(99, $quantity)));
        $item->save();
        return $cart->load('items.product.category');
    }

    private function allowedChoice(?string $value, ?array $allowed, string $field): ?string
    {
        $allowed = array_values(array_filter($allowed ?? []));
        if ($value === null || $value === '') return $allowed[0] ?? null;
        if (!in_array($value, $allowed, true)) throw ValidationException::withMessages([$field => 'گزینه انتخاب‌شده برای این محصول معتبر نیست.']);
        return $value;
    }

    public function update(CartItem $item, int $quantity): Cart
    {
        $cart = $this->current();
        abort_unless($cart && (int) $item->cart_id === (int) $cart->id, 404);
        if ($quantity < 1) $item->delete(); else $item->update(['quantity' => min(99, $quantity)]);
        return $cart->fresh()->load('items.product.category');
    }

    public function remove(CartItem $item): void
    {
        $cart = $this->current();
        abort_unless($cart && (int) $item->cart_id === (int) $cart->id, 404);
        $item->delete();
    }

    public function importLegacy(array $rows): int
    {
        $skipped = 0;
        foreach (array_slice($rows, 0, 100) as $row) {
            $legacyId = (string) ($row['id'] ?? '');
            $legacyMap = ['daily-sneakers' => 'everyday-sneaker', 'soft-sweatshirt' => 'ribbed-top'];
            $product = Product::query()->active()->where('slug', $legacyMap[$legacyId] ?? $legacyId)->first();
            if (!$product) { $skipped++; continue; }
            $size = $this->allowedChoice($row['size'] ?? null, $product->sizes, 'size');
            $color = $this->allowedChoice($row['color'] ?? null, $product->colors, 'color');
            $lineHash = hash('sha256', $product->id.'|'.($size ?? '').'|'.($color ?? ''));
            $cart = $this->current(true);
            CartItem::firstOrCreate(['cart_id' => $cart->id, 'line_hash' => $lineHash], [
                'product_id' => $product->id, 'selected_size' => $size, 'selected_color' => $color,
                'quantity' => max(1, min(99, (int) ($row['quantity'] ?? 1))),
            ]);
        }
        return $skipped;
    }

    public function mergeGuestIntoUser(): void
    {
        $hash = $this->guest->hash();
        $user = auth()->user();
        if (!$hash || !$user) return;
        DB::transaction(function () use ($hash, $user) {
            $guest = Cart::query()->where('guest_token_hash', $hash)->lockForUpdate()->first();
            if (!$guest) return;
            $target = Cart::query()->where('user_id', $user->id)->lockForUpdate()->first();
            if (!$target) {
                $guest->update(['user_id' => $user->id, 'guest_token_hash' => null]);
                return;
            }
            foreach ($guest->items as $line) {
                $existing = $target->items()->where('line_hash', $line->line_hash)->first();
                if ($existing) $existing->update(['quantity' => min(99, $existing->quantity + $line->quantity)]);
                else $target->items()->create($line->only(['product_id','line_hash','selected_size','selected_color','quantity']));
            }
            $guest->delete();
        });
    }
}
