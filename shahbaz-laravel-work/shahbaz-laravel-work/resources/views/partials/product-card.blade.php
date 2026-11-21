@php($isSaved = in_array((int) $product->id, $savedProductIds ?? [], true))
<article class="product-card {{ $modifier ?? '' }}" data-product-card>
    <div class="product-image-wrap">
        <a class="product-image-link" href="{{ route('storefront.products.show', $product->slug) }}" aria-label="مشاهده {{ $product->name }}"><img src="{{ asset($product->image_path) }}" alt="{{ $product->image_alt ?: $product->name }}" loading="lazy" decoding="async"></a>
        @if ($product->discount_percent > 0)<span class="discount-badge">{{ number_format($product->discount_percent) }}٪ تخفیف</span>@endif
        @if ($tagOverride ?? $product->badge)<span class="product-tag">{{ $tagOverride ?? $product->badge }}</span>@endif
        <form action="{{ route('wishlist.store', $product->slug) }}" method="POST">@csrf<button class="product-wishlist {{ $isSaved ? 'is-saved' : '' }}" type="submit" aria-label="{{ $isSaved ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' }}" aria-pressed="{{ $isSaved ? 'true' : 'false' }}"><x-icon name="heart" /></button></form>
    </div>
    <div class="product-info"><span class="product-category">{{ $product->category->name }}</span><h3><a href="{{ route('storefront.products.show', $product->slug) }}">{{ $product->name }}</a></h3><div class="product-price-row"><strong>{{ number_format($product->price) }} تومان</strong>@if($product->compare_at_price)<del>{{ number_format($product->compare_at_price) }} تومان</del>@endif</div><a class="add-to-cart-button" href="{{ route('storefront.products.show', $product->slug) }}">مشاهده و انتخاب <x-icon name="arrow" /></a></div>
</article>
