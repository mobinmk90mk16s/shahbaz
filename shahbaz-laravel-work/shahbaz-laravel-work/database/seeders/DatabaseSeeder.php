<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // No default or privileged demo account is created.
        $this->call([CategorySeeder::class, ProductSeeder::class, RolePermissionSeeder::class]);
    }
}
