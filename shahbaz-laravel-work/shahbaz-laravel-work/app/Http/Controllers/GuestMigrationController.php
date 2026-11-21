<?php

namespace App\Http\Controllers;

use App\Models\GuestProfile;
use App\Services\Cart\GuestIdentity;
use App\Services\Cart\CartService;
use App\Services\Wishlist\WishlistService;
use App\Support\PhoneNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestMigrationController extends Controller
{
    public function __construct(private readonly GuestIdentity $guest, private readonly CartService $carts, private readonly WishlistService $wishlists) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cart' => ['sometimes','array','max:100'], 'cart.*.id' => ['required_with:cart','string','max:120'],
            'cart.*.quantity' => ['nullable','integer','min:1','max:99'], 'cart.*.size' => ['nullable','string','max:40'], 'cart.*.color' => ['nullable','string','max:60'],
            'wishlist' => ['sometimes','array','max:200'], 'wishlist.*' => ['string','max:120'],
            'profile' => ['sometimes','array'], 'profile.name' => ['nullable','string','max:120'],
            'profile.phone' => ['nullable','string','max:32'], 'profile.email' => ['nullable','email','max:254'],
        ]);
        $skipped = 0;
        DB::transaction(function () use ($data, &$skipped) {
            if (!empty($data['cart'])) $skipped += $this->carts->importLegacy($data['cart']);
            if (!empty($data['wishlist'])) $skipped += $this->wishlists->importLegacy($data['wishlist']);
            if (!empty($data['profile'])) {
                $hash = $this->guest->hash(true);
                $profile = $data['profile'];
                $mobile = !empty($profile['phone']) ? PhoneNormalizer::normalize($profile['phone']) : null;
                GuestProfile::updateOrCreate(['guest_token_hash'=>$hash], [
                    'name'=>$profile['name'] ?? null, 'mobile'=>$mobile, 'email'=>$profile['email'] ?? null, 'expires_at'=>now()->addDays(30),
                ]);
            }
        });
        return response()->json([
            'ok' => $skipped === 0,
            'skipped' => $skipped,
            'message' => $skipped === 0
                ? 'داده‌های قبلی با موفقیت منتقل شدند.'
                : 'برخی شناسه‌های قدیمی در کاتالوگ فعلی پیدا نشدند؛ داده‌های مرورگر حذف نشدند.',
        ]);
    }
}
