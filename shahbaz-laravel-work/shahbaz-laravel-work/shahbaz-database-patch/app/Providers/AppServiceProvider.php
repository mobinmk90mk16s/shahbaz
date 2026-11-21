<?php

namespace App\Providers;

use App\Contracts\SmsGateway;
use App\Services\Cart\GuestIdentity;
use App\Services\Sms\HttpSmsGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(GuestIdentity::class);
        $this->app->bind(SmsGateway::class, HttpSmsGateway::class);
    }

    public function boot(): void
    {
        RateLimiter::for('otp-send', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));
        RateLimiter::for('otp-verify', fn (Request $request) => Limit::perMinute(6)->by($request->ip()));

        View::composer('layouts.storefront', function ($view) {
            $cart = app(\App\Services\Cart\CartService::class)->current();
            $wishlist = app(\App\Services\Wishlist\WishlistService::class)->current();
            $view->with([
                'cartCount' => $cart ? $cart->quantity : 0,
                'wishlistCount' => $wishlist ? $wishlist->items->count() : 0,
            ]);
        });
    }
}
