<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('tile_image_path')->nullable()->after('card_image_path');
        });

        $images = [
            'men' => 'assets/images/categories/catalog-men.webp',
            'women' => 'assets/images/categories/catalog-women.webp',
            'kids' => 'assets/images/categories/catalog-kids.webp',
            'bags-shoes' => 'assets/images/categories/catalog-bags-shoes.webp',
        ];

        foreach ($images as $slug => $path) {
            DB::table('categories')->where('slug', $slug)->update(['tile_image_path' => $path]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('tile_image_path');
        });
    }
};
