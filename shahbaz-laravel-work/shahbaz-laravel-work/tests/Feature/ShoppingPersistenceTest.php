<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Wishlist;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_cart_is_persisted_with_a_hashed_guest_identity(): void
    {
        $product = Product::where('slug', 'linen-shirt')->firstOrFail();
        $response = $this->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2]);
        $response->assertRedirect();
        $this->assertDatabaseHas('carts', ['user_id' => null]);
        $cart = Cart::whereNotNull('guest_token_hash')->firstOrFail();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $cart->guest_token_hash);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_guest_wishlist_is_persisted_in_database(): void
    {
        $product = Product::where('slug', 'linen-shirt')->firstOrFail();
        $this->post(route('wishlist.store', $product->slug))->assertRedirect();
        $wishlist = Wishlist::whereNotNull('guest_token_hash')->firstOrFail();
        $this->assertDatabaseHas('wishlist_items', ['wishlist_id' => $wishlist->id, 'product_id' => $product->id]);
    }

    public function test_cart_rejects_product_choice_not_in_catalog(): void
    {
        $product = Product::where('slug', 'linen-shirt')->firstOrFail();
        $this->from(route('storefront.products.show', $product->slug))
            ->post(route('cart.items.store'), ['product_id' => $product->id, 'size' => 'INVALID'])
            ->assertSessionHasErrors('size');
        $this->assertSame(0, CartItem::count());
    }
}
