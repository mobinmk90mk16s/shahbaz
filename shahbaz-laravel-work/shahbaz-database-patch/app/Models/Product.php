<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'description', 'price', 'compare_at_price', 'image_path', 'image_alt', 'badge', 'sizes', 'colors', 'is_active', 'is_featured', 'is_limited_collection', 'sort_order', 'sku', 'barcode', 'track_inventory', 'weight_grams', 'meta_title', 'meta_description', 'published_at'];
    protected function casts(): array
    {
        return ['price' => 'integer', 'compare_at_price' => 'integer', 'sizes' => 'array', 'colors' => 'array', 'is_active' => 'boolean', 'is_featured' => 'boolean', 'is_limited_collection' => 'boolean', 'sort_order' => 'integer', 'track_inventory' => 'boolean', 'weight_grams' => 'integer', 'published_at' => 'datetime'];
    }
    public function getRouteKeyName(): string { return 'slug'; }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function images(): HasMany { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function inventory(): HasOne { return $this->hasOne(Inventory::class); }
    public function inventoryMovements(): HasMany { return $this->hasMany(InventoryMovement::class); }
    public function cartItems(): HasMany { return $this->hasMany(CartItem::class); }
    public function wishlistItems(): HasMany { return $this->hasMany(WishlistItem::class); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true)->whereHas('category', fn ($q) => $q->where('is_active', true)); }
    public function getDiscountPercentAttribute(): int
    {
        if (!$this->compare_at_price || $this->compare_at_price <= $this->price) return 0;
        return (int) round((1 - ($this->price / $this->compare_at_price)) * 100);
    }
}
