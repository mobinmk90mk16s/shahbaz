<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'image_path', 'card_image_path', 'features', 'is_active', 'sort_order', 'parent_id', 'meta_title', 'meta_description'];
    protected function casts(): array { return ['is_active' => 'boolean', 'sort_order' => 'integer', 'features' => 'array', 'parent_id' => 'integer']; }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order'); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function activeProducts(): HasMany { return $this->products()->where('is_active', true); }
}
