@extends('layouts.storefront')
@section('title', 'شهباز | فروشگاه اینترنتی پوشاک')
@section('description', 'دنیای پوشاک شهباز؛ انتخابی برای هر سلیقه و هر سبک زندگی.')
@section('page-type', 'home')
@section('content')
<section class="hero-section hero-slider" data-hero-slider aria-label="معرفی کالکشن‌های شهباز">
    <div class="hero-slider-stage">
        @foreach ($categories as $index => $category)
            <article class="hero-slide {{ $index === 0 ? 'is-active' : '' }}" data-hero-slide aria-hidden="{{ $index === 0 ? 'false' : 'true' }}">
                <img class="hero-image" src="{{ asset($category->card_image_path ?: $category->image_path) }}" alt="{{ $category->name }}" @if($index > 0) loading="lazy" @endif>
                <div class="hero-slide-shade"></div>
                <div class="hero-content hero-slide-copy">
                    <span class="hero-slide-eyebrow">کالکشن شهباز · {{ str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <h2>{{ $category->name }}</h2>
                    <p>{{ $category->description }}</p>
                    <a class="button button-light hero-slide-cta" href="{{ route('storefront.categories.'.$category->slug) }}">مشاهده کالکشن <x-icon name="arrow" /></a>
                </div>
            </article>
        @endforeach
    </div>
    <div class="hero-slider-controls"><button class="icon-button hero-slider-arrow hero-slider-prev" type="button" data-hero-prev aria-label="اسلاید قبلی"><x-icon name="arrow" /></button><div class="hero-slider-dots">@foreach($categories as $index => $category)<button type="button" data-hero-dot="{{ $index }}" class="{{ $index === 0 ? 'is-active' : '' }}" aria-label="نمایش اسلاید {{ $index + 1 }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"></button>@endforeach</div><button class="icon-button hero-slider-arrow hero-slider-next" type="button" data-hero-next aria-label="اسلاید بعدی"><x-icon name="arrow" /></button></div>
    <div class="hero-note"><span>انتخابی برای سبک تو</span><i></i><span>SHAHBAZ</span></div>
</section>

<section class="categories-section container section-space">
    <div class="section-heading"><span class="section-kicker">دسته‌بندی‌ها</span><h2>برای هر سلیقه، یک انتخاب</h2><p>از پوشاک روزمره تا جزئیات تکمیل‌کننده استایل</p></div>
    <div class="category-grid">
        @foreach ($categories as $category)
            <a class="category-card category-{{ $category->slug }}" href="{{ route('storefront.categories.'.$category->slug) }}">
                <img src="{{ asset($category->tile_image_path ?: $category->image_path) }}" alt="{{ $category->name }}" loading="lazy" decoding="async">
                <span class="category-overlay" aria-hidden="true"></span>
                <span class="category-content"><strong>{{ $category->name }}</strong><b><x-icon name="arrow" /></b></span>
            </a>
        @endforeach
    </div>
</section>

<section class="listing-section container section-space">
    <div class="section-heading section-heading-row"><div><span class="section-kicker">پیشنهاد شهباز</span><h2>انتخاب‌های محبوب</h2><p>چند پیشنهاد برای شروع یک استایل تازه</p></div><a class="text-link" href="{{ route('storefront.search') }}">مشاهده همه محصولات <x-icon name="arrow" /></a></div>
    <div class="product-grid">
        @forelse ($featuredProducts as $product)
            @include('partials.product-card', ['product' => $product, 'savedProductIds' => $savedProductIds ?? []])
        @empty
            <p class="empty-state">هنوز محصولی برای نمایش ثبت نشده است.</p>
        @endforelse
    </div>
</section>

<section class="brand-story-section" id="about">
    <div class="brand-story-inner container">
        <div class="brand-story-image"><img src="{{ asset('assets/images/editorial/collection-editorial.webp') }}" alt="مجموعه پوشاک شهباز" loading="lazy"></div>
        <div class="brand-story-copy"><span class="section-kicker">داستان شهباز</span><h2>سادگی، کیفیت و انتخاب آگاهانه</h2><p>در شهباز تلاش می‌کنیم انتخاب پوشاک را ساده‌تر کنیم؛ محصولاتی با فرم کاربردی و طراحی متعادل برای روزهای واقعی زندگی.</p><a class="button button-dark" href="{{ route('storefront.search') }}">شروع گشت‌وگذار <x-icon name="arrow" /></a></div>
    </div>
</section>

@if ($limitedProducts->isNotEmpty())
<section class="last-drops-section">
    <div class="last-drops-layout">
        <div class="last-drops-intro"><span class="last-drops-kicker">COLLECTED FOR YOU</span><h2>تک‌مانده‌ها</h2><p>تعدادی از انتخاب‌های محدود فروشگاه؛ برای دیدن جزئیات هر محصول وارد صفحه آن شو.</p><a class="last-drops-all" href="{{ route('storefront.search', ['remaining' => 1]) }}">مشاهده همه <x-icon name="arrow" /></a><small>موجودی در این مرحله صرفاً اطلاعات کاتالوگ نمونه است.</small></div>
        <div class="last-drops-rail-shell"><div class="product-grid">
            @foreach ($limitedProducts as $product)
                @include('partials.product-card', ['product' => $product, 'savedProductIds' => $savedProductIds ?? [], 'modifier' => 'last-drops-product', 'tagOverride' => 'کالکشن محدود'])
            @endforeach
        </div></div>
    </div>
</section>
@endif

<section class="trust-section container section-space"><div class="section-heading"><span class="section-kicker">تجربه خرید</span><h2>انتخابی ساده و مطمئن</h2></div><div class="about-features"><div class="about-feature"><strong>انتخاب شفاف</strong><p>جزئیات هر محصول پیش از افزودن به سبد قابل مشاهده است.</p></div><div class="about-feature"><strong>حساب کاربری</strong><p>اطلاعات حساب و فهرست انتخاب‌ها در فضای امن ذخیره می‌شوند.</p></div><div class="about-feature"><strong>پشتیبانی</strong><p>برای راهنمایی درباره محصولات با ما در تماس باش.</p></div></div></section>
@endsection
