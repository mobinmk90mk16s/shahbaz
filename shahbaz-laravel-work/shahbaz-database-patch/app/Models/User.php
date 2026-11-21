<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'mobile', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function addresses(): HasMany { return $this->hasMany(Address::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function inventoryMovements(): HasMany { return $this->hasMany(InventoryMovement::class); }
    public function cart(): HasOne { return $this->hasOne(Cart::class); }
    public function wishlist(): HasOne { return $this->hasOne(Wishlist::class); }
    public function roles(): BelongsToMany { return $this->belongsToMany(Role::class); }
    public function hasRole(string $role): bool { return $this->roles()->where('slug', $role)->exists(); }
    public function hasPermission(string $permission): bool
    {
        return $this->roles()->whereHas('permissions', fn ($q) => $q->where('slug', $permission))->exists();
    }
    public function isSuperAdmin(): bool { return $this->hasRole('super-admin'); }
}
