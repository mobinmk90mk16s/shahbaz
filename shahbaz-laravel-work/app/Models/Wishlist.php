<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Wishlist extends Model
{
    protected $fillable = ['user_id', 'guest_token_hash'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): BelongsToMany { return $this->belongsToMany(Product::class, 'wishlist_items')->withTimestamps(); }
}
