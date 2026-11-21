<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\Wishlist\WishlistService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function __construct(private readonly WishlistService $wishlists) {}

    private function savedProductIds(): array
    {
        return $this->wishlists->current()?->items->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];
    }

    public function home(): View
    {
        return view('storefront.home', [
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'featuredProducts' => Product::query()->active()->with('category')->orderByDesc('is_featured')->orderBy('sort_order')->limit(8)->get(),
            'limitedProducts' => Product::query()->active()->where('is_limited_collection', true)->with('category')->orderBy('sort_order')->limit(8)->get(),
            'savedProductIds' => $this->savedProductIds(),
        ]);
    }

    public function category(Request $request, string $slug): View
    {
        $category = Category::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();
        $validated = $request->validate([
            'sort' => ['nullable', 'in:featured,price-asc,price-desc,discount'],
            'sizes' => ['sometimes', 'array', 'max:20'],
            'sizes.*' => ['string', 'max:40'],
            'colors' => ['sometimes', 'array', 'max:30'],
            'colors.*' => ['string', 'max:60'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'sale' => ['nullable', 'in:1'],
        ]);

        $minPrice = $validated['min_price'] ?? null;
        $maxPrice = $validated['max_price'] ?? null;
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            throw ValidationException::withMessages(['min_price' => 'حداقل قیمت نمی‌تواند از حداکثر قیمت بیشتر باشد.']);
        }

        $filterCatalog = $category->activeProducts()->get(['sizes', 'colors', 'price']);
        $availableSizes = $filterCatalog->flatMap(fn (Product $product) => $product->sizes ?? [])->unique()->values();
        $availableColors = $filterCatalog->flatMap(fn (Product $product) => $product->colors ?? [])->unique()->values();

        $query = $category->activeProducts()->with('category');
        if (!empty($validated['sizes'])) {
            $query->where(function ($builder) use ($validated) {
                foreach ($validated['sizes'] as $size) $builder->orWhereJsonContains('sizes', $size);
            });
        }
        if (!empty($validated['colors'])) {
            $query->where(function ($builder) use ($validated) {
                foreach ($validated['colors'] as $color) $builder->orWhereJsonContains('colors', $color);
            });
        }
        if ($minPrice !== null) $query->where('price', '>=', $minPrice);
        if ($maxPrice !== null) $query->where('price', '<=', $maxPrice);
        if (($validated['sale'] ?? null) === '1') $query->whereNotNull('compare_at_price')->whereColumn('compare_at_price', '>', 'price');

        $selectedFilterCount = count($validated['sizes'] ?? []) + count($validated['colors'] ?? [])
            + ($minPrice !== null ? 1 : 0) + ($maxPrice !== null ? 1 : 0)
            + (($validated['sale'] ?? null) === '1' ? 1 : 0);

        $sort = $validated['sort'] ?? 'featured';
        match ($sort) {
            'price-asc' => $query->orderBy('price'),
            'price-desc' => $query->orderByDesc('price'),
            'discount' => $query->orderByRaw('(compare_at_price - price) / NULLIF(compare_at_price, 0) DESC'),
            default => $query->orderByDesc('is_featured')->orderBy('sort_order'),
        };

        return view('storefront.category', [
            'category' => $category,
            'products' => $query->paginate(12)->withQueryString(),
            'sort' => $sort,
            'selectedFilterCount' => $selectedFilterCount,
            'filters' => [
                'sizes' => $validated['sizes'] ?? [],
                'colors' => $validated['colors'] ?? [],
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sale' => ($validated['sale'] ?? null) === '1',
            ],
            'availableSizes' => $availableSizes,
            'availableColors' => $availableColors,
            'minCatalogPrice' => (int) ($filterCatalog->min('price') ?? 0),
            'maxCatalogPrice' => (int) ($filterCatalog->max('price') ?? 0),
            'savedProductIds' => $this->savedProductIds(),
        ]);
    }

    public function product(string $slug): View
    {
        $product = Product::query()->active()->with('category')->where('slug', $slug)->firstOrFail();
        $relatedProducts = Product::query()->active()->with('category')->where('category_id', $product->category_id)->where('id', '!=', $product->id)->orderByDesc('is_featured')->limit(4)->get();
        return view('storefront.product', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'savedProductIds' => $this->savedProductIds(),
        ]);
    }

    public function search(Request $request): View
    {
        $validated = $request->validate(['q' => ['nullable','string','max:120'], 'sale' => ['nullable','in:1'], 'remaining' => ['nullable','in:1'], 'sort' => ['nullable','in:featured,price-asc,price-desc,discount']]);
        $term = trim((string) ($validated['q'] ?? ''));
        $query = Product::query()->active()->with('category');
        if ($term !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';
            $query->where(fn ($q) => $q->where('name','like',$like)->orWhere('description','like',$like)->orWhereHas('category',fn ($c) => $c->where('name','like',$like)));
        }
        if (($validated['sale'] ?? null) === '1') $query->whereNotNull('compare_at_price')->whereColumn('compare_at_price','>','price');
        if (($validated['remaining'] ?? null) === '1') $query->where('is_limited_collection', true);
        match ($validated['sort'] ?? 'featured') {
            'price-asc' => $query->orderBy('price'),
            'price-desc' => $query->orderByDesc('price'),
            'discount' => $query->orderByRaw('(compare_at_price - price) / NULLIF(compare_at_price, 0) DESC'),
            default => $query->orderByDesc('is_featured')->orderBy('sort_order'),
        };
        return view('storefront.search', [
            'products' => $query->paginate(12)->withQueryString(),
            'term' => $term,
            'saleOnly' => ($validated['sale'] ?? null) === '1',
            'remainingOnly' => ($validated['remaining'] ?? null) === '1',
            'sort' => $validated['sort'] ?? 'featured',
            'savedProductIds' => $this->savedProductIds(),
        ]);
    }
    public function suggest(Request $request): JsonResponse
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:80']]);
        $term = trim((string) ($validated['q'] ?? ''));
        $query = Product::query()->active()->with('category');
        if ($term !== '') {
            $tokens = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach (array_slice($tokens, 0, 6) as $token) {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $token).'%';
                $query->where(function ($builder) use ($like, $token) {
                    $builder->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', $like))
                        ->orWhereJsonContains('colors', $token)
                        ->orWhereJsonContains('sizes', $token);
                });
            }
        }

        $total = (clone $query)->count();
        $products = $query->orderByDesc('is_featured')->orderBy('sort_order')->limit(6)->get();
        return response()->json([
            'total' => $total,
            'items' => $products->map(fn (Product $product) => [
                'name' => $product->name,
                'category' => $product->category->name,
                'price' => $product->price,
                'image' => asset($product->image_path),
                'url' => route('storefront.products.show', $product->slug),
            ])->values(),
        ]);
    }

}
