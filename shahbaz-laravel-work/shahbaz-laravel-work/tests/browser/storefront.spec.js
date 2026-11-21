import { test, expect } from '@playwright/test';

const publicPages = [
  ['/', 'انتخاب‌های محبوب'],
  ['/men', 'محصولات پوشاک مردانه'],
  ['/women', 'محصولات پوشاک زنانه'],
  ['/kids', 'محصولات پوشاک بچگانه'],
  ['/bags-shoes', 'محصولات کیف و کفش'],
  ['/search?q=لینن', 'پیراهن لینن یقه‌دار'],
  ['/cart', 'سبد خرید'],
  ['/wishlist', 'علاقه‌مندی‌های من'],
  ['/account', 'حساب کاربری'],
  ['/login', 'ورود به شهباز'],
  ['/register', 'ساخت حساب کاربری'],
  ['/product/linen-shirt', 'پیراهن لینن یقه‌دار'],
];

test.describe('Shahbaz storefront routes', () => {
  for (const [path, visibleText] of publicPages) {
    test(`renders ${path} through Laravel`, async ({ page }) => {
      const response = await page.goto(path);
      expect(response?.ok()).toBeTruthy();
      await expect(page.locator('body')).toContainText(visibleText);
      await expect(page.locator('main#page-main')).toBeVisible();
    });
  }
});

test('live search loads server-backed product suggestions', async ({ page }) => {
  await page.goto('/');
  await page.locator('#site-search').click();
  await expect(page.locator('.search-glass')).toBeVisible();
  await page.locator('.search-glass input[name="q"]').fill('لینن');
  await expect(page.locator('.search-live-item').first()).toContainText('پیراهن لینن یقه‌دار');
});

test('product options can be added to a database-backed guest cart', async ({ page }) => {
  await page.goto('/product/linen-shirt');
  await page.locator('input[name="size"]').first().check();
  await page.locator('input[name="color"]').first().check();
  await page.locator('[data-product-buy-form] button[type="submit"]').click();
  await expect(page).toHaveURL(/\/product\/linen-shirt$/);
  await page.goto('/cart');
  await expect(page.locator('.cart-page-row')).toContainText('پیراهن لینن یقه‌دار');
});

test('category filters remain usable on a mobile viewport', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/men');
  await page.locator('[data-filter-open]').click();
  await expect(page.locator('#category-filter-sidebar')).toHaveClass(/is-mobile-open/);
  await page.locator('#category-filter-sidebar input[name="sizes[]"]').first().check();
  await page.locator('#category-filter-form button[type="submit"]').click();
  await expect(page).toHaveURL(/sizes%5B%5D=/);
});
