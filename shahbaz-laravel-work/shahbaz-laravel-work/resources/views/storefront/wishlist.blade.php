@extends('layouts.storefront')
@section('title', 'علاقه‌مندی‌های من | شهباز')
@section('page-type', 'wishlist')
@section('content')
<section class="listing-section container section-space"><div class="section-heading"><span class="section-kicker">انتخاب‌های ذخیره‌شده</span><h1>علاقه‌مندی‌های من</h1><p>محصولاتی که برای بعد نگه داشته‌ای.</p></div>
@if($products->isNotEmpty())<div class="product-grid">@foreach($products as $product)@include('partials.product-card', ['product'=>$product,'savedProductIds'=>$savedProductIds ?? []])@endforeach</div>@else<div class="empty-state large-empty-state"><div class="empty-state-icon"><x-icon name="heart" /></div><h2>هنوز محصولی ذخیره نکرده‌ای</h2><p>روی قلب هر محصول بزن تا اینجا نگه داشته شود.</p><a class="button button-dark" href="{{ route('storefront.home') }}">دیدن محصولات</a></div>@endif
</section>
@endsection
