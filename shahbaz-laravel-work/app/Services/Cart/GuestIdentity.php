<?php

namespace App\Services\Cart;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class GuestIdentity
{
    private bool $resolved = false;
    private ?string $resolvedHash = null;

    public function hash(bool $create = false): ?string
    {
        if ($this->resolved && $this->resolvedHash !== null) return $this->resolvedHash;
        $token = request()->cookie('shahbaz_guest');
        if (!is_string($token) || !preg_match('/^[A-Za-z0-9]{40,80}$/', $token)) {
            if (!$create) return null;
            $token = Str::random(64);
            Cookie::queue(cookie('shahbaz_guest', $token, 60 * 24 * 180, '/', null, request()->isSecure(), true, false, 'lax'));
        }
        $this->resolved = true;
        return $this->resolvedHash = hash('sha256', $token);
    }
}
