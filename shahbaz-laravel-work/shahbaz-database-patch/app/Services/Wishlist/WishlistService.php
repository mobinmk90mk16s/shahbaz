<?php

namespace App\Services\Wishlist;

use App\Models\Product;
use App\Models\Wishlist;
use App\Services\Cart\GuestIdentity;
use Illuminate\Support\Facades\DB;

class WishlistService
{
    public function __construct(private readonly GuestIdentity $guest) {}
    public function current(bool $create = false): ?Wishlist
    {
        if ($user = auth()->user()) {
            if (!$create) return Wishlist::query()->where('user_id', $user->id)->with('items.category')->first();
            return Wishlist::firstOrCreate(['user_id' => $user->id], ['guest_token_hash' => null])->load('items.category');
        }
        $hash = $this->guest->hash($create);
        if (!$hash) return null;
        $wishlist = Wishlist::query()->where('guest_token_hash', $hash)->with('items.category')->first();
        if (!$wishlist && $create) $wishlist = Wishlist::create(['guest_token_hash' => $hash]);
        return $wishlist?->load('items.category');
    }
    public function add(Product $product): void { $this->current(true)->items()->syncWithoutDetaching([$product->id]); }
    public function remove(Product $product): void { $this->current()?->items()->detach($product->id); }
    public function toggle(Product $product): bool
    {
        $wishlist = $this->current(true);
        if ($wishlist->items()->whereKey($product->id)->exists()) { $wishlist->items()->detach($product->id); return false; }
        $wishlist->items()->syncWithoutDetaching([$product->id]); return true;
    }
    public function importLegacy(array $ids): int
    {
        $skipped = 0;
        foreach (array_slice(array_unique($ids), 0, 200) as $id) {
            $legacyMap = ['daily-sneakers' => 'everyday-sneaker', 'soft-sweatshirt' => 'ribbed-top'];
            $product = Product::query()->active()->where('slug', $legacyMap[(string) $id] ?? $id)->first();
            if ($product) $this->add($product);
            else $skipped++;
        }
        return $skipped;
    }
    public function mergeGuestIntoUser(): void
    {
        $hash = $this->guest->hash(); $user = auth()->user();
        if (!$hash || !$user) return;
        DB::transaction(function () use ($hash, $user) {
            $guest = Wishlist::query()->where('guest_token_hash', $hash)->lockForUpdate()->first();
            if (!$guest) return;
            $target = Wishlist::query()->where('user_id', $user->id)->lockForUpdate()->first();
            if (!$target) { $guest->update(['user_id' => $user->id, 'guest_token_hash' => null]); return; }
            $target->items()->syncWithoutDetaching($guest->items()->pluck('products.id')->all());
            $guest->delete();
        });
    }
}
