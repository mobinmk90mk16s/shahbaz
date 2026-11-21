<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = ['order_id', 'carrier', 'service', 'tracking_number', 'status', 'shipping_cost', 'shipped_at', 'delivered_at', 'notes'];
    protected function casts(): array { return ['shipping_cost' => 'integer', 'shipped_at' => 'datetime', 'delivered_at' => 'datetime']; }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
