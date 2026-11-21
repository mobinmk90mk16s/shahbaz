<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = ['code', 'name', 'type', 'value', 'minimum_order_amount', 'maximum_discount', 'usage_limit', 'usage_per_user', 'used_count', 'starts_at', 'expires_at', 'is_active'];
    protected function casts(): array { return ['value' => 'integer', 'minimum_order_amount' => 'integer', 'maximum_discount' => 'integer', 'usage_limit' => 'integer', 'usage_per_user' => 'integer', 'used_count' => 'integer', 'starts_at' => 'datetime', 'expires_at' => 'datetime', 'is_active' => 'boolean']; }
    public function redemptions(): HasMany { return $this->hasMany(CouponRedemption::class); }
}
