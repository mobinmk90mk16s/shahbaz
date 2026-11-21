<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile', 16)->nullable()->after('name');
            $table->timestamp('mobile_verified_at')->nullable()->after('mobile');
            $table->boolean('is_active')->default(true)->after('password');
            $table->string('email')->nullable()->change();
            $table->unique('mobile', 'users_mobile_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_mobile_unique');
            $table->dropColumn(['mobile', 'mobile_verified_at', 'is_active']);
            // Existing rows may now have a null email; never coerce or delete user data here.
        });
    }
};
