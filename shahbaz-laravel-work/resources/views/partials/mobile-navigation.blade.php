<nav class="mobile-bottom-nav" aria-label="دسترسی سریع موبایل">
    <a class="mobile-nav-item @if(request()->routeIs('wishlist.*')) is-active @endif" href="{{ route('wishlist.index') }}" aria-label="علاقه‌مندی‌ها"><span class="mobile-nav-icon-wrap"><x-icon name="heart" /><b class="mobile-nav-count">{{ $wishlistCount ?? 0 }}</b></span><span>علاقه‌مندی</span></a>
    <a class="mobile-nav-item @if(request()->routeIs('cart.*')) is-active @endif" href="{{ route('cart.index') }}" aria-label="سبد خرید"><span class="mobile-nav-icon-wrap"><x-icon name="bag" /><b class="mobile-nav-count">{{ $cartCount ?? 0 }}</b></span><span>سبد خرید</span></a>
    <a class="mobile-nav-item mobile-nav-home @if(request()->routeIs('storefront.home')) is-active @endif" href="{{ route('storefront.home') }}" aria-label="خانه"><x-icon name="home" /><span>خانه</span></a>
    <a class="mobile-nav-item @if(request()->routeIs('account.*')) is-active @endif" href="{{ route('account.index') }}" aria-label="تنظیمات حساب"><x-icon name="settings" /><span>تنظیمات</span></a>
    <button class="mobile-nav-item mobile-nav-menu" type="button" data-mobile-menu-open aria-label="باز کردن منو"><x-icon name="menu" /><span>منو</span></button>
</nav>
