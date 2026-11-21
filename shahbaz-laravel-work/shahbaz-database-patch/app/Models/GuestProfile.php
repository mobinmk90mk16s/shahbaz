<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestProfile extends Model
{
    protected $fillable = ['guest_token_hash', 'name', 'mobile', 'email', 'expires_at'];
    protected function casts(): array { return ['expires_at' => 'datetime']; }
}
