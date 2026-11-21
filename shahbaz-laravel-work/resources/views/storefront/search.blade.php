@extends('layouts.storefront')
@section('title', 'جست‌وجوی محصولات | شهباز')
@section('page-type', 'search')
@section('content')
<section class="listing-section container section-space">
    <div class="section-heading"><span class="section-kicker">جست‌وجوی شهباز</span><h1>محصول موردنظرت را پیدا کن</h1><p>در نام، توضیحات و دسته‌بندی محصولات جست‌وجو کن.</p></div>
    <form class="search-page-form" method="GET" action="{{ route('storefront.search') }}">
        <label class="visually-hidden" for="product-query">جست‌وجوی محصول</label><input id="product-query" name="q" type="search" value="{{ $term }}" placeholder="مثلاً پیراهن، کیف یا کفش...">
        <label><input type="checkbox" name="sale" value="1" @checked($saleOnly)> فقط تخفیف‌دارها</label><label><input type="checkbox" name="remaining" value="1" @checked($remainingOnly)> کالکشن محدود</label>
        <label for="search-sort">مرتب‌سازی</label><select name="sort" id="search-sort"><option value="featured" @selected($sort === 'featured')>پیشنهادی</option><option value="price-asc" @selected($sort === 'price-asc')>ارزان‌ترین</option><option value="price-desc" @selected($sort === 'price-desc')>گران‌ترین</option><option value="discount" @selected($sort === 'discount')>بیشترین تخفیف</option></select><button class="button button-dark" type="submit">جست‌وجو</button>
    </form>
    <div class="section-heading section-heading-row"><div><h2>نتایج جست‌وجو</h2><p>{{ $products->total() }} نتیجه</p></div></div>
    @if($products->count())<div class="product-grid">@foreach($products as $product)@include('partials.product-card', ['product' => $product, 'savedProductIds' => $savedProductIds ?? []])@endforeach</div>@else<div class="empty-state large-empty-state"><div class="empty-state-icon"><x-icon name="search" /></div><h2>نتیجه‌ای پیدا نشد</h2><p>عبارت دیگری را امتحان کن یا فیلترها را پاک کن.</p><a class="button button-light" href="{{ route('storefront.search') }}">پاک کردن فیلترها</a></div>@endif
    <div class="pagination-wrap">{{ $products->links('vendor.pagination.shahbaz') }}</div>
</section>
@endsection
