# نصب بسته پیاده‌سازی Laravel MVC شهباز

این ZIP یک بسته به‌روزرسانی روی پروژه Laravel موجود است، نه یک پروژه مستقل همراه `vendor/` یا `.env`. محتویات را روی ریشه پروژه‌ای که `artisan` در آن قرار دارد استخراج کنید؛ مثلاً `shahbaz-laravel-work`. گزینه Replace را فقط برای فایل‌های هم‌نام داخل بسته تأیید کنید. فایل `.env` شخصی شما داخل ZIP نیست و نباید جایگزین شود.

## 1) پیش‌نیازها

- PHP 8.3 یا بالاتر (پروژه فعلی با PHP 8.5 قابل اجراست)
- Composer نصب‌شده و وابستگی‌های پروژه موجود
- MySQL و دیتابیس فعلی `shahbaz`
- Node لازم نیست؛ دارایی‌های این بسته به‌صورت CSS/JS استاتیک در `public/assets` هستند.

در PowerShell برای انتخاب PHP نصب‌شده در مسیر `C:\tools\php-8.5`:

```powershell
$env:Path = "C:\tools\php-8.5;$env:Path"
php -v
composer --version
```

## 2) تنظیم محیط

فایل `.env` فعلی را حفظ کنید. فقط مقدارهای مربوط به اتصال MySQL و گزینه‌های جدید پیامک را با `.env.example` مقایسه کنید. حداقل مقادیر موردنیاز:

```dotenv
APP_LOCALE=fa
APP_FALLBACK_LOCALE=fa
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shahbaz
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
OTP_TTL_MINUTES=5
OTP_MAX_ATTEMPTS=5
SMS_DRIVER=http
SMS_HTTP_URL=
SMS_HTTP_TOKEN=
SMS_SENDER_ID=
```

`APP_KEY` فعلی را بازتولید نکنید؛ اگر از قبل مقدار دارد همان را نگه دارید. هیچ Secret واقعی را داخل Git commit نکنید.

## 3) اجرای به‌روزرسانی دیتابیس

از ریشه پروژه:

```powershell
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=DatabaseSeeder
```

Migrationهای افزایشی هستند: ستون‌های موبایل به `users` اضافه می‌شوند و ایمیل nullable می‌شود؛ جدول‌های کاتالوگ، سبد خرید، علاقه‌مندی، پروفایل مهمان، OTP و RBAC ساخته می‌شوند. Seeder با `updateOrCreate` داده‌های ۴ دسته و ۱۶ محصول نمونه را به‌روزرسانی می‌کند و حساب مدیر آزمایشی ایجاد نمی‌کند.

**به هیچ وجه `php artisan migrate:fresh` اجرا نکنید.** این دستور می‌تواند داده‌های موجود را حذف کند.

## 4) ورود و پیامک

- ورود با موبایل و رمز عبور پس از ثبت‌نام و تأیید OTP فعال است.
- ثبت‌نام شماره موبایل را با OTP تأیید می‌کند.
- ورود OTP برای حساب‌های فعال وجود دارد.
- تا وقتی `SMS_HTTP_URL` و `SMS_HTTP_TOKEN` و قرارداد واقعی سرویس‌دهنده در `.env` تنظیم نشده باشد، ارسال پیامک کار نخواهد کرد. هیچ کد ثابتی برای OTP یا درگاه پیامک آزمایشی در پروژه وجود ندارد.
- قرارداد HTTP فعلی `POST` با Bearer Token و JSON شامل `to`, `message`, `sender_id` است؛ اگر سرویس‌دهنده شما payload متفاوتی می‌خواهد، باید کلاس `app/Services/Sms/HttpSmsGateway.php` با مستندات همان سرویس سازگار شود.
- پس از ساخت حساب و تأیید موبایل، برای اختصاص مدیر کل از PowerShell استفاده کنید:

```powershell
php artisan shahbaz:make-super-admin 09123456789
```

این فرمان فقط یک کاربر موجود را تغییر می‌دهد؛ نقش از فرم یا درخواست مرورگر پذیرفته نمی‌شود.

## 5) اجرای سایت

```powershell
php artisan serve
```

سپس آدرس محلی را در مرورگر باز کنید. در صورت استفاده از Apache/XAMPP، DocumentRoot باید به پوشه `public` همین پروژه اشاره کند.

## 6) رفتار داده‌های قدیمی مرورگر

فایل `legacy-migration.js` کلیدهای قدیمی `shahbaz.cart.v2`, `shahbaz-cart-v1`, `shahbaz.wishlist.v2`, `shahbaz-wishlist-v1` و `shahbaz.profile.v1` را می‌خواند و به `/guest/migrate` می‌فرستد. کلیدهای LocalStorage تنها پس از پاسخ موفق سرور حذف می‌شوند. مهاجرت در سمت سرور idempotent است.

## 7) محدودیت‌های عمدی این مرحله

فرایند سفارش، دفتر موجودی واقعی، هزینه ارسال، Checkout، اتصال درگاه پرداخت و Callback پرداخت در سورس فعلی وجود نداشتند و در این بسته نیز ساخته یا شبیه‌سازی نشده‌اند. برچسب «کالکشن محدود» از داده نمونه کاتالوگ می‌آید و نشان‌دهنده موجودی واقعی انبار نیست. رابط کاربری پنل مدیریت هم طبق درخواست فعلاً ساخته نشده است؛ فقط زیرساخت نقش/مجوز و ثبت فعالیت مدیریتی آماده شده است.

## 8) اجرای تست‌ها

پس از تنظیم `.env`، اجرای Migrationها و Seed کردن کاتالوگ:

```powershell
php artisan test
npm install
npx playwright install chromium
npm run test:e2e
```

تست‌های Feature/Unit با PHPUnit و تست‌های مرورگری در `tests/browser` با Playwright قرار دارند. Playwright به‌عنوان وابستگی توسعه‌ای در `package.json` اضافه شده است. تست‌های مرورگری از سرور محلی و کاتالوگ Seedشده استفاده می‌کنند و به درگاه پیامک واقعی نیاز ندارند. در این بسته، این تست‌ها فقط نوشته و از نظر Syntax بررسی شده‌اند؛ اجرای نهایی آن‌ها باید روی پروژه محلی و دیتابیس همان پروژه انجام شود.
