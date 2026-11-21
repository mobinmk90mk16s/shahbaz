<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id', 'guest_token_hash'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(CartItem::class)->with('product.category'); }
    private function purchasableItems()
    {
        return $this->items->filter(fn ($item) => $item->product && $item->product->is_active && $item->product->category?->is_active);
    }
    public function getQuantityAttribute(): int { return (int) $this->purchasableItems()->sum('quantity'); }
    public function getSubtotalAttribute(): int { return (int) $this->purchasableItems()->sum(fn ($item) => $item->product->price * $item->quantity); }
}
