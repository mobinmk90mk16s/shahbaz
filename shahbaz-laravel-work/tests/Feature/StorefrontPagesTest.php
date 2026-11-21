<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_homepage_is_rendered_by_laravel_and_shows_catalog(): void
    {
        $this->get(route('storefront.home'))->assertOk()->assertSee('انتخاب‌های محبوب')->assertSee('پیراهن لینن یقه‌دار');
    }

    public function test_category_and_product_pages_are_rendered_from_database(): void
    {
        $this->get(route('storefront.categories.men'))->assertOk()->assertSee('پوشاک مردانه');
        $this->get(route('storefront.products.show', 'linen-shirt'))->assertOk()->assertSee('پیراهن لینن یقه‌دار');
    }

    public function test_search_page_accepts_query_and_filters(): void
    {
        $this->get(route('storefront.search', ['q' => 'لینن']))->assertOk()->assertSee('پیراهن لینن یقه‌دار');
    }
}
