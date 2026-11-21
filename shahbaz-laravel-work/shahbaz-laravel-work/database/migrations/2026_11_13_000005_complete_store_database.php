<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Extend the existing catalog without replacing or deleting current products/categories.
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
                $table->index(['parent_id', 'is_active']);
            }
            if (!Schema::hasColumn('categories', 'meta_title')) $table->string('meta_title', 180)->nullable();
            if (!Schema::hasColumn('categories', 'meta_description')) $table->string('meta_description', 320)->nullable();
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sku')) $table->string('sku', 80)->nullable()->unique();
            if (!Schema::hasColumn('products', 'barcode')) $table->string('barcode', 100)->nullable()->unique();
            if (!Schema::hasColumn('products', 'track_inventory')) $table->boolean('track_inventory')->default(false);
            if (!Schema::hasColumn('products', 'weight_grams')) $table->unsignedInteger('weight_grams')->nullable();
            if (!Schema::hasColumn('products', 'meta_title')) $table->string('meta_title', 180)->nullable();
            if (!Schema::hasColumn('products', 'meta_description')) $table->string('meta_description', 320)->nullable();
            if (!Schema::hasColumn('products', 'published_at')) $table->timestamp('published_at')->nullable();
        });

        // expires_at is a business deadline, not an auto-updated timestamp.
        if (Schema::hasColumn('guest_profiles', 'expires_at')) {
            Schema::table('guest_profiles', function (Blueprint $table) {
                $table->timestamp('expires_at')->useCurrent()->change();
            });
        }

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt', 180)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 60)->nullable();
            $table->string('recipient_name', 120);
            $table->string('mobile', 16);
            $table->string('province', 100);
            $table->string('city', 100);
            $table->string('district', 120)->nullable();
            $table->text('address_line');
            $table->string('postal_code', 20)->nullable();
            $table->string('plaque', 20)->nullable();
            $table->string('unit', 20)->nullable();
            $table->text('delivery_note')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_default']);
            $table->index(['province', 'city']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('guest_token_hash', 64)->nullable()->index();
            $table->foreignId('shipping_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->string('customer_name', 120);
            $table->string('customer_mobile', 16)->index();
            $table->string('customer_email')->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->string('payment_status', 32)->default('unpaid')->index();
            $table->string('fulfillment_status', 32)->default('unfulfilled')->index();
            $table->char('currency', 3)->default('IRT');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount_total')->default(0);
            $table->unsignedBigInteger('shipping_total')->default(0);
            $table->unsignedBigInteger('tax_total')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->string('coupon_code', 60)->nullable();
            $table->json('shipping_address_snapshot');
            $table->json('billing_address_snapshot')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 180);
            $table->string('product_slug', 140)->nullable();
            $table->string('sku', 80)->nullable();
            $table->string('product_image', 500)->nullable();
            $table->string('selected_size', 40)->nullable();
            $table->string('selected_color', 60)->nullable();
            $table->json('options')->nullable();
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('line_discount')->default(0);
            $table->unsignedBigInteger('line_total');
            $table->timestamps();
            $table->index(['order_id', 'product_id']);
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'created_at']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 60);
            $table->string('method', 40)->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedBigInteger('amount');
            $table->char('currency', 3)->default('IRT');
            $table->string('authority', 120)->nullable()->unique();
            $table->string('transaction_id', 120)->nullable()->unique();
            $table->string('reference_id', 120)->nullable();
            $table->json('gateway_request')->nullable();
            $table->json('gateway_response')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'created_at']);
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('carrier', 80)->nullable();
            $table->string('service', 80)->nullable();
            $table->string('tracking_number', 120)->nullable()->index();
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->boolean('allow_backorder')->default(false);
            $table->timestamps();
            $table->index(['quantity', 'low_stock_threshold']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32); // receipt, sale, reservation, release, adjustment, return
            $table->integer('quantity_delta');
            $table->unsignedInteger('quantity_after')->nullable();
            $table->string('reference', 120)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'created_at']);
            $table->index(['order_id', 'type']);
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('name', 120)->nullable();
            $table->string('type', 20); // fixed, percentage
            $table->unsignedBigInteger('value');
            $table->unsignedBigInteger('minimum_order_amount')->default(0);
            $table->unsignedBigInteger('maximum_discount')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_per_user')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'starts_at', 'expires_at']);
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('discount_amount');
            $table->timestamps();
            $table->unique(['coupon_id', 'order_id']);
            $table->index(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('product_images');
        // Added columns on existing catalog tables are intentionally retained on rollback
        // to avoid accidental data loss. A dedicated reviewed rollback is required.
    }
};
