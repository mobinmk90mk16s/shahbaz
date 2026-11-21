<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['order_number', 'user_id', 'guest_token_hash', 'shipping_address_id', 'customer_name', 'customer_mobile', 'customer_email', 'status', 'payment_status', 'fulfillment_status', 'currency', 'subtotal', 'discount_total', 'shipping_total', 'tax_total', 'grand_total', 'coupon_code', 'shipping_address_snapshot', 'billing_address_snapshot', 'customer_note', 'admin_note', 'placed_at', 'cancelled_at', 'cancellation_reason'];
    protected function casts(): array { return ['subtotal' => 'integer', 'discount_total' => 'integer', 'shipping_total' => 'integer', 'tax_total' => 'integer', 'grand_total' => 'integer', 'shipping_address_snapshot' => 'array', 'billing_address_snapshot' => 'array', 'placed_at' => 'datetime', 'cancelled_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function shippingAddress(): BelongsTo { return $this->belongsTo(Address::class, 'shipping_address_id'); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function shipments(): HasMany { return $this->hasMany(Shipment::class); }
    public function statusHistory(): HasMany { return $this->hasMany(OrderStatusHistory::class); }
}
