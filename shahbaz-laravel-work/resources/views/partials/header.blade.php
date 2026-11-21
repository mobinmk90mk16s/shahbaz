<div class="announcement-bar"><span>انتخاب‌های خوش‌سلیقه، برای هر روز</span><i></i><span>به دنیای شهباز خوش آمدی</span></div>
<header class="site-header" id="top">
    <div class="header-inner container">
        <button class="icon-button mobile-menu-trigger" type="button" data-mobile-menu-open aria-label="باز کردن منو"><x-icon name="menu" /></button>
        <a class="brand" href="{{ route('storefront.home') }}" aria-label="صفحه اصلی شهباز">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 44 44" fill="none"><path d="M7 29.5C13.3 28.7 18.7 24.6 23.1 17.2C25.1 13.8 28.4 10.9 33.4 9.2C32.8 14.5 30.5 19.1 26.5 22.9C22.1 27.1 15.9 29.5 7 29.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M10.2 34.2C17.4 33.8 24.6 31.3 30.2 26.6C33.2 24.1 35.4 21 37 17.4M7 29.5l3.2 4.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
            <span class="brand-copy"><strong>شهباز</strong><small>انتخابی برای سبک تو</small></span>
        </a>
        <nav class="main-nav" aria-label="منوی اصلی">
            <a class="nav-link @if(request()->routeIs('storefront.home')) is-active @endif" href="{{ route('storefront.home') }}">خانه</a>
            <a class="nav-link @if(request()->routeIs('storefront.categories.men')) is-active @endif" href="{{ route('storefront.categories.men') }}">مردانه</a>
            <a class="nav-link @if(request()->routeIs('storefront.categories.women')) is-active @endif" href="{{ route('storefront.categories.women') }}">زنانه</a>
            <a class="nav-link @if(request()->routeIs('storefront.categories.kids')) is-active @endif" href="{{ route('storefront.categories.kids') }}">بچگانه</a>
            <a class="nav-link @if(request()->routeIs('storefront.categories.bags-shoes')) is-active @endif" href="{{ route('storefront.categories.bags-shoes') }}">کیف و کفش</a>
        </nav>
        <div class="header-tools">
            <form id="search-form" class="search-form" action="{{ route('storefront.search') }}" method="GET" role="search">
                <label class="visually-hidden" for="site-search">جست‌وجوی محصولات</label>
                <input id="site-search" name="q" type="search" value="{{ request('q') }}" placeholder="جست‌وجو در شهباز..." autocomplete="off">
                <button type="submit" aria-label="جست‌وجو"><x-icon name="search" /></button>
            </form>
            <a class="header-action" href="{{ route('wishlist.index') }}" aria-label="علاقه‌مندی‌ها"><span class="action-icon-wrap"><x-icon name="heart" /><b class="tiny-count">{{ $wishlistCount ?? 0 }}</b></span><span>علاقه‌مندی‌ها</span></a>
            <a class="header-action" href="{{ route('account.index') }}" aria-label="حساب کاربری"><x-icon name="user" /><span>حساب کاربری</span></a>
            <a class="header-action" href="{{ route('cart.index') }}" aria-label="سبد خرید"><span class="action-icon-wrap"><x-icon name="bag" /><b class="cart-count">{{ $cartCount ?? 0 }}</b></span><span>سبد خرید</span></a>
        </div>
        <a class="icon-button mobile-cart-trigger" href="{{ route('cart.index') }}" aria-label="سبد خرید"><x-icon name="bag" /><b class="cart-count">{{ $cartCount ?? 0 }}</b></a>
    </div>
</header>
<div class="mobile-menu-overlay" data-mobile-menu-close hidden></div>
<aside class="mobile-menu" data-mobile-menu aria-hidden="true" aria-label="منوی موبایل">
    <div class="mobile-menu-head"><a class="brand" href="{{ route('storefront.home') }}"><span class="brand-copy"><strong>شهباز</strong><small>انتخابی برای سبک تو</small></span></a><button class="icon-button" data-mobile-menu-close type="button" aria-label="بستن منو"><x-icon name="close" /></button></div>
    <nav class="mobile-nav-links">
        <a href="{{ route('storefront.home') }}">خانه <x-icon name="arrow" /></a>
        <a href="{{ route('storefront.categories.men') }}">مردانه <x-icon name="arrow" /></a>
        <a href="{{ route('storefront.categories.women') }}">زنانه <x-icon name="arrow" /></a>
        <a href="{{ route('storefront.categories.kids') }}">بچگانه <x-icon name="arrow" /></a>
        <a href="{{ route('storefront.categories.bags-shoes') }}">کیف و کفش <x-icon name="arrow" /></a>
        <a href="{{ route('account.index') }}">حساب کاربری <x-icon name="user" /></a>
        <a href="{{ route('wishlist.index') }}">علاقه‌مندی‌ها <x-icon name="heart" /></a>
    </nav>
    <p class="mobile-menu-note">سادگی، کیفیت و انتخاب آگاهانه</p>
</aside>
