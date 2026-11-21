<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(File::get(database_path('seeders/catalog.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($data['products'] as $index => $row) {
            $category = Category::where('slug', $row['category'])->firstOrFail();
            Product::updateOrCreate(['slug' => $row['slug']], [
                'category_id' => $category->id, 'name' => $row['title'], 'description' => $row['description'],
                'price' => $row['price'], 'compare_at_price' => $row['oldPrice'] ?? null,
                'image_path' => $row['image'], 'image_alt' => $row['alt'] ?? $row['title'],
                'badge' => $row['tag'] ?? null, 'sizes' => $row['sizes'] ?? [], 'colors' => $row['colors'] ?? [],
                'is_active' => true, 'is_featured' => isset($row['tag']),
                'is_limited_collection' => !empty($row['stockRemaining']), 'sort_order' => $index,
            ]);
        }
    }
}
