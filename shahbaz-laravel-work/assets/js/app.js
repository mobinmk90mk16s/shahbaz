php/* =========================================================
   شهباز | هسته تعاملات فروشگاه
   کاتالوگ مستقل + سبد/علاقه‌مندی مشترک + صفحات دسته‌بندی و حساب کاربری
   داده‌ها فعلاً در مرورگر نگهداری می‌شوند؛ اتصال ورود و پرداخت به بک‌اند
   عمداً تا زمان دریافت سرویس واقعی غیرفعال است.
   ========================================================= */
(() => {
  'use strict';

  const CATALOG = window.SHAHBAZ_CATALOG || [];
  const CATEGORIES = window.SHAHBAZ_CATEGORIES || {};
  const ROOT = document.body.dataset.root || './';
  const KEYS = {
    cart: 'shahbaz.cart.v2',
    wishlist: 'shahbaz.wishlist.v2',
    profile: 'shahbaz.profile.v1'
  };
  const LEGACY_KEYS = { cart: 'shahbaz-cart-v1', wishlist: 'shahbaz-wishlist-v1' };
  const LEGACY_PRODUCT_IDS = { 'daily-sneakers':'everyday-sneaker', 'soft-sweatshirt':'ribbed-top' };
  const page = document.body.dataset.page || 'home';
  const categoryKey = document.body.dataset.category || '';
  const money = new Intl.NumberFormat('fa-IR');
  const $ = (selector, scope = document) => scope.querySelector(selector);
  const $$ = (selector, scope = document) => [...scope.querySelectorAll(selector)];
  const productById = new Map(CATALOG.map((product) => [product.id, product]));

  let cart = loadCart();
  let wishlist = loadWishlist();
  let drawerOpen = false;
  let mobileMenuOpen = false;
  let settingsOpen = false;
  let returnFocus = null;
  let toastTimer = null;

  function route(path = '') { return `${ROOT}${path}`; }
  function asset(path) { return `${ROOT}${path}`; }
  function imagePath(path) { return /^(?:https?:|data:|\/)/i.test(String(path || '')) ? String(path) : asset(path); }
  function escapeHTML(value = '') {
    return String(value).replace(/[&<>"']/g, (char) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' })[char]);
  }
  function number(value) { return money.format(Number(value) || 0); }
  function price(value) { return `${number(value)} تومان`; }
  function discount(product) {
    if (product.discount) return product.discount;
    if (!product.oldPrice || product.oldPrice <= product.price) return 0;
    return Math.round((1 - product.price / product.oldPrice) * 100);
  }
  function icon(name, extraClass = '') {
    const paths = {
      menu:'<path d="M4 7h16M4 12h16M4 17h16"/>', close:'<path d="m6 6 12 12M18 6 6 18"/>',
      search:'<circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.1 4.1"/>',
      heart:'<path d="M20.2 8.7c0 5.1-8.2 10.1-8.2 10.1S3.8 13.8 3.8 8.7A4.4 4.4 0 0 1 12 6.4a4.4 4.4 0 0 1 8.2 2.3Z"/>',
      user:'<circle cx="12" cy="7.7" r="3.3"/><path d="M5.1 20c.5-3.4 3.2-5.6 6.9-5.6s6.4 2.2 6.9 5.6"/>',
      bag:'<path d="M4 8.2h16l-1.3 11H5.3L4 8.2Z"/><path d="M8.3 9V6.4a3.7 3.7 0 0 1 7.4 0V9"/>',
      arrow:'<path d="M19 12H5m0 0 6-6m-6 6 6 6"/>',
      plus:'<path d="M12 5v14M5 12h14"/>', minus:'<path d="M5 12h14"/>', trash:'<path d="M4 7h16M9 7V4h6v3m-9 0 1 13h10l1-13M10 11v5m4-5v5"/>',
      truck:'<path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z"/><circle cx="7.5" cy="18" r="1.8"/><circle cx="17.5" cy="18" r="1.8"/>',
      shield:'<path d="M12 3 20 6v5.5c0 4.5-3.1 7.7-8 9.5-4.9-1.8-8-5-8-9.5V6l8-3Z"/><path d="m8.5 12 2.2 2.2 4.8-4.8"/>',
      return:'<path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.7 9A7 7 0 0 1 18 6.2L20 12M4 12l2 5.8A7 7 0 0 0 18.3 15"/>',
      headset:'<path d="M4 13v-2a8 8 0 0 1 16 0v2M4 13h3v6H6a2 2 0 0 1-2-2v-4ZM20 13h-3v6h1a2 2 0 0 0 2-2v-4ZM17 20h-5"/>',
      spark:'<path d="m12 2 1.7 6.3L20 10l-6.3 1.7L12 18l-1.7-6.3L4 10l6.3-1.7L12 2Z"/>',
      pin:'<path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.2"/>',
      edit:'<path d="m4 16.5-.8 4.3 4.3-.8L19.7 7.8a2.1 2.1 0 0 0-3-3L4 16.5Z"/><path d="m14.9 6.6 3 3"/>',
      box:'<path d="m12 3 8 4.2v9.6L12 21l-8-4.2V7.2L12 3Z"/><path d="m4.5 7.5 7.5 4 7.5-4M12 12v8.5M8 5.1l8 4.2"/>',
      check:'<path d="m5 12.5 4.5 4.5L19 7"/>'
    };
    return `<svg class="icon ${extraClass}" viewBox="0 0 24 24" aria-hidden="true" focusable="false">${paths[name] || paths.spark}</svg>`;
  }
  function brandMark() {
    return `<span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 44 44" fill="none"><path d="M7 29.5C13.3 28.7 18.7 24.6 23.1 17.2C25.1 13.8 28.4 10.9 33.4 9.2C32.8 14.5 30.5 19.1 26.5 22.9C22.1 27.1 15.9 29.5 7 29.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M10.2 34.2C17.4 33.8 24.6 31.3 30.2 26.6C33.2 24.1 35.4 21 37 17.4M7 29.5l3.2 4.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>`;
  }

  function readStorage(key, fallback) {
    try {
      const raw = localStorage.getItem(key);
      if (!raw) return fallback;
      const value = JSON.parse(raw);
      return value;
    } catch { return fallback; }
  }
  function writeStorage(key, value) {
    try { localStorage.setItem(key, JSON.stringify(value)); return true; }
    catch { toast('ذخیره‌سازی در این مرورگر در دسترس نیست.'); return false; }
  }
  function loadCart() {
    const current = readStorage(KEYS.cart, null);
    const legacy = current === null ? readStorage(LEGACY_KEYS.cart, []) : current;
    if (!Array.isArray(legacy)) return [];
    const normalized = [];
    legacy.forEach((row) => {
      const rawId = String(row.id || '');
      const id = LEGACY_PRODUCT_IDS[rawId] || rawId;
      const product = productById.get(id);
      const quantity = Math.max(1, Math.min(99, Math.floor(Number(row.quantity) || 1)));
      if (!product) return;
      const existing = normalized.find((entry) => entry.id === id && (entry.size || '') === String(row.size || '') && (entry.color || '') === String(row.color || ''));
      if (existing) existing.quantity = Math.min(99, existing.quantity + quantity);
      else normalized.push({ id, quantity, size:row.size || '', color:row.color || '' });
    });
    return normalized;
  }
  function loadWishlist() {
    const current = readStorage(KEYS.wishlist, null);
    const legacy = current === null ? readStorage(LEGACY_KEYS.wishlist, []) : current;
    return Array.isArray(legacy) ? [...new Set(legacy.map((rawId) => LEGACY_PRODUCT_IDS[String(rawId)] || String(rawId)).filter((id) => productById.has(id)))] : [];
  }
  function saveCart() { writeStorage(KEYS.cart, cart); }
  function saveWishlist() { writeStorage(KEYS.wishlist, wishlist); }
  function getCartProducts() {
    return cart.map((line) => {
      const product = productById.get(line.id);
      return product ? { ...product, quantity: line.quantity, size: line.size || '', color: line.color || '', key: lineKey(line) } : null;
    }).filter(Boolean);
  }
  function lineKey(line) { return `${line.id}::${line.size || ''}::${line.color || ''}`; }
  function cartQuantity() { return cart.reduce((total, line) => total + line.quantity, 0); }
  function cartTotal() { return getCartProducts().reduce((sum, item) => sum + item.price * item.quantity, 0); }

  function toast(message) {
    const region = $('#toast-region');
    if (!region) return;
    region.innerHTML = `<div class="toast-message" role="status">${escapeHTML(message)}</div>`;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { region.replaceChildren(); }, 2600);
  }

  function renderHeader() {
    const active = page === 'category' ? categoryKey : page;
    const navItems = [
      ['خانه', '', 'home'], ['مردانه', 'men/', 'men'], ['زنانه', 'women/', 'women'],
      ['بچگانه', 'kids/', 'kids'], ['کیف و کفش', 'bags-shoes/', 'bags-shoes']
    ];
    const desktopNav = navItems.map(([label, href, key]) => `<a class="nav-link ${active === key ? 'is-active' : ''}" href="${route(href)}" ${active === key ? 'aria-current="page"' : ''}>${label}</a>`).join('');
    const menuLinks = navItems.map(([label, href, key]) => `<a href="${route(href)}" ${active === key ? 'aria-current="page"' : ''}>${label}${icon('arrow')}</a>`).join('');
    $('#site-shell').innerHTML = `
      <div class="announcement-bar"><span>انتخاب‌های خوش‌سلیقه، برای هر روز</span><i></i><span>به دنیای شهباز خوش آمدی</span></div>
      <header class="site-header" id="top">
        <div class="header-inner container">
          <button class="icon-button mobile-menu-trigger" id="mobile-menu-trigger" type="button" aria-label="باز کردن منو" aria-expanded="false">${icon('menu')}</button>
          <a class="brand" href="${route('')}" aria-label="صفحه اصلی شهباز">${brandMark()}<span class="brand-copy"><strong>شهباز</strong><small>انتخابی برای سبک تو</small></span></a>
          <nav class="main-nav" aria-label="منوی اصلی">${desktopNav}</nav>
          <div class="header-tools">
            <form class="search-form" id="search-form" role="search"><label class="visually-hidden" for="site-search">جست‌وجوی محصولات</label><input id="site-search" name="q" type="search" placeholder="جست‌وجو در شهباز..." autocomplete="off"><button type="submit" aria-label="جست‌وجو">${icon('search')}</button></form>
            <button class="header-action" id="wishlist-trigger" type="button" aria-label="نمایش علاقه‌مندی‌ها"><span class="action-icon-wrap">${icon('heart')}<b class="tiny-count" id="wishlist-count">۰</b></span><span>علاقه‌مندی‌ها</span></button>
            <a class="header-action" href="${route('account/')}" aria-label="حساب کاربری">${icon('user')}<span>حساب کاربری</span></a>
            <button class="header-action" id="cart-trigger" type="button" aria-label="باز کردن سبد خرید" aria-haspopup="dialog"><span class="action-icon-wrap">${icon('bag')}<b class="cart-count">۰</b></span><span>سبد خرید</span></button>
          </div>
          <button class="icon-button mobile-cart-trigger" id="mobile-cart-trigger" type="button" aria-label="باز کردن سبد خرید">${icon('bag')}<b class="cart-count">۰</b></button>
        </div>
      </header>
      <div class="mobile-menu-overlay" id="mobile-menu-overlay" hidden></div>
      <aside class="mobile-menu" id="mobile-menu" aria-hidden="true" aria-label="منوی موبایل">
        <div class="mobile-menu-head"><a class="brand" href="${route('')}">${brandMark()}<span class="brand-copy"><strong>شهباز</strong><small>انتخابی برای سبک تو</small></span></a><button class="icon-button" id="mobile-menu-close" type="button" aria-label="بستن منو">${icon('close')}</button></div>
        <nav class="mobile-nav-links">${menuLinks}<a href="${route('account/')}">حساب کاربری${icon('user')}</a><a href="${route('wishlist/')}">علاقه‌مندی‌ها${icon('heart')}</a></nav>
        <p class="mobile-menu-note">سادگی، کیفیت و انتخاب آگاهانه</p>
      </aside>`;
  }

  function renderFooter() {
    $('#site-footer-mount').innerHTML = `
      <footer class="site-footer" id="site-footer">
        <div class="footer-main">
          <div class="footer-grid">
            <!-- نمادهای اعتماد -->
            <section class="footer-section trust-section" aria-labelledby="trust-title">
              <h2 id="trust-title" class="section-title">نمادهای اعتماد</h2>
              <div class="trust-badges" aria-label="محل قرارگیری نمادهای اعتماد">
                <span class="icon-slot badge-slot" role="img" aria-label="محل نماد اعتماد اول"></span>
                <span class="icon-slot badge-slot" role="img" aria-label="محل نماد اعتماد دوم"></span>
                <span class="icon-slot badge-slot" role="img" aria-label="محل نماد اعتماد سوم"></span>
              </div>
            </section>

            <!-- اطلاعات تماس -->
            <section class="footer-section contact-section" aria-labelledby="contact-title">
              <h2 id="contact-title" class="section-title">اطلاعات تماس</h2>
              <a class="contact-link" href="mailto:info@shahbaz.com">info@shahbaz.com</a>
              <a class="contact-link" href="tel:02112345678">021-12345678</a>
            </section>

            <!-- درباره ما -->
            <section class="footer-section about-section" aria-labelledby="about-title">
              <h2 id="about-title" class="section-title">درباره ما</h2>
              <ul class="footer-links">
                <li><a href="${route('')}#about">داستان ما</a></li>
                <li><a href="#contact">تماس با ما</a></li>
                <li><a href="#cooperation">همکاری با ما</a></li>
              </ul>
            </section>

            <!-- دسته‌بندی‌ها -->
            <section class="footer-section categories-section" aria-labelledby="categories-title">
              <h2 id="categories-title" class="section-title">دسته‌بندی‌ها</h2>
              <ul class="footer-links">
                <li><a href="${route('men/')}">مردانه</a></li>
                <li><a href="${route('women/')}">زنانه</a></li>
                <li><a href="${route('kids/')}">بچگانه</a></li>
                <li><a href="${route('bags-shoes/')}">کیف و کفش</a></li>
              </ul>
            </section>

            <!-- توضیحات و شبکه‌های اجتماعی -->
            <section class="footer-section description-section" aria-label="معرفی فروشگاه">
              <p class="description-line">شهباز، همراه شما در مسیر استایل بهتر</p>
              <p class="description-line description-second">با کیفیت بالاتر و تنوع بیشتر</p>
              <div class="social-links" aria-label="شبکه‌های اجتماعی">
                <span class="icon-slot social-slot" role="img" aria-label="محل آیکن اینستاگرام"></span>
                <span class="icon-slot social-slot" role="img" aria-label="محل آیکن واتساپ"></span>
              </div>
            </section>

            <!-- برند -->
            <section class="footer-section brand-section" aria-label="نام فروشگاه شاهباز">
              <div class="brand-mark-slot" aria-hidden="true"></div>
              <div class="brand-name">SHAHBAZ</div>
              <div class="brand-caption">FASHION STORE</div>
            </section>
          </div>
        </div>

        <div class="copyright">
          <p data-copyright>© تمامی حقوق محفوظ است. فروشگاه شاهباز.</p>
        </div>
      </footer>
    `;
  }

  function renderOverlays() {
    $('#site-overlays').innerHTML = `
      <nav class="bottom-navigation" aria-label="ناوبری سریع موبایل">
        <button type="button" data-bottom-action="menu">${icon('menu')}<span>منو</span></button>
        <button type="button" data-bottom-action="cart"><span class="bottom-icon-wrap">${icon('bag')}<b class="cart-count">۰</b></span><span>سبد خرید</span></button>
        <a class="bottom-home" href="${route('')}" ${page === 'home' ? 'aria-current="page"' : ''}><span>${icon('spark')}</span><span>خانه</span></a>
        <a href="${route('wishlist/')}">${icon('heart')}<span>علاقه‌مندی</span></a>
        <button type="button" data-bottom-action="settings">${icon('user')}<span>تنظیمات</span></button>
      </nav>
      <div class="drawer-overlay" id="drawer-overlay" hidden></div>
      <aside class="side-drawer cart-drawer" id="cart-drawer" role="dialog" aria-modal="true" aria-labelledby="cart-title" aria-hidden="true">
        <div class="drawer-header"><div><span class="drawer-eyebrow">انتخاب‌های تو</span><h2 id="cart-title">سبد خرید <small id="cart-title-count">(۰)</small></h2></div><button class="icon-button drawer-close" type="button" data-close-drawer aria-label="بستن">${icon('close')}</button></div>
        <div class="drawer-body"><div class="empty-state" id="cart-empty-state"><span class="empty-state-icon">${icon('bag')}</span><h3>سبدت هنوز خالیه</h3><p>چند انتخاب دوست‌داشتنی پیدا کن و به سبدت اضافه کن.</p><a class="button button-dark" href="${route('')}#categories">رفتن به دسته‌بندی‌ها</a></div><div class="cart-items" id="cart-items" aria-live="polite"></div></div>
        <div class="drawer-footer" id="cart-drawer-footer" hidden><div class="cart-subtotal"><span>جمع سبد خرید</span><strong id="cart-subtotal-value">۰ تومان</strong></div><p>هزینه ارسال در مرحله ثبت سفارش محاسبه می‌شود.</p><a class="button button-dark button-full" href="${route('cart/')}">مشاهده سبد و ادامه خرید ${icon('arrow')}</a><small>پرداخت آنلاین در نسخه متصل به درگاه فعال خواهد شد.</small></div>
      </aside>
      <aside class="side-drawer wishlist-drawer" id="wishlist-drawer" role="dialog" aria-modal="true" aria-labelledby="wishlist-title" aria-hidden="true">
        <div class="drawer-header"><div><span class="drawer-eyebrow">برای بعد نگه دار</span><h2 id="wishlist-title">علاقه‌مندی‌های من <small id="wishlist-title-count">(۰)</small></h2></div><button class="icon-button drawer-close" type="button" data-close-drawer aria-label="بستن">${icon('close')}</button></div>
        <div class="drawer-body"><div class="empty-state" id="wishlist-empty-state"><span class="empty-state-icon">${icon('heart')}</span><h3>هنوز چیزی ذخیره نکردی</h3><p>با لمس قلب روی محصولات، انتخاب‌های موردعلاقه‌ات را اینجا نگه دار.</p><a class="button button-dark" href="${route('')}">کشف انتخاب‌ها</a></div><div class="wishlist-items" id="wishlist-items" aria-live="polite"></div></div>
        <div class="drawer-footer" id="wishlist-drawer-footer" hidden><a class="button button-dark button-full" href="${route('wishlist/')}">مشاهده همه علاقه‌مندی‌ها</a></div>
      </aside>
      <div class="settings-popover" id="settings-popover" hidden><a href="${route('account/')}">${icon('user')} حساب کاربری</a><a href="${route('wishlist/')}">${icon('heart')} علاقه‌مندی‌ها</a><a href="${route('')}#top">بازگشت به بالا</a></div>
      <div class="toast-region" id="toast-region" aria-live="polite" aria-atomic="true"></div>`;
  }

  function productCard(product, modifier = '', tagOverride = '') {
    const pct = discount(product);
    const saved = wishlist.includes(product.id);
    return `<article class="product-card ${modifier}" data-product-card data-id="${escapeHTML(product.id)}">
      <div class="product-image-wrap">
        <a class="product-image-link" href="${route(`product.html?id=${encodeURIComponent(product.id)}`)}" aria-label="مشاهده ${escapeHTML(product.title)}"><img src="${escapeHTML(imagePath(product.image))}" alt="${escapeHTML(product.alt)}" loading="lazy" decoding="async" data-fallback="${asset('assets/images/fallback-fashion.svg')}"></a>
        ${pct ? `<span class="discount-badge">${number(pct)}٪ تخفیف</span>` : ''}
        ${(tagOverride || product.tag) ? `<span class="product-tag">${escapeHTML(tagOverride || product.tag)}</span>` : ''}
        <button class="product-wishlist ${saved ? 'is-saved' : ''}" type="button" data-wishlist-button data-id="${escapeHTML(product.id)}" aria-label="${saved ? 'حذف از' : 'افزودن به'} علاقه‌مندی‌ها" aria-pressed="${saved}">${icon('heart')}</button>
      </div>
      <div class="product-info"><span class="product-category">${escapeHTML(product.categoryLabel)}</span><h3><a href="${route(`product.html?id=${encodeURIComponent(product.id)}`)}">${escapeHTML(product.title)}</a></h3>
        <div class="product-price-row"><strong>${price(product.price)}</strong>${product.oldPrice ? `<del>${number(product.oldPrice)} تومان</del>` : ''}</div>
        <button class="add-to-cart-button" type="button" data-add-to-cart data-id="${escapeHTML(product.id)}">افزودن به سبد ${icon('plus')}</button>
      </div></article>`;
  }
  function categoryCard(key) {
    const category = CATEGORIES[key];
    const title = escapeHTML(category.title);
    return `<a class="category-card category-${key}" href="${route(`${key}/`)}" aria-label="مشاهده دسته ${title}"><img src="${imagePath(category.cardImage || category.image)}" alt="${escapeHTML(category.alt)}" loading="lazy" decoding="async" data-fallback="${asset('assets/images/fallback-fashion.svg')}"><span class="category-overlay" aria-hidden="true"></span><span class="category-content"><strong>${title}</strong><b aria-hidden="true">${icon('arrow')}</b></span></a>`;
  }

  function renderHome() {
    $('#page-main').innerHTML = `
      <section class="hero-slider container" data-hero-slider aria-label="اسلایدر تبلیغاتی شهباز">
        <div class="hero-slider-stage">
          <article class="hero-slide is-active" data-hero-slide aria-hidden="false">
            <img src="${asset('assets/images/sliders/men.webp')}" alt="مدل مردانه با استایل زیتونی و کرم در فضای آفتابی" fetchpriority="high" decoding="async">
            <div class="hero-slide-shade"></div><div class="hero-slide-copy"><span class="hero-slide-eyebrow">SHAHBAZ / MEN</span><h2>استایل ماندگار،<br><em>برای هر روز تو</em></h2><p>انتخاب‌های مردانه با ترکیبی از راحتی، کیفیت و سادگی.</p><a class="hero-slide-cta" href="${route('men/')}">مشاهده کالکشن مردانه ${icon('arrow')}</a></div><span class="hero-slide-index">۰۱ <i></i> ۰۴</span>
          </article>
          <article class="hero-slide" data-hero-slide aria-hidden="true">
            <img src="${asset('assets/images/sliders/women.webp')}" alt="استایل زنانه با رنگ‌های کرم و بژ در نور طبیعی" loading="lazy" decoding="async">
            <div class="hero-slide-shade"></div><div class="hero-slide-copy"><span class="hero-slide-eyebrow">SHAHBAZ / WOMEN</span><h2>ظرافت در سادگی،<br><em>زیبایی در هر روز</em></h2><p>برای لحظه‌هایی که می‌خواهی راحت و خوش‌استایل باشی.</p><a class="hero-slide-cta" href="${route('women/')}">مشاهده کالکشن زنانه ${icon('arrow')}</a></div><span class="hero-slide-index">۰۲ <i></i> ۰۴</span>
          </article>
          <article class="hero-slide" data-hero-slide aria-hidden="true">
            <img src="${asset('assets/images/sliders/kids.webp')}" alt="استایل راحت و دوست‌داشتنی کودک در فضای روشن" loading="lazy" decoding="async">
            <div class="hero-slide-shade"></div><div class="hero-slide-copy"><span class="hero-slide-eyebrow">SHAHBAZ / KIDS</span><h2>برای دنیای پرجنب‌وجوش،<br><em>انتخاب‌های دوست‌داشتنی</em></h2><p>لباس‌های راحت برای بازی، کشف و لبخندهای هر روز.</p><a class="hero-slide-cta" href="${route('kids/')}">مشاهده کالکشن بچگانه ${icon('arrow')}</a></div><span class="hero-slide-index">۰۳ <i></i> ۰۴</span>
          </article>
          <article class="hero-slide" data-hero-slide aria-hidden="true">
            <img src="${asset('assets/images/sliders/bags-shoes.webp')}" alt="کیف دستی کرم و کتانی روشن در چیدمان مینیمال" loading="lazy" decoding="async">
            <div class="hero-slide-shade"></div><div class="hero-slide-copy"><span class="hero-slide-eyebrow">SHAHBAZ / ACCESSORIES</span><h2>جزئیات کوچک،<br><em>تفاوت‌های بزرگ</em></h2><p>کیف و کفش‌هایی برای کامل‌کردن انتخاب‌های روزمره.</p><a class="hero-slide-cta" href="${route('bags-shoes/')}">مشاهده کیف و کفش ${icon('arrow')}</a></div><span class="hero-slide-index">۰۴ <i></i> ۰۴</span>
          </article>
        </div>
        <div class="hero-slider-controls"><button class="hero-slider-arrow" type="button" data-hero-prev aria-label="اسلاید قبلی">${icon('arrow')}</button><div class="hero-slider-dots" role="group" aria-label="انتخاب اسلاید"><button type="button" data-hero-dot="0" class="is-active" aria-label="اسلاید مردانه" aria-pressed="true"></button><button type="button" data-hero-dot="1" aria-label="اسلاید زنانه" aria-pressed="false"></button><button type="button" data-hero-dot="2" aria-label="اسلاید بچگانه" aria-pressed="false"></button><button type="button" data-hero-dot="3" aria-label="اسلاید کیف و کفش" aria-pressed="false"></button></div><button class="hero-slider-arrow hero-slider-next" type="button" data-hero-next aria-label="اسلاید بعدی">${icon('arrow')}</button></div>
      </section>
      <section class="categories-section section-space container" id="categories" aria-labelledby="categories-title"><div class="section-heading centered"><span class="eyebrow">مسیر انتخاب تو</span><h2 id="categories-title">دنبال چه چیزی هستی؟</h2><p>دسته موردنظرت رو انتخاب کن و دنیای شهباز رو کشف کن.</p></div><div class="category-grid">${categoryCard('men')}${categoryCard('women')}${categoryCard('kids')}${categoryCard('bags-shoes')}</div></section>
      
      <!-- بنر معرفی فروشگاه -->
      <section class="about-store-banner" id="about">
        <div class="about-store-wrapper">
          <!-- بخش متن‌ها (سمت راست) -->
          <div class="about-store-content">
            <h2 class="about-title">فروشگاه شهباز</h2>
            <h3 class="about-subtitle">ترکیبی از کیفیت، تنوع و اعتماد</h3>
            <p class="about-desc">ما در شهباز با ارائه جدیدترین مدل‌ها و متنوع‌ترین محصولات، همیشه در کنار شما هستیم تا تجربه‌ای متفاوت و لذت‌بخش از خرید داشته باشید.</p>
            
            <div class="about-features">
              <!-- ویژگی 1 -->
              <div class="feature-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                  <path d="M3 18a3 3 0 0 0 3 3h1a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H5a2 2 0 0 0-2 2zm18 0a3 3 0 0 1-3 3h-1a1 1 0 0 1-1-1v-4a1 1 0 0 1 1-1h2a2 2 0 0 1 2 2z"></path>
                </svg>
                <span>پشتیبانی حرفه‌ای</span>
              </div>
              <span class="feature-divider"></span>
              
              <!-- ویژگی 2 -->
              <div class="feature-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 2L14.2 8.2L21 10L14.2 11.8L12 18L9.8 11.8L3 10L9.8 8.2L12 2Z"></path>
                  <path d="M19 15L20.2 18.3L23 19L20.2 19.8L19 23L17.8 19.8L15 19L17.8 18.3L19 15Z"></path>
                  <path d="M5 1L6 3.5L8 4L6 4.8L5 7L4 4.8L2 4L4 3.5L5 1Z"></path>
                </svg>
                <span>تنوع بی‌نظیر</span>
              </div>
              <span class="feature-divider"></span>
              
              <!-- ویژگی 3 -->
              <div class="feature-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"></path>
                  <path d="m9 12 2 2 4-4"></path>
                </svg>
                <span>خرید مطمئن</span>
              </div>
            </div>
          </div>

          <!-- بخش عکس (سمت چپ) -->
          <div class="about-store-image">
            <img src="${asset('assets/images/editorial/family-story.webp')}" alt="فروشگاه شهباز" loading="lazy" data-fallback="${asset('assets/images/fallback-fashion.svg')}">
          </div>
        </div>
      </section>

      <!-- بخش تک‌مونده‌ها -->
      <section class="last-drops-section container" id="last-drops" aria-labelledby="last-drops-title">
        <div class="last-drops-layout">
          <aside class="last-drops-intro">
            <span class="last-drops-kicker">SHAHBAZ · LIMITED PIECES</span>
            <h2 id="last-drops-title">تک‌مونده‌ها</h2>
            <p>فرصتی بی‌نظیر برای استایل خاص و متفاوت؛ از بعضی انتخاب‌ها فقط یک عدد باقی مانده.</p>
            <a class="last-drops-all" href="${route('search/?remaining=1')}">مشاهده همه <span>+</span></a>
            <small>وقتی تمام شوند، دیگر تکرار نمی‌شوند.</small>
          </aside>
          <div class="last-drops-rail-shell">
            <div class="last-drops-rail-toolbar">
              <span class="last-drops-count">${number(CATALOG.filter((product) => Number(product.stockRemaining) === 1).length)} انتخاب محدود</span>
              <div class="last-drops-slider-controls" aria-label="کنترل حرکت محصولات">
                <button type="button" data-last-drops-prev aria-label="نمایش محصولات قبلی">${icon('arrow')}</button>
                <button type="button" data-last-drops-next aria-label="نمایش محصولات بعدی">${icon('arrow')}</button>
              </div>
            </div>
            <div class="last-drops-viewport" id="last-drops-viewport" tabindex="0" role="region" aria-label="محصولاتی که فقط یک عدد از آن‌ها باقی مانده است">
              <div class="last-drops-track">
                ${CATALOG.filter((product) => Number(product.stockRemaining) === 1).map((product) => productCard(product, 'last-drops-product', 'فقط ۱ عدد باقی‌مانده')).join('')}
              </div>
            </div>
            <div class="last-drops-scroll-hint"><span>برای دیدن محصولات بیشتر، افقی حرکت کن</span><i></i></div>
          </div>
        </div>
        <p class="sample-note">موجودی این محصولات در نسخه نمایشی تنظیم شده است؛ در نسخه نهایی باید تعداد واقعی از سامانه انبار دریافت شود.</p>
      </section>

      <!-- بخش جدید چرا شهباز (زیر تک‌مونده‌ها) -->
      <section class="why-shahbaz-banner container" aria-labelledby="why-title">
        <div class="why-shahbaz-inner">
          <div class="why-item">
            <div class="why-icon">${icon('return')}</div>
            <div class="why-text">
              <h3 id="why-title">چرا شهباز؟</h3>
              <p>انتخابی مطمئن برای خرید لذت‌بخش</p>
            </div>
          </div>
          <div class="why-divider"></div>
          <div class="why-item">
            <div class="why-icon">${icon('truck')}</div>
            <div class="why-text">
              <h3>ارسال سریع</h3>
              <p>به سراسر کشور</p>
            </div>
          </div>
          <div class="why-divider"></div>
          <div class="why-item">
            <div class="why-icon">${icon('shield')}</div>
            <div class="why-text">
              <h3>تضمین اصالت کالا</h3>
              <p>تضمین کیفیت محصولات</p>
            </div>
          </div>
          <div class="why-divider"></div>
          <div class="why-item">
            <div class="why-icon">${icon('headset')}</div>
            <div class="why-text">
              <h3>پشتیبانی مشتریان</h3>
              <p>پاسخگویی ۲۴ ساعته</p>
            </div>
          </div>
        </div>
      </section>
      `;
  }

  function renderCategory() {
    const category = CATEGORIES[categoryKey];
    if (!category) { renderNotFound(); return; }
    const products = CATALOG.filter((product) => product.category === categoryKey);
    const productLinks = category.features.map((label, index) => `<span class="category-chip ${index === 0 ? 'is-active' : ''}">${label}</span>`).join('');
    $('#page-main').innerHTML = `
      <div class="container breadcrumb"><a href="${route('')}">خانه</a>${icon('arrow')}<span>${category.title}</span></div>
      <section class="category-hero-art container" aria-label="${escapeHTML(category.title)}"><h1 class="visually-hidden">${escapeHTML(category.title)}</h1><img src="${imagePath(category.image)}" alt="${escapeHTML(category.alt)}" fetchpriority="high" decoding="async" data-fallback="${asset('assets/images/fallback-fashion.svg')}"></section>
      <section class="listing-section container" aria-labelledby="listing-title"><div class="listing-heading"><div><span class="eyebrow">مجموعه شهباز</span><h2 id="listing-title">${category.title}</h2><p>انتخاب‌های این دسته را با حوصله مرور کن.</p></div><div class="listing-sort"><label for="product-sort">مرتب‌سازی</label><select id="product-sort"><option value="featured">پیشنهاد شهباز</option><option value="price-asc">ارزان‌ترین</option><option value="price-desc">گران‌ترین</option><option value="discount">بیشترین تخفیف</option></select></div></div><div class="listing-toolbar"><div class="category-chips">${productLinks}</div><span id="listing-count">${number(products.length)} انتخاب</span></div><div class="product-grid listing-grid" id="listing-products">${products.map(productCard).join('')}</div></section>`;
  }

  function renderWishlistPage() {
    const products = wishlist.map((id) => productById.get(id)).filter(Boolean);
    $('#page-main').innerHTML = `<div class="container breadcrumb"><a href="${route('')}">خانه</a>${icon('arrow')}<span>علاقه‌مندی‌های من</span></div><section class="utility-page container"><div class="utility-heading"><span class="eyebrow">برای بعد نگه دار</span><h1>علاقه‌مندی‌های من</h1><p>انتخاب‌هایی که دوست داری دوباره به آن‌ها سر بزنی.</p></div><div id="wishlist-page-content">${products.length ? `<div class="product-grid">${products.map(productCard).join('')}</div>` : `<div class="large-empty-state"><span>${icon('heart')}</span><h2>هنوز چیزی ذخیره نکردی</h2><p>با لمس نشان قلب روی هر محصول، آن را به فهرست علاقه‌مندی‌ها اضافه کن.</p><a class="button button-dark" href="${route('')}">رفتن به صفحه اصلی</a></div>`}</div></section>`;
  }

  function renderCartPage() {
    const items = getCartProducts();
    $('#page-main').innerHTML = `<div class="container breadcrumb"><a href="${route('')}">خانه</a>${icon('arrow')}<span>سبد خرید</span></div><section class="utility-page container"><div class="utility-heading"><span class="eyebrow">انتخاب‌های تو</span><h1>سبد خرید</h1><p>پیش از ادامه، تعداد و انتخاب‌هایت را بررسی کن.</p></div><div id="cart-page-content">${items.length ? `<div class="cart-page-layout"><div class="cart-page-items">${items.map(cartRow).join('')}</div><aside class="order-summary"><h2>خلاصه سفارش</h2><div><span>تعداد کالا</span><strong>${number(cartQuantity())}</strong></div><div><span>جمع کالاها</span><strong>${price(cartTotal())}</strong></div><p>هزینه ارسال پس از اتصال روش‌های ارسال محاسبه خواهد شد.</p><button class="button button-dark button-full" type="button" data-checkout>ادامه فرایند خرید</button><small>ثبت سفارش و پرداخت آنلاین در نسخه فعلی فعال نیست.</small></aside></div>` : `<div class="large-empty-state"><span>${icon('bag')}</span><h2>سبد خریدت خالیه</h2><p>هنوز محصولی به سبد اضافه نکردی. از دسته‌بندی‌ها شروع کن.</p><a class="button button-dark" href="${route('')}#categories">رفتن به دسته‌بندی‌ها</a></div>`}</div></section>`;
  }

  function cartRow(product) {
    return `<article class="cart-page-row"><a class="cart-page-image" href="${route(`product.html?id=${product.id}`)}"><img src="${escapeHTML(imagePath(product.image))}" alt="${escapeHTML(product.alt)}" data-fallback="${asset('assets/images/fallback-fashion.svg')}"></a><div class="cart-page-info"><a href="${route(`product.html?id=${product.id}`)}"><h3>${escapeHTML(product.title)}</h3></a><span>${escapeHTML(product.categoryLabel)}${product.size || product.color ? ` · ${escapeHTML([product.size, product.color].filter(Boolean).join(' / '))}` : ''}</span><strong>${price(product.price)}</strong><div class="quantity-control"><button type="button" data-cart-action="increase" data-cart-key="${escapeHTML(product.key)}" aria-label="افزایش تعداد">${icon('plus')}</button><b>${number(product.quantity)}</b><button type="button" data-cart-action="decrease" data-cart-key="${escapeHTML(product.key)}" aria-label="کاهش تعداد">${icon('minus')}</button></div></div><button class="remove-item" type="button" data-cart-action="remove" data-cart-key="${escapeHTML(product.key)}" aria-label="حذف محصول">${icon('trash')}</button></article>`;
  }

  function renderAccountPage() {
    const profile = readStorage(KEYS.profile, { name:'', phone:'', email:'' }) || {};
    $('#page-main').innerHTML = `<div class="container breadcrumb"><a href="${route('')}">خانه</a>${icon('arrow')}<span>حساب کاربری</span></div>
      <section class="account-page container"><div class="utility-heading"><span class="eyebrow">فضای شخصی تو</span><h1>حساب کاربری</h1><p>اطلاعات پایه‌ات را در این مرورگر نگه دار و دسترسی سریع‌تری به انتخاب‌هایت داشته باش.</p></div>
      <div class="account-layout"><aside class="account-sidebar"><div class="account-avatar">${icon('user')}</div><strong>${escapeHTML(profile.name || 'کاربر مهمان')}</strong><span class="guest-label">حساب مهمان · بدون ورود</span><nav><a class="is-active" href="#profile-form">اطلاعات حساب ${icon('user')}</a><a href="${route('cart/')}">سفارش و سبد خرید ${icon('bag')}</a><a href="${route('wishlist/')}">علاقه‌مندی‌ها ${icon('heart')}</a></nav></aside>
      <div class="account-main"><div class="account-notice"><span>${icon('spark')}</span><div><strong>فعلاً در حالت مهمان هستی</strong><p>ورود پیامکی و مدیریت سفارش‌ها پس از اتصال سامانه کاربران فعال می‌شود. اطلاعات زیر فقط در همین مرورگر ذخیره خواهد شد.</p></div></div>
      <form class="profile-form" id="profile-form"><div class="form-heading"><div><h2>اطلاعات شخصی</h2><p>مشخصات دلخواهت را وارد یا ویرایش کن.</p></div><span>${icon('edit')}</span></div><div class="form-grid"><label>نام و نام خانوادگی<input name="name" type="text" maxlength="70" autocomplete="name" placeholder="نام و نام خانوادگی" value="${escapeHTML(profile.name || '')}"></label><label>شماره همراه<input name="phone" type="tel" inputmode="tel" maxlength="15" autocomplete="tel" placeholder="۰۹۱۲۱۲۳۴۵۶۷" value="${escapeHTML(profile.phone || '')}"></label><label class="full-field">نشانی ایمیل<input name="email" type="email" maxlength="100" autocomplete="email" placeholder="name@example.com" value="${escapeHTML(profile.email || '')}"></label></div><div class="form-actions"><button class="button button-dark" type="submit">ذخیره اطلاعات ${icon('check')}</button><span id="profile-save-note">اطلاعات فقط روی همین دستگاه ذخیره می‌شود.</span></div></form>
      <div class="account-shortcuts"><a href="${route('cart/')}"><span>${icon('bag')}</span><strong>سبد خرید</strong><small id="account-cart-summary">${number(cartQuantity())} کالا در سبد</small>${icon('arrow')}</a><a href="${route('wishlist/')}"><span>${icon('heart')}</span><strong>علاقه‌مندی‌ها</strong><small id="account-wishlist-summary">${number(wishlist.length)} محصول ذخیره‌شده</small>${icon('arrow')}</a></div>
      </div></div></section>`;
  }

  function renderSearchPage() {
    const params = new URLSearchParams(window.location.search);
    const query = params.get('q') || '';
    const saleOnly = params.get('sale') === '1';
    const remainingOnly = params.get('remaining') === '1';
    const normalized = query.trim().toLocaleLowerCase('fa-IR');
    const results = CATALOG.filter((product) => (saleOnly ? product.oldPrice > product.price : true) && (remainingOnly ? Number(product.stockRemaining) === 1 : true) && (!normalized || `${product.title} ${product.categoryLabel} ${product.description}`.toLocaleLowerCase('fa-IR').includes(normalized)));
    $('#page-main').innerHTML = `<div class="container breadcrumb"><a href="${route('')}">خانه</a>${icon('arrow')}<span>${remainingOnly ? 'تک‌مونده‌ها' : 'جست‌وجو'}</span></div><section class="utility-page container"><div class="utility-heading"><span class="eyebrow">${remainingOnly ? 'فرصت‌های محدود' : 'جست‌وجوی شهباز'}</span><h1>${remainingOnly ? 'تک‌مونده‌ها' : 'نتایج جست‌وجو'}</h1><p>${remainingOnly ? 'محصولاتی که فقط یک عدد از آن‌ها باقی مانده است.' : saleOnly ? 'انتخاب‌های دارای تخفیف' : normalized ? `نتیجه برای «${escapeHTML(query)}»` : 'عبارت موردنظرت را جست‌وجو کن.'}</p></div><form class="search-page-form" id="search-page-form"><input type="search" name="q" value="${escapeHTML(query)}" placeholder="نام محصول یا دسته‌بندی..."><button class="button button-dark" type="submit">جست‌وجو ${icon('search')}</button></form>${results.length ? `<div class="product-grid">${results.map((product) => productCard(product, '', remainingOnly ? 'فقط ۱ عدد باقی‌مانده' : '')).join('')}</div>` : `<div class="large-empty-state"><span>${icon('search')}</span><h2>نتیجه‌ای پیدا نشد</h2><p>عبارت دیگری امتحان کن یا از دسته‌بندی‌ها کمک بگیر.</p><a class="button button-dark" href="${route('')}">بازگشت به خانه</a></div>`}</section>`;
    const field = $('#site-search'); if (field) field.value = query;
  }

  function renderProductPage() {
    const id = new URLSearchParams(window.location.search).get('id');
    const product = productById.get(id);
    if (!product) { renderNotFound(); return; }
    document.title = `${product.title} | شهباز`;
    const saved = wishlist.includes(product.id);
    const more = CATALOG.filter((item) => item.category === product.category && item.id !== product.id).slice(0, 3);
    $('#page-main').innerHTML = `<div class="container breadcrumb"><a href="${route('')}">خانه</a>${icon('arrow')}<a href="${route(`${product.category}/`)}">${product.categoryLabel}</a>${icon('arrow')}<span>${escapeHTML(product.title)}</span></div><section class="product-detail container" data-product-detail><div class="product-detail-image"><img src="${escapeHTML(imagePath(product.image))}" alt="${escapeHTML(product.alt)}" fetchpriority="high" data-fallback="${asset('assets/images/fallback-fashion.svg')}"></div><div class="product-detail-copy"><span class="eyebrow">${escapeHTML(product.categoryLabel)}</span><h1>${escapeHTML(product.title)}</h1><p class="product-detail-description">${escapeHTML(product.description)}</p><div class="detail-price"><strong>${price(product.price)}</strong>${product.oldPrice ? `<del>${number(product.oldPrice)} تومان</del><span>${number(discount(product))}٪ تخفیف</span>` : ''}</div><div class="detail-option"><span>رنگ‌های موجود</span><div class="color-options">${product.colors.map((color, i) => `<button type="button" class="color-chip ${i===0?'is-selected':''}" data-color-choice data-color-value="${escapeHTML(color)}" aria-pressed="${i===0}">${escapeHTML(color)}</button>`).join('')}</div></div><div class="detail-option"><label for="product-size">انتخاب سایز</label><select id="product-size">${product.sizes.map((size) => `<option value="${escapeHTML(size)}">${escapeHTML(size)}</option>`).join('')}</select></div><div class="detail-actions"><button type="button" class="button button-dark" data-add-to-cart data-variant-add data-id="${product.id}">افزودن به سبد خرید ${icon('bag')}</button><button type="button" class="detail-wishlist ${saved?'is-saved':''}" data-wishlist-button data-id="${product.id}" aria-pressed="${saved}">${icon('heart')} ${saved?'ذخیره‌شده':'افزودن به علاقه‌مندی'}</button></div><div class="detail-trust"><span>${icon('shield')} کیفیت و مشخصات شفاف</span><span>${icon('return')} بازگشت طبق قوانین فروشگاه</span></div><p class="sample-note">اطلاعات این محصول برای نسخه نمایشی است؛ موجودی، سایزبندی و قیمت نهایی باید از سامانه فروش دریافت شود.</p></div></section>${more.length?`<section class="related-section container"><div class="section-heading"><span class="eyebrow">انتخاب‌های مشابه</span><h2>شاید این‌ها را هم دوست داشته باشی</h2></div><div class="product-grid">${more.map(productCard).join('')}</div></section>`:''}`;
  }

  function renderNotFound() {
    $('#page-main').innerHTML = `<section class="large-empty-state not-found container"><span>${icon('search')}</span><h1>این صفحه پیدا نشد</h1><p>ممکن است نشانی تغییر کرده باشد. از صفحه اصلی دوباره شروع کن.</p><a class="button button-dark" href="${route('')}">رفتن به صفحه اصلی</a></section>`;
  }

  function renderPage() {
    if (page === 'home') renderHome();
    else if (page === 'category') renderCategory();
    else if (page === 'wishlist') renderWishlistPage();
    else if (page === 'cart') renderCartPage();
    else if (page === 'account') renderAccountPage();
    else if (page === 'search') renderSearchPage();
    else if (page === 'product') renderProductPage();
    else renderNotFound();
  }

  function renderCartDrawer() {
    const list = $('#cart-items');
    if (!list) return;
    const items = getCartProducts();
    $('#cart-empty-state').hidden = items.length > 0;
    $('#cart-drawer-footer').hidden = items.length === 0;
    list.innerHTML = items.map((item) => `
      <article class="drawer-product-row"><a class="drawer-product-image" href="${route(`product.html?id=${item.id}`)}"><img src="${escapeHTML(imagePath(item.image))}" alt="${escapeHTML(item.alt)}" data-fallback="${asset('assets/images/fallback-fashion.svg')}"></a><div class="drawer-product-info"><h3><a href="${route(`product.html?id=${item.id}`)}">${escapeHTML(item.title)}</a></h3><strong>${price(item.price)}</strong>${item.size || item.color ? `<small class="variant-note">${escapeHTML([item.size, item.color].filter(Boolean).join(' / '))}</small>` : ''}<div class="quantity-control"><button type="button" data-cart-action="increase" data-cart-key="${escapeHTML(item.key)}" aria-label="افزایش تعداد">${icon('plus')}</button><b>${number(item.quantity)}</b><button type="button" data-cart-action="decrease" data-cart-key="${escapeHTML(item.key)}" aria-label="کاهش تعداد">${icon('minus')}</button></div></div><button class="remove-item" type="button" data-cart-action="remove" data-cart-key="${escapeHTML(item.key)}" aria-label="حذف ${escapeHTML(item.title)}">${icon('trash')}</button></article>`).join('');
    $('#cart-subtotal-value').textContent = price(cartTotal());
    $('#cart-title-count').textContent = `(${number(cartQuantity())})`;
  }
  function renderWishlistDrawer() {
    const list = $('#wishlist-items');
    if (!list) return;
    const products = wishlist.map((id) => productById.get(id)).filter(Boolean);
    $('#wishlist-empty-state').hidden = products.length > 0;
    $('#wishlist-drawer-footer').hidden = products.length === 0;
    $('#wishlist-title-count').textContent = `(${number(products.length)})`;
    list.innerHTML = products.map((product) => `<article class="drawer-product-row"><a class="drawer-product-image" href="${route(`product.html?id=${product.id}`)}"><img src="${escapeHTML(imagePath(product.image))}" alt="${escapeHTML(product.alt)}" data-fallback="${asset('assets/images/fallback-fashion.svg')}"></a><div class="drawer-product-info"><h3><a href="${route(`product.html?id=${product.id}`)}">${escapeHTML(product.title)}</a></h3><strong>${price(product.price)}</strong><button class="wishlist-add-cart" type="button" data-wishlist-add-cart data-id="${product.id}">افزودن به سبد خرید</button></div><button class="remove-item" type="button" data-wishlist-remove data-id="${product.id}" aria-label="حذف از علاقه‌مندی‌ها">${icon('close')}</button></article>`).join('');
  }
  function updateCounters() {
    $$('.cart-count').forEach((element) => {
      element.textContent = number(cartQuantity());
      element.setAttribute('aria-label', `${number(cartQuantity())} کالا در سبد خرید`);
    });
    const wishCount = $('#wishlist-count'); if (wishCount) wishCount.textContent = number(wishlist.length);
    const accountCart = $('#account-cart-summary'); if (accountCart) accountCart.textContent = `${number(cartQuantity())} کالا در سبد`;
    const accountWish = $('#account-wishlist-summary'); if (accountWish) accountWish.textContent = `${number(wishlist.length)} محصول ذخیره‌شده`;
    $$('.product-wishlist[data-id]').forEach((button) => {
      const saved = wishlist.includes(button.dataset.id);
      button.classList.toggle('is-saved', saved);
      button.setAttribute('aria-pressed', String(saved));
      button.setAttribute('aria-label', saved ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها');
    });
    const titleCount = $('#cart-title-count'); if (titleCount) titleCount.textContent = `(${number(cartQuantity())})`;
  }
  function refreshStateViews() {
    renderCartDrawer();
    renderWishlistDrawer();
    updateCounters();
    if (page === 'wishlist') renderWishlistPage();
    if (page === 'cart') renderCartPage();
  }

  function openDrawer(name) {
    const drawer = $(`#${name}-drawer`);
    const overlay = $('#drawer-overlay');
    if (!drawer || !overlay) return;
    closeMobileMenu(); closeSettings();
    if (drawerOpen) closeDrawer(false);
    returnFocus = document.activeElement;
    drawerOpen = true;
    overlay.hidden = false;
    requestAnimationFrame(() => overlay.classList.add('is-visible'));
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    document.body.classList.add('lock-scroll');
    $('.drawer-close', drawer)?.focus();
  }
  function closeDrawer(restore = true) {
    if (!drawerOpen) return;
    $$('.side-drawer').forEach((drawer) => { drawer.classList.remove('is-open'); drawer.setAttribute('aria-hidden', 'true'); });
    const overlay = $('#drawer-overlay');
    overlay?.classList.remove('is-visible');
    setTimeout(() => { if (!drawerOpen && overlay) overlay.hidden = true; }, 260);
    drawerOpen = false;
    document.body.classList.remove('lock-scroll');
    if (restore && returnFocus instanceof HTMLElement) returnFocus.focus();
  }
  function openMobileMenu() {
    if (mobileMenuOpen) return;
    closeDrawer(false); closeSettings();
    mobileMenuOpen = true;
    const menu = $('#mobile-menu'); const overlay = $('#mobile-menu-overlay');
    if (!menu || !overlay) return;
    overlay.hidden = false; requestAnimationFrame(() => overlay.classList.add('is-visible'));
    menu.classList.add('is-open'); menu.setAttribute('aria-hidden', 'false');
    $('#mobile-menu-trigger')?.setAttribute('aria-expanded', 'true');
    document.body.classList.add('lock-scroll');
    $('#mobile-menu-close')?.focus();
  } 
  function closeMobileMenu() {
    if (!mobileMenuOpen) return;
    mobileMenuOpen = false;
    const menu = $('#mobile-menu'); const overlay = $('#mobile-menu-overlay');
    menu?.classList.remove('is-open'); menu?.setAttribute('aria-hidden', 'true');
    overlay?.classList.remove('is-visible');
    setTimeout(() => { if (!mobileMenuOpen && overlay) overlay.hidden = true; }, 260);
    $('#mobile-menu-trigger')?.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('lock-scroll');
  }
  function closeSettings() { settingsOpen = false; const panel = $('#settings-popover'); if (panel) panel.hidden = true; }
  function toggleSettings() {
    closeDrawer(false); closeMobileMenu();
    settingsOpen = !settingsOpen;
    const panel = $('#settings-popover'); if (panel) panel.hidden = !settingsOpen;
  }

  function addToCart(id, options = {}) {
    const product = productById.get(id);
    if (!product) { toast('این محصول در کاتالوگ پیدا نشد.'); return; }
    const variant = { size:String(options.size || ''), color:String(options.color || '') };
    const line = cart.find((item) => item.id === id && (item.size || '') === variant.size && (item.color || '') === variant.color);
    if (line) line.quantity = Math.min(99, line.quantity + 1);
    else cart.push({ id, quantity: 1, ...variant });
    saveCart(); refreshStateViews();
    toast(`${product.title} به سبد خرید اضافه شد.`);
    openDrawer('cart');
  }
  function changeCart(key, action) {
    const line = cart.find((item) => lineKey(item) === key || item.id === key);
    if (!line) return;
    if (action === 'increase') line.quantity = Math.min(99, line.quantity + 1);
    if (action === 'decrease') line.quantity -= 1;
    if (action === 'remove' || line.quantity <= 0) cart = cart.filter((item) => item !== line);
    saveCart(); refreshStateViews();
  }
  function toggleWishlist(id) {
    const product = productById.get(id); if (!product) return;
    if (wishlist.includes(id)) { wishlist = wishlist.filter((item) => item !== id); toast(`${product.title} از علاقه‌مندی‌ها حذف شد.`); }
    else { wishlist.push(id); toast(`${product.title} به علاقه‌مندی‌ها اضافه شد.`); }
    saveWishlist(); refreshStateViews();
  }

  function bindGlobalEvents() {
    document.addEventListener('click', (event) => {
      const lastDropsControl = event.target.closest('[data-last-drops-prev], [data-last-drops-next]');
      if (lastDropsControl) {
        const viewport = $('#last-drops-viewport');
        if (viewport) {
          const isNext = lastDropsControl.hasAttribute('data-last-drops-next');
          const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
          viewport.scrollBy({ left: isNext ? -Math.max(260, viewport.clientWidth * 0.78) : Math.max(260, viewport.clientWidth * 0.78), behavior: reduceMotion ? 'auto' : 'smooth' });
        }
        return;
      }
      const addButton = event.target.closest('[data-add-to-cart]');
      if (addButton) {
        const id = addButton.dataset.id || addButton.closest('[data-id]')?.dataset.id;
        const detail = addButton.closest('[data-product-detail]');
        const options = detail
          ? { size:$('#product-size', detail)?.value || '', color:$('.color-chip.is-selected', detail)?.dataset.colorValue || '' }
          : { size:addButton.dataset.size || '', color:addButton.dataset.color || '' };
        addToCart(id, options); return;
      }
      const wishButton = event.target.closest('[data-wishlist-button]');
      if (wishButton) { toggleWishlist(wishButton.dataset.id); return; }
      const cartAction = event.target.closest('[data-cart-action]');
      if (cartAction) { changeCart(cartAction.dataset.cartKey || cartAction.dataset.id, cartAction.dataset.cartAction); return; }
      const removeWish = event.target.closest('[data-wishlist-remove]');
      if (removeWish) { toggleWishlist(removeWish.dataset.id); return; }
      const addWishToCart = event.target.closest('[data-wishlist-add-cart]');
      if (addWishToCart) { addToCart(addWishToCart.dataset.id); return; }
      const close = event.target.closest('[data-close-drawer]');
      if (close) { closeDrawer(); return; }
      const bottom = event.target.closest('[data-bottom-action]');
      if (bottom) {
        const action = bottom.dataset.bottomAction;
        if (action === 'menu') openMobileMenu();
        if (action === 'cart') openDrawer('cart');
        if (action === 'settings') toggleSettings();
        return;
      }
      if (event.target.closest('#cart-trigger, #mobile-cart-trigger')) { openDrawer('cart'); return; }
      if (event.target.closest('#wishlist-trigger')) { openDrawer('wishlist'); return; }
      if (event.target.closest('#mobile-menu-trigger')) { openMobileMenu(); return; }
      if (event.target.closest('#mobile-menu-close')) { closeMobileMenu(); return; }
      if (event.target.closest('#mobile-menu-overlay')) { closeMobileMenu(); return; }
      if (event.target.closest('#drawer-overlay')) { closeDrawer(); return; }
      const feature = event.target.closest('[data-feature-filter]');
      if (feature) { $$('.category-chip').forEach((button) => button.classList.toggle('is-active', button === feature)); return; }
      const color = event.target.closest('[data-color-choice]');
      if (color) { $$('.color-chip').forEach((button) => { button.classList.toggle('is-selected', button === color); button.setAttribute('aria-pressed', String(button === color)); }); return; }
      if (settingsOpen && !event.target.closest('#settings-popover')) closeSettings();
    });

    $('#search-form')?.addEventListener('submit', (event) => {
      event.preventDefault(); const query = $('#site-search')?.value.trim() || '';
      navigateSoftly(`${route('search/')}?q=${encodeURIComponent(query)}`);
    });
    $('#search-page-form')?.addEventListener('submit', (event) => {
      event.preventDefault(); const query = new FormData(event.currentTarget).get('q') || '';
      navigateSoftly(`${route('search/')}?q=${encodeURIComponent(query)}`);
    });
    $('#profile-form')?.addEventListener('submit', (event) => {
      event.preventDefault(); const form = new FormData(event.currentTarget);
      const profile = { name:String(form.get('name') || '').trim(), phone:String(form.get('phone') || '').trim(), email:String(form.get('email') || '').trim() };
      if (profile.phone && !/^[+\d۰-۹٠-٩\s()-]{7,15}$/.test(profile.phone)) { toast('شماره همراه را بررسی کن.'); return; }
      if (profile.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(profile.email)) { toast('نشانی ایمیل معتبر نیست.'); return; }
      writeStorage(KEYS.profile, profile);
      const name = $('.account-sidebar > strong'); if (name) name.textContent = profile.name || 'کاربر مهمان';
      toast('اطلاعات حساب در همین مرورگر ذخیره شد.');
      const note = $('#profile-save-note'); if (note) note.textContent = 'آخرین تغییرات ذخیره شد.';
    });
    $('#product-sort')?.addEventListener('change', (event) => {
      const grid = $('#listing-products'); if (!grid) return;
      const items = CATALOG.filter((product) => product.category === categoryKey);
      const sort = event.target.value;
      if (sort === 'price-asc') items.sort((a,b) => a.price - b.price);
      else if (sort === 'price-desc') items.sort((a,b) => b.price - a.price);
      else if (sort === 'discount') items.sort((a,b) => discount(b) - discount(a));
      grid.innerHTML = items.map(productCard).join('');
    });
    $('#checkout-button')?.addEventListener('click', () => toast('برای پرداخت واقعی، اتصال درگاه و بک‌اند سفارش لازم است.'));
    document.addEventListener('click', (event) => {
      if (event.target.closest('[data-checkout]')) toast('برای پرداخت واقعی، اتصال درگاه و بک‌اند سفارش لازم است.');
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') { closeDrawer(); closeMobileMenu(); closeSettings(); }
      if (event.key === 'Tab' && drawerOpen) {
        const drawer = $('.side-drawer.is-open');
        const focusable = $$('button:not([disabled]), a[href], input:not([disabled]), select:not([disabled])', drawer).filter((element) => element.offsetParent !== null);
        if (!focusable.length) return;
        const first = focusable[0], last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
      }
    });
  }

  function installImageFallbacks() {
    document.addEventListener('error', (event) => {
      const image = event.target;
      if (!(image instanceof HTMLImageElement) || !image.dataset.fallback || image.dataset.fallbackApplied) return;
      image.dataset.fallbackApplied = 'true';
      image.src = image.dataset.fallback;
    }, true);
  }

  function setupPageMotion() {
    const main = $('#page-main');
    if (!main) return;
    main.classList.add('page-motion-enter');
    document.documentElement.classList.add('motion-ready');

    const selectors = [
      '.section-heading', '.section-heading-row', '.category-card',
      '.about-store-wrapper', '.product-card',
      '.benefits-grid article', '.collection-banner', '.category-hero',
      '.listing-heading', '.listing-toolbar', '.account-sidebar', '.account-main',
      '.utility-heading', '.large-empty-state', '.product-detail-image',
      '.product-detail-copy', '.cart-page-row', '.order-summary',
      '.search-page-form', '.related-section .section-heading'
    ];
    const nodes = [...new Set(selectors.flatMap((selector) => $$(selector, main)))];
    if (!nodes.length) return;

    const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion || !('IntersectionObserver' in window)) {
      nodes.forEach((node) => node.classList.add('reveal-item', 'is-revealed'));
      return;
    }

    const observer = new IntersectionObserver((entries, currentObserver) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-revealed');
        currentObserver.unobserve(entry.target);
      });
    }, { root: null, rootMargin: '0px 0px -7% 0px', threshold: 0.08 });

    nodes.forEach((node, index) => {
      node.classList.add('reveal-item');
      node.style.setProperty('--reveal-delay', `${(index % 5) * 42}ms`);
      observer.observe(node);
    });
  }

  function navigateSoftly(destination) {
    const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion) { window.location.assign(destination); return; }
    document.body.classList.add('page-leaving');
    window.setTimeout(() => window.location.assign(destination), 155);
  }

  function installSoftNavigation() {
    document.addEventListener('click', (event) => {
      if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      const anchor = event.target.closest('a[href]');
      if (!anchor || anchor.hasAttribute('download') || anchor.target && anchor.target !== '_self' || anchor.rel === 'external') return;
      let destination;
      try { destination = new URL(anchor.href, window.location.href); } catch { return; }
      if (destination.origin !== window.location.origin) return;
      const rootPath = new URL(ROOT, window.location.href).pathname;
      if (!destination.pathname.startsWith(rootPath)) return;
      if (destination.pathname === window.location.pathname && destination.search === window.location.search && destination.hash) return;
      if (destination.href === window.location.href) return;
      event.preventDefault();
      navigateSoftly(destination.href);
    }, true);

    window.addEventListener('pageshow', () => document.body.classList.remove('page-leaving'));
  }

  renderHeader();
  renderFooter();
  renderOverlays();
  renderPage();
  setupPageMotion();
  installSoftNavigation();
  bindGlobalEvents();
  refreshStateViews();
  installImageFallbacks();

  const updateCopyrightYear = () => {
    const copyright = document.querySelector('[data-copyright]');
    if (!copyright) return;
    const currentYear = new Date().getFullYear();
    copyright.textContent = `© ${currentYear} تمامی حقوق محفوظ است. فروشگاه شاهباز.`;
  };
  updateCopyrightYear();

})();