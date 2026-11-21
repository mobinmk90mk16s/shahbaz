<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'مدیر کل', 'slug' => 'super-admin', 'description' => 'دسترسی کامل مدیریتی'],
            ['name' => 'مدیر بخش', 'slug' => 'section-manager', 'description' => 'مدیریت بخش‌های مجاز'],
        ];
        foreach ($roles as $data) Role::updateOrCreate(['slug' => $data['slug']], $data);

        $permissions = [
            ['name' => 'مدیریت کاتالوگ', 'slug' => 'catalog.manage'],
            ['name' => 'مدیریت سفارش‌ها', 'slug' => 'orders.manage'],
            ['name' => 'مدیریت کاربران', 'slug' => 'users.manage'],
            ['name' => 'مشاهده گزارش‌ها', 'slug' => 'reports.view'],
            ['name' => 'مدیریت تنظیمات', 'slug' => 'settings.manage'],
        ];
        foreach ($permissions as $data) Permission::updateOrCreate(['slug' => $data['slug']], $data);
        Role::where('slug', 'super-admin')->firstOrFail()->permissions()->sync(Permission::pluck('id')->all());
        Role::where('slug', 'section-manager')->firstOrFail()->permissions()->sync(Permission::whereIn('slug', ['catalog.manage', 'orders.manage'])->pluck('id')->all());
    }
}
