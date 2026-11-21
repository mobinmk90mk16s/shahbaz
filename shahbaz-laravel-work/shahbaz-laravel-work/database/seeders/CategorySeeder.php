<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(File::get(database_path('seeders/catalog.json')), true, 512, JSON_THROW_ON_ERROR);
        $order = 0;
        foreach ($data['categories'] as $slug => $row) {
            Category::updateOrCreate(['slug' => $slug], [
                'name' => $row['title'], 'description' => $row['description'],
                'image_path' => $row['image'], 'card_image_path' => $row['cardImage'] ?? $row['image'],
                'tile_image_path' => $row['tileImage'] ?? $row['image'],
                'features' => $row['features'] ?? [], 'is_active' => true, 'sort_order' => $order++,
            ]);
        }
    }
}
