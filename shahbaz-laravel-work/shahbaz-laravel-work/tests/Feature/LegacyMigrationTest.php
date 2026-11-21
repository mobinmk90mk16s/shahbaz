<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\GuestProfile;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LegacyMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_legacy_browser_data_is_persisted_idempotently_under_one_guest_identity(): void
    {
        $payload = [
            'cart' => [['id' => 'linen-shirt', 'quantity' => 2, 'size' => 'M', 'color' => 'عاجی']],
            'wishlist' => ['classic-polo'],
            'profile' => ['name' => 'کاربر مهمان', 'phone' => '۰۹۱۲۳۴۵۶۷۸۹', 'email' => 'guest@example.test'],
        ];

        $guestToken = Str::random(64);
        $this->withCookie('shahbaz_guest', $guestToken)
            ->postJson(route('guest.migrate'), $payload)->assertOk()->assertJsonPath('ok', true);
        $this->assertSame(1, Cart::count());
        $this->assertSame(1, CartItem::count());
        $this->assertSame(1, Wishlist::count());
        $this->assertSame(1, WishlistItem::count());
        $this->assertSame(1, GuestProfile::count());

        $cartHash = Cart::firstOrFail()->guest_token_hash;
        $this->assertSame($cartHash, Wishlist::firstOrFail()->guest_token_hash);
        $this->assertSame($cartHash, GuestProfile::firstOrFail()->guest_token_hash);
        $this->assertDatabaseHas('guest_profiles', ['mobile' => '09123456789']);

        $this->withCookie('shahbaz_guest', $guestToken)
            ->postJson(route('guest.migrate'), $payload)->assertOk()->assertJsonPath('ok', true);
        $this->assertSame(1, CartItem::count());
        $this->assertSame(1, WishlistItem::count());
    }
}
