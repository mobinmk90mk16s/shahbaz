<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['order_id', 'gateway', 'method', 'status', 'amount', 'currency', 'authority', 'transaction_id', 'reference_id', 'gateway_request', 'gateway_response', 'failure_reason', 'paid_at', 'failed_at'];
    protected function casts(): array { return ['amount' => 'integer', 'gateway_request' => 'array', 'gateway_response' => 'array', 'paid_at' => 'datetime', 'failed_at' => 'datetime']; }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
