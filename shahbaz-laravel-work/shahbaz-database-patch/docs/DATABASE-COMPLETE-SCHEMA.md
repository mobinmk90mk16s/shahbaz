# Shahbaz Store — Database schema phase

This patch adds an additive Laravel migration for the existing database. It does not drop, truncate, or recreate the existing 16 tables and does not rewrite existing product/category records.

## Existing tables retained

- `users`, `password_reset_tokens`
- `categories`, `products`
- `carts`, `cart_items`, `wishlists`, `wishlist_items`, `guest_profiles`
- `otp_challenges`
- `roles`, `permissions`, `role_user`, `permission_role`
- `admin_activity_logs`, `migrations`

The existing category/product tables receive nullable SEO/SKU metadata and category hierarchy fields. Existing catalog rows remain intact. Legacy products get `track_inventory = false` so an unknown stock quantity is not incorrectly treated as zero stock. Enable stock tracking only after real quantities have been entered.

`guest_profiles.expires_at` is altered to use a creation-time default without MySQL's `ON UPDATE CURRENT_TIMESTAMP` behavior. It must represent a fixed expiry deadline, not move whenever the profile is edited.

## New tables

| Table | Purpose |
|---|---|
| `product_images` | Multiple product images, ordering and primary image |
| `addresses` | Saved customer delivery addresses |
| `orders` | Order header, immutable address snapshots, customer and totals |
| `order_items` | Immutable product/price/option snapshots per order line |
| `payments` | Payment attempts, gateway authority/reference and sanitized response data |
| `order_status_histories` | Auditable order status transitions |
| `shipments` | Carrier, tracking number and fulfillment lifecycle |
| `inventories` | On-hand, reserved and available product quantities |
| `inventory_movements` | Auditable stock receipts, sales, reservations, releases and adjustments |
| `coupons` | Fixed/percentage coupon definitions and limits |
| `coupon_redemptions` | Coupon use linked to order and optional customer |

## Money and inventory conventions

- All money columns are unsigned integer minor units. For this project, confirm whether catalog values are stored in Rial or Toman and use that convention consistently; `currency` defaults to `IRT` because the current storefront displays catalog prices in Toman. Convert to Rial only at a payment gateway boundary when that provider requires it; do not silently mix units.
- `orders` and `order_items` store snapshots. Historical orders must not change when a product name, image, option or price changes later.
- `inventories.quantity` is on-hand stock; `reserved_quantity` is stock reserved for unpaid/in-process orders. Available stock is `max(0, quantity - reserved_quantity)`.
- Stock mutations must be performed in a DB transaction with `lockForUpdate()` and a corresponding `inventory_movements` record. Do not update stock directly from browser input.
- Legacy catalog products are not stock-tracked until a real opening quantity is entered and `products.track_inventory` is enabled.

## Safe deployment

1. Back up the `shahbaz` database.
2. Keep the existing `.env` unchanged and verify `DB_DATABASE=shahbaz`.
3. From the Laravel project root run:

```powershell
php artisan optimize:clear
php artisan migrate:status
```

4. Confirm migrations `000001` through `000004` are already marked as Ran and `000005` is Pending. Then run:

```powershell
php artisan migrate --force
```

For local development, `--force` may be omitted. Never use `migrate:fresh`, `migrate:refresh`, or `db:wipe` against this database.

5. Verify the new tables and migration status:

```powershell
php artisan migrate:status
```

The migration is additive. Its rollback removes the newly-created tables only; added catalog columns are intentionally retained to avoid accidental data loss. A reviewed rollback is required if those columns ever need removal.

## Important scope boundary

This phase establishes schema and Eloquent model relationships. It does **not** yet implement checkout, payment gateway callbacks, coupon validation, shipment integration, or stock reservation services/controllers. Those workflows must be added with authorization, transactions, idempotency and tests before accepting real orders or payments. Never store card numbers, CVV, gateway passwords, API secrets or unredacted sensitive payment payloads in `gateway_request` / `gateway_response`.
