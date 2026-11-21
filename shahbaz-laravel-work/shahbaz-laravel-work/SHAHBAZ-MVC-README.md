# Shahbaz — Laravel MVC update

این بسته کد اجرایی Controller / Model / Migration / Blade / Auth / Server-side cart & wishlist را برای Overlay روی پروژه Laravel موجود فراهم می‌کند.

**قبل از اجرا:** فایل `docs/INSTALLATION-LARAVEL-MVC.md` را کامل بخوانید. ZIP را روی ریشه پروژه Laravel (همان پوشه‌ای که `artisan` دارد) استخراج کنید؛ نه داخل `docs`. فایل `.env` محلی جایگزین نمی‌شود.

## به‌روزرسانی تصاویر دسته‌بندی صفحه اصلی

برای چهار کارت دسته‌بندی صفحه اصلی، تصاویر کاتالوگی اختصاصی در مسیر `public/assets/images/categories/` قرار گرفته‌اند. فیلد `tile_image_path` مسیر تصویر کارت صفحه اصلی را از بنر اسلایدر و تصویر هدر صفحه دسته‌بندی جدا می‌کند؛ بنابراین بنرهای قبلی و طراحی صفحات داخلی بدون تغییر باقی می‌مانند.

پس از دریافت این نسخه، migration جدید با اجرای معمول `php artisan migrate` ستون `tile_image_path` را ایجاد و مسیر تصاویر چهار دسته را در دیتابیس موجود ثبت می‌کند. برای نصب تازه نیز `CategorySeeder` همین مسیرها را از `database/seeders/catalog.json` وارد می‌کند.
