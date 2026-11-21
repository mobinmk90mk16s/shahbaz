<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'product_name', 'product_slug', 'sku', 'product_image', 'selected_size', 'selected_color', 'options', 'unit_price', 'quantity', 'line_discount', 'line_total'];
    protected function casts(): array { return ['options' => 'array', 'unit_price' => 'integer', 'quantity' => 'integer', 'line_discount' => 'integer', 'line_total' => 'integer']; }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
