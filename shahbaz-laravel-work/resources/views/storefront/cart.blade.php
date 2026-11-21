@extends('layouts.storefront')
@section('title', 'سبد خرید | شهباز')
@section('page-type', 'cart')
@section('content')
<section class="container section-space cart-page-items">
    <div class="section-heading"><span class="section-kicker">انتخاب‌های تو</span><h1>سبد خرید</h1><p>محصولات انتخاب‌شده در این بخش نگهداری می‌شوند.</p></div>
    @if($cart && $cart->items->isNotEmpty())
        <div class="cart-page-layout"><div class="cart-items">
            @foreach($cart->items as $item)
                @if($item->product && $item->product->is_active && $item->product->category?->is_active)
                <article class="cart-page-row"><a class="cart-page-image" href="{{ route('storefront.products.show', $item->product->slug) }}"><img src="{{ asset($item->product->image_path) }}" alt="{{ $item->product->name }}" loading="lazy"></a><div class="cart-page-info"><h3><a href="{{ route('storefront.products.show', $item->product->slug) }}">{{ $item->product->name }}</a></h3><span>{{ $item->selected_size ? 'سایز '.$item->selected_size : '' }} {{ $item->selected_color ? ' · '.$item->selected_color : '' }}</span><strong>{{ number_format($item->product->price) }} تومان</strong><form action="{{ route('cart.items.update',$item) }}" method="POST" class="cart-quantity-form">@csrf @method('PATCH')<label class="visually-hidden" for="qty-{{ $item->id }}">تعداد {{ $item->product->name }}</label><input id="qty-{{ $item->id }}" type="number" name="quantity" min="1" max="99" value="{{ $item->quantity }}"><button type="submit" class="button button-light">به‌روزرسانی</button></form></div><form action="{{ route('cart.items.destroy',$item) }}" method="POST">@csrf @method('DELETE')<button class="icon-button" type="submit" aria-label="حذف {{ $item->product->name }}"><x-icon name="trash" /></button></form></article>
                @else
                    <article class="cart-page-row cart-unavailable-row"><div class="cart-page-info"><strong>این محصول دیگر در دسترس نیست</strong><span>برای اصلاح سبد، این ردیف را حذف کن.</span></div><form action="{{ route('cart.items.destroy',$item) }}" method="POST">@csrf @method('DELETE')<button class="icon-button" type="submit" aria-label="حذف محصول ناموجود"><x-icon name="trash" /></button></form></article>
                @endif
            @endforeach
        </div><aside class="cart-subtotal"><h2>خلاصه سبد</h2><div><span>تعداد اقلام</span><strong>{{ $cart->quantity }}</strong></div><div><span>جمع کالاها</span><strong>{{ number_format($cart->subtotal) }} تومان</strong></div><p>اتصال به ثبت سفارش، محاسبه ارسال و درگاه پرداخت هنوز در این مرحله فعال نشده است.</p><button class="button button-dark button-full" type="button" disabled>ثبت سفارش (به‌زودی)</button><a class="button button-light button-full" href="{{ route('storefront.home') }}">ادامه خرید</a></aside></div>
    @else
        <div class="empty-state large-empty-state"><div class="empty-state-icon"><x-icon name="bag" /></div><h2>سبد خریدت خالی است</h2><p>با دیدن محصولات شهباز، اولین انتخابت را به سبد اضافه کن.</p><a class="button button-dark" href="{{ route('storefront.home') }}">مشاهده محصولات</a></div>
    @endif
</section>
@endsection
