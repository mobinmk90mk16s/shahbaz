@extends('layouts.storefront')
@section('title', $category->name.' | شهباز')
@section('description', $category->description)
@section('page-type', 'category')
@section('content')
<section class="category-hero category-{{ $category->slug }}">
    <img src="{{ asset($category->image_path) }}" alt="{{ $category->name }}"><div class="category-hero-overlay"></div>
    <div class="category-hero-copy container"><nav class="breadcrumb"><a href="{{ route('storefront.home') }}">خانه</a><span>/</span><span>{{ $category->name }}</span></nav><span class="section-kicker">انتخابی برای سبک تو</span><h1>{{ $category->name }}</h1><p>{{ $category->description }}</p><div class="category-highlights">@foreach(($category->features ?? []) as $feature)<span class="category-chip">{{ $feature }}</span>@endforeach</div></div>
</section>
<section class="listing-section container section-space">
    <div class="section-heading section-heading-row"><div><span class="section-kicker">کالکشن شهباز</span><h2>محصولات {{ $category->name }}</h2><p>{{ $products->total() }} محصول</p></div>
        <form method="GET" class="listing-sort"><label for="product-sort">مرتب‌سازی</label><select id="product-sort" name="sort" data-submit-on-change><option value="featured" @selected($sort === 'featured')>پیشنهادی</option><option value="price-asc" @selected($sort === 'price-asc')>ارزان‌ترین</option><option value="price-desc" @selected($sort === 'price-desc')>گران‌ترین</option><option value="discount" @selected($sort === 'discount')>بیشترین تخفیف</option></select>
            @foreach($filters['sizes'] as $size)<input type="hidden" name="sizes[]" value="{{ $size }}">@endforeach
            @foreach($filters['colors'] as $color)<input type="hidden" name="colors[]" value="{{ $color }}">@endforeach
            @if($filters['min_price'] !== null)<input type="hidden" name="min_price" value="{{ $filters['min_price'] }}">@endif
            @if($filters['max_price'] !== null)<input type="hidden" name="max_price" value="{{ $filters['max_price'] }}">@endif
            @if($filters['sale'])<input type="hidden" name="sale" value="1">@endif
        </form>
    </div>
    <div class="listing-content-layout">
        <aside class="category-filter-sidebar" id="category-filter-sidebar" aria-label="فیلتر محصولات">
            <div class="filter-panel-inner">
                <div class="filter-panel-heading"><div><span class="eyebrow">انتخاب دقیق‌تر</span><h3>فیلترهای محصولات</h3><p>نتایج را مطابق سلیقه‌ات محدود کن.</p></div><button class="icon-button filter-sidebar-close" type="button" data-filter-close aria-label="بستن فیلترها">×</button><span class="filter-count-badge" @if($selectedFilterCount === 0) hidden @endif>{{ $selectedFilterCount }}</span></div>
                <form method="GET" action="{{ route('storefront.categories.'.$category->slug) }}" id="category-filter-form">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <section class="filter-group"><h4>محدوده قیمت <small>تومان</small></h4><div class="filter-price-fields"><label><span>از</span><input type="number" name="min_price" min="0" inputmode="numeric" placeholder="حداقل" value="{{ $filters['min_price'] }}"></label><i>—</i><label><span>تا</span><input type="number" name="max_price" min="0" inputmode="numeric" placeholder="حداکثر" value="{{ $filters['max_price'] }}"></label></div><div class="filter-range-note">از {{ number_format($minCatalogPrice) }} تا {{ number_format($maxCatalogPrice) }}</div></section>
                    @if($availableColors->isNotEmpty())<section class="filter-group"><h4>رنگ محصول</h4><div class="filter-choice-list">@foreach($availableColors as $color)<label class="filter-choice"><input type="checkbox" name="colors[]" value="{{ $color }}" @checked(in_array($color,$filters['colors'],true))><i class="filter-color-dot" aria-hidden="true"></i><span>{{ $color }}</span></label>@endforeach</div></section>@endif
                    @if($availableSizes->isNotEmpty())<section class="filter-group"><h4>سایز</h4><div class="filter-choice-list">@foreach($availableSizes as $size)<label class="filter-choice filter-size-choice"><input type="checkbox" name="sizes[]" value="{{ $size }}" @checked(in_array($size,$filters['sizes'],true))><span>{{ $size }}</span></label>@endforeach</div></section>@endif
                    <section class="filter-group filter-sale-group"><label class="filter-switch-row"><span><strong>فقط محصولات تخفیف‌دار</strong><small>نمایش قیمت‌های دارای تخفیف</small></span><input type="checkbox" name="sale" value="1" @checked($filters['sale'])><i aria-hidden="true"></i></label></section>
                    <div class="filter-panel-actions"><button class="filter-apply-button" type="submit">اعمال فیلترها <span>{{ $products->total() }}</span></button><a class="filter-reset-button" href="{{ route('storefront.categories.'.$category->slug) }}">پاک کردن فیلترها</a></div>
                </form>
            </div>
        </aside>
        <div class="listing-results">
            <button type="button" class="mobile-filter-trigger" data-filter-open><span class="mobile-filter-trigger-icon" aria-hidden="true">☷</span><span>فیلتر محصولات</span><b>{{ $selectedFilterCount }}</b></button>
            @if ($products->count())<div class="product-grid listing-grid">@foreach($products as $product)@include('partials.product-card', ['product' => $product, 'savedProductIds' => $savedProductIds ?? []])@endforeach</div>@else<div class="empty-state large-empty-state"><div class="empty-state-icon"><x-icon name="search" /></div><h2>با این فیلترها محصولی پیدا نشد</h2><p>یکی از فیلترها را تغییر بده یا همه را پاک کن.</p><a class="button button-light" href="{{ route('storefront.categories.'.$category->slug) }}">پاک کردن فیلترها</a></div>@endif
            <div class="pagination-wrap">{{ $products->links('vendor.pagination.shahbaz') }}</div>
        </div>
    </div>
</section>
<div class="filter-backdrop" data-filter-backdrop hidden></div>
@endsection
