<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#FAFAF7">
    <meta name="description" content="@yield('description', 'دنیای پوشاک شهباز؛ انتخابی برای هر سلیقه و هر سبک زندگی.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="index,follow">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:site_name" content="شهباز">
    <meta property="og:title" content="@yield('title', 'شهباز | فروشگاه اینترنتی پوشاک')">
    <meta property="og:description" content="@yield('description', 'دنیای پوشاک شهباز؛ انتخابی برای هر سلیقه و هر سبک زندگی.')">
    <meta property="og:image" content="{{ asset('assets/images/sliders/men.webp') }}">
    <title>@yield('title', 'شهباز | فروشگاه اینترنتی پوشاک')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" as="style">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/storefront-mvc.css') }}">
    @stack('head')
</head>
<body data-page="@yield('page-type', 'home')" data-root="{{ rtrim(url('/'), '/') }}/" data-guest-migration-url="{{ route('guest.migrate') }}" data-search-suggest-url="{{ route('storefront.search.suggest') }}">
    <a class="skip-link" href="#page-main">رفتن به محتوای اصلی</a>
    @include('partials.header')

    @if (session('success'))
        <div class="container" role="status"><div class="account-notice"><span><x-icon name="check" /></span><div><strong>{{ session('success') }}</strong></div></div></div>
    @endif
    @if ($errors->any())
        <div class="container" role="alert"><div class="account-notice"><span><x-icon name="shield" /></span><div><strong>لطفاً موارد زیر را بررسی کن.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div></div>
    @endif

    <main id="page-main" tabindex="-1">
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.mobile-navigation')
    <div id="site-overlays"></div>

    @if (request()->routeIs('storefront.home'))
        <script src="{{ asset('assets/js/home-slider.js') }}" defer></script>
    @endif
    <script src="{{ asset('assets/js/storefront-ui.js') }}" defer></script>
    <script src="{{ asset('assets/js/live-search.js') }}" defer></script>
    @if (request()->routeIs('storefront.categories.*'))
        <script src="{{ asset('assets/js/category-filters.js') }}" defer></script>
    @endif
    @if (request()->routeIs('storefront.products.show'))
        <script src="{{ asset('assets/js/product-experience.js') }}" defer></script>
    @endif
    <script src="{{ asset('assets/js/legacy-migration.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
