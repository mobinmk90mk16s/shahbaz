@extends('layouts.storefront')
@section('title', $product->name.' | شهباز')
@section('description', $product->description)
@section('page-type', 'product')
@section('content')
<section class="container section-space">
    <nav class="breadcrumb"><a href="{{ route('storefront.home') }}">خانه</a><span>/</span><a href="{{ route('storefront.categories.'.$product->category->slug) }}">{{ $product->category->name }}</a><span>/</span><span>{{ $product->name }}</span></nav>
    <div class="product-detail" data-product-detail>
        <div class="product-detail-image product-image-editorial"><img src="{{ asset($product->image_path) }}" alt="{{ $product->image_alt ?: $product->name }}"><span class="product-image-index">SHAHBAZ / {{ str_pad((string)$product->id,2,'0',STR_PAD_LEFT) }}</span><span class="product-image-caption">جزئیات یک انتخاب خوب</span></div>
        <div class="product-detail-copy">
            <div class="product-detail-meta"><span class="eyebrow">{{ $product->category->name }}</span><span>کد محصول: {{ strtoupper($product->slug) }}</span></div>
            <h1>{{ $product->name }}</h1>
            <div class="detail-price"><strong>{{ number_format($product->price) }} تومان</strong>@if($product->compare_at_price)<del>{{ number_format($product->compare_at_price) }} تومان</del><span>{{ $product->discount_percent }}٪ تخفیف</span>@endif</div>
            <p class="product-detail-description">{{ $product->description }}</p>
            <form action="{{ route('cart.items.store') }}" method="POST" class="product-buy-form" data-product-buy-form>
                @csrf<input type="hidden" name="product_id" value="{{ $product->id }}">
                @if(!empty($product->sizes))<fieldset class="detail-option choice-field"><legend>انتخاب سایز</legend><div class="size-option-head"><span>سایز مناسب خودت را انتخاب کن</span><button class="size-guide-trigger" type="button" data-size-guide-open>راهنمای انتخاب سایز <span>↗</span></button></div><div class="detail-size-options">@foreach($product->sizes as $index => $size)<label class="detail-size-choice"><input type="radio" name="size" value="{{ $size }}" @checked($index === 0) @if($index === 0) required @endif><span>{{ $size }}</span></label>@endforeach</div></fieldset>@endif
                @if(!empty($product->colors))<fieldset class="detail-option choice-field"><legend>انتخاب رنگ</legend><div class="color-options">@foreach($product->colors as $index => $color)<label class="color-chip"><input type="radio" name="color" value="{{ $color }}" @checked($index === 0)><span>{{ $color }}</span></label>@endforeach</div></fieldset>@endif
                <label class="quantity-field">تعداد <input type="number" name="quantity" value="1" min="1" max="99"></label>
                <button class="button button-dark button-full add-to-cart-button" type="submit">افزودن به سبد خرید <x-icon name="bag" /></button>
            </form>
            <form action="{{ route('wishlist.store', $product->slug) }}" method="POST" class="detail-wishlist">@csrf<button class="button button-light button-full" type="submit"><x-icon name="heart" /> افزودن / حذف از علاقه‌مندی‌ها</button></form>
            <details class="product-info-accordion"><summary>مشخصات انتخاب</summary><div><p>رنگ‌های موجود: {{ implode('، ', $product->colors ?? []) ?: 'ثبت نشده' }}</p><p>سایزبندی: {{ implode('، ', $product->sizes ?? []) ?: 'ثبت نشده' }}</p></div></details>
            <details class="product-info-accordion"><summary>درباره این محصول</summary><div><p>{{ $product->description }}</p><p>مشخصات این نسخه از کاتالوگ نمونه فعلی شهباز نمایش داده می‌شود.</p></div></details>
            <div class="product-detail-description"><strong>توجه</strong><p>قیمت‌ها و مشخصات این نسخه از کاتالوگ نمونه می‌آیند. موجودی انبار و فرایند سفارش/پرداخت هنوز به سامانه متصل نشده‌اند.</p></div>
        </div>
    </div>
</section>
<div class="product-mobile-buybar"><div><span>قیمت محصول</span><strong>{{ number_format($product->price) }} تومان</strong></div><button class="button button-dark" type="button" data-mobile-add>افزودن به سبد <x-icon name="bag" /></button></div>
<div class="size-guide-modal" data-size-guide hidden>
    <section class="size-guide-panel" role="dialog" aria-modal="true" aria-labelledby="size-guide-title">
        <header class="size-guide-head"><div><span class="eyebrow">راهنمای همراه انتخاب</span><h2 id="size-guide-title">سایز مناسب خودت را پیدا کن</h2><p>چند نکته ساده برای انتخاب مطمئن‌تر</p></div><button type="button" class="icon-button" data-size-guide-close aria-label="بستن راهنمای سایز">×</button></header>
        <div class="size-guide-content">
            @if($product->category->slug === 'bags-shoes')
                <div class="size-guide-steps"><article><b>۰۱</b><strong>طول پا را اندازه بگیر</strong><p>پاشنه را به دیوار بچسبان و فاصله تا نوک بلندترین انگشت را اندازه بگیر.</p></article><article><b>۰۲</b><strong>هر دو پا را بررسی کن</strong><p>اندازه هر دو پا را بگیر و برای انتخاب، اندازه پای بزرگ‌تر را در نظر بگیر.</p></article><article><b>۰۳</b><strong>فضای راحتی را در نظر بگیر</strong><p>فرم کفش و نوع جورابی که می‌پوشی روی حس نهایی سایز اثر می‌گذارد.</p></article></div>
            @elseif($product->category->slug === 'kids')
                <div class="size-guide-steps"><article><b>۰۱</b><strong>سن و قد کودک</strong><p>بازه درج‌شده کنار سایز را با سن و قد فعلی کودک مقایسه کن.</p></article><article><b>۰۲</b><strong>فضای حرکت</strong><p>برای لباس روزمره، آزادی حرکت را در نظر بگیر و از انتخاب بیش از حد جذب پرهیز کن.</p></article><article><b>۰۳</b><strong>بین دو سایز</strong><p>اگر بین دو بازه قرار دارد، ابعاد رسمی محصول را بررسی کن.</p></article></div>
            @else
                <div class="size-guide-steps"><article><b>۰۱</b><strong>دور سینه</strong><p>متر را از برجسته‌ترین قسمت سینه عبور بده و موازی سطح زمین نگه دار.</p></article><article><b>۰۲</b><strong>دور کمر</strong><p>باریک‌ترین قسمت کمر را بدون سفت‌کردن متر اندازه بگیر.</p></article><article><b>۰۳</b><strong>دور باسن</strong><p>متر را از برجسته‌ترین قسمت باسن عبور بده تا اندازه دقیق‌تری داشته باشی.</p></article></div>
            @endif
            <p class="size-guide-note">جدول اندازه‌های عددی این محصول در کاتالوگ نمونه ثبت نشده است؛ برای انتشار نهایی، اندازه‌های تأییدشده تولیدکننده باید به این بخش اضافه شوند.</p>
        </div><footer><button type="button" class="button button-dark" data-size-guide-close>متوجه شدم</button></footer>
    </section>
</div>
@if($relatedProducts->isNotEmpty())<section class="related-section container section-space"><div class="section-heading"><span class="section-kicker">پیشنهاد شهباز</span><h2>محصولات مشابه</h2></div><div class="product-grid">@foreach($relatedProducts as $related)@include('partials.product-card', ['product' => $related, 'savedProductIds' => $savedProductIds ?? []])@endforeach</div></section>@endif
@endsection
