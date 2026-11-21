<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpChallenge extends Model
{
    protected $fillable = ['mobile', 'purpose', 'code_hash', 'expires_at', 'attempts', 'max_attempts', 'last_sent_at', 'consumed_at', 'metadata'];
    protected function casts(): array { return ['expires_at' => 'datetime', 'last_sent_at' => 'datetime', 'consumed_at' => 'datetime', 'metadata' => 'array']; }
}
