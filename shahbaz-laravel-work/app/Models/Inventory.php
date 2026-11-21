<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $fillable = ['product_id', 'quantity', 'reserved_quantity', 'low_stock_threshold', 'allow_backorder'];
    protected function casts(): array { return ['quantity' => 'integer', 'reserved_quantity' => 'integer', 'low_stock_threshold' => 'integer', 'allow_backorder' => 'boolean']; }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function getAvailableQuantityAttribute(): int { return max(0, $this->quantity - $this->reserved_quantity); }
}
