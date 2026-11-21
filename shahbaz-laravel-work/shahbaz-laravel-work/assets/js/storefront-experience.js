/* =========================================================
   SHAHBAZ | Product discovery experience
   Live search, category filters, responsive filter modal,
   refined product-detail controls and size guidance.
   ========================================================= */
(() => {
  'use strict';

  const catalog = window.SHAHBAZ_CATALOG || [];
  const categories = window.SHAHBAZ_CATEGORIES || {};
  const page = document.body.dataset.page || '';
  const categoryKey = document.body.dataset.category || '';
  const root = document.body.dataset.root || './';
  const $ = (selector, scope = document) => scope.querySelector(selector);
  const $$ = (selector, scope = document) => [...scope.querySelectorAll(selector)];
  const money = new Intl.NumberFormat('fa-IR');
  const toFa = (value) => money.format(Number(value) || 0);
  const esc = (value = '') => String(value).replace(/[&<>"']/g, (char) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' })[char]);
  const route = (path) => `${root}${path}`;
  const productUrl = (id) => route(`product.html?id=${encodeURIComponent(id)}`);
  const normalize = (value = '') => String(value)
    .toLocaleLowerCase('fa-IR')
    .replace(/[يى]/g, 'ی').replace(/ك/g, 'ک')
    .replace(/[\u064B-\u065F\u0670\u200c\u200e\u200f]/g, '')
    .replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
    .replace(/[۰-۹]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
    .trim();

  function announce(message) {
    const region = $('#toast-region');
    if (!region) return;
    region.innerHTML = `<div class="toast-message" role="status">${esc(message)}</div>`;
    window.clearTimeout(announce.timer);
    announce.timer = window.setTimeout(() => region.replaceChildren(), 2800);
  }

  /* ---------------------- LIVE SEARCH ---------------------- */
  function setupLiveSearch() {
    const headerForm = $('#search-form');
    const headerInput = $('#site-search');
    const mount = $('#site-overlays');
    if (!headerForm || !headerInput || !mount) return;

    const backdrop = document.createElement('div');
    backdrop.className = 'search-glass';
    backdrop.id = 'search-glass';
    backdrop.hidden = true;
    backdrop.innerHTML = `
      <section class="search-glass-panel" role="dialog" aria-modal="true" aria-labelledby="live-search-title">
        <div class="search-glass-head">
          <div><span class="eyebrow">جست‌وجوی هوشمند شهباز</span><h2 id="live-search-title">چی توی ذهنته؟</h2></div>
          <button class="icon-button" type="button" data-search-close aria-label="بستن جست‌وجو">×</button>
        </div>
        <form class="search-glass-form" id="live-search-form" role="search">
          <span class="search-glass-icon" aria-hidden="true">⌕</span>
          <input id="live-search-input" type="search" autocomplete="off" placeholder="نام محصول، رنگ یا دسته‌بندی را بنویس..." aria-label="جست‌وجو در تمام محصولات">
          <button type="submit">مشاهده همه نتایج</button>
        </form>
        <div class="search-live-meta"><span id="search-live-caption">محصولات پیشنهادی</span><span>جست‌وجو در تمام محصولات شهباز</span></div>
        <div class="search-live-results" id="search-live-results" aria-live="polite"></div>
        <div class="search-glass-foot"><span>برای جست‌وجوی سریع، نام محصول یا بخشی از آن را وارد کن.</span><kbd>ESC</kbd></div>
      </section>`;
    mount.append(backdrop);

    const overlayInput = $('#live-search-input', backdrop);
    const resultsNode = $('#search-live-results', backdrop);
    const captionNode = $('#search-live-caption', backdrop);
    let active = false;
    let skipHeaderFocus = false;

    const productCard = (product) => `
      <a class="search-live-item" href="${esc(productUrl(product.id))}">
        <span class="search-live-thumb"><img src="${esc(route(product.image))}" alt="" loading="lazy" data-fallback="${esc(route('assets/images/fallback-fashion.svg'))}"></span>
        <span class="search-live-info"><small>${esc(product.categoryLabel)}</small><strong>${esc(product.title)}</strong><span>${toFa(product.price)} تومان</span></span>
        <span class="search-live-arrow" aria-hidden="true">←</span>
      </a>`;

    function showResults(raw) {
      const query = normalize(raw);
      const tokens = query.split(/\s+/).filter(Boolean);
      let matched = catalog;
      if (tokens.length) {
        matched = catalog.filter((product) => {
          const haystack = normalize([
            product.title, product.categoryLabel, product.description,
            product.tag, product.id, ...(product.colors || []), ...(product.sizes || [])
          ].join(' '));
          return tokens.every((token) => haystack.includes(token));
        });
      } else {
        matched = catalog.slice(0, 4);
      }
      const shown = matched.slice(0, 6);
      captionNode.textContent = tokens.length
        ? `${toFa(matched.length)} نتیجه برای «${raw.trim()}»`
        : 'محصولات پیشنهادی';
      if (!matched.length) {
        resultsNode.innerHTML = `<div class="search-live-empty"><span>⌕</span><strong>چیزی با این عبارت پیدا نکردیم.</strong><p>املای کلمه را بررسی کن یا عبارت ساده‌تری بنویس.</p></div>`;
        return;
      }
      resultsNode.innerHTML = shown.map(productCard).join('') + (matched.length > shown.length
        ? `<a class="search-live-more" href="${esc(route(`search/?q=${encodeURIComponent(raw.trim())}`))}">نمایش همه ${toFa(matched.length)} نتیجه ${'←'}</a>` : '');
    }

    function openSearch(value = '') {
      if (active) {
        if (value && overlayInput.value !== value) { overlayInput.value = value; showResults(value); }
        return;
      }
      active = true;
      backdrop.hidden = false;
      backdrop.classList.remove('is-closing');
      document.body.classList.add('search-open', 'lock-scroll');
      overlayInput.value = value || headerInput.value || '';
      showResults(overlayInput.value);
      requestAnimationFrame(() => overlayInput.focus({ preventScroll:true }));
    }
    function closeSearch(restoreFocus = false) {
      if (!active) return;
      active = false;
      backdrop.classList.add('is-closing');
      document.body.classList.remove('search-open', 'lock-scroll');
      window.setTimeout(() => { if (!active) { backdrop.hidden = true; backdrop.classList.remove('is-closing'); } }, 180);
      if (restoreFocus) {
        // بازگرداندن فوکوس نباید رویداد focus را به‌عنوان درخواست بازکردن دوباره تفسیر کند.
        skipHeaderFocus = true;
        headerInput.focus({ preventScroll:true });
        requestAnimationFrame(() => { skipHeaderFocus = false; });
      }
    }

    headerInput.addEventListener('focus', () => { if (!skipHeaderFocus) openSearch(headerInput.value); });
    headerInput.addEventListener('click', () => openSearch(headerInput.value));
    overlayInput.addEventListener('input', () => {
      headerInput.value = overlayInput.value;
      showResults(overlayInput.value);
    });
    backdrop.addEventListener('click', (event) => {
      if (event.target === backdrop || event.target.closest('[data-search-close]')) closeSearch(true);
    });
    $('#live-search-form', backdrop).addEventListener('submit', (event) => {
      event.preventDefault();
      const query = overlayInput.value.trim();
      window.location.href = route(`search/?q=${encodeURIComponent(query)}`);
    });
    headerForm.addEventListener('submit', () => {
      // جست‌وجوی کامل با Enter/دکمه، صفحه نتایج را باز می‌کند؛ تایپ عادی زنده می‌ماند.
      closeSearch(false);
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && active) { event.stopPropagation(); closeSearch(true); }
    }, true);
    // backdrop blur should be visible immediately, without a flash of sharp content
    window.shahbazOpenSearch = openSearch;
  }

  /* -------------------- CATEGORY FILTERS ------------------- */
  function setupCategoryFilters() {
    if (page !== 'category') return;
    const listing = $('.listing-section');
    const grid = $('#listing-products');
    const category = categories[categoryKey];
    if (!listing || !grid || !category) return;

    const products = catalog.filter((item) => item.category === categoryKey);
    const prices = products.map((item) => item.price);
    const minCatalogPrice = Math.min(...prices);
    const maxCatalogPrice = Math.max(...prices);
    const colors = [...new Set(products.flatMap((item) => item.colors || []))];
    const sizes = [...new Set(products.flatMap((item) => item.sizes || []))];
    const defaults = { minPrice:'', maxPrice:'', colors:[], sizes:[], saleOnly:false };
    let draft = structuredClone(defaults);
    let applied = structuredClone(defaults);

    const colorButtons = colors.map((color) => `<button type="button" class="filter-choice" data-filter-toggle data-filter-type="color" data-value="${esc(color)}" aria-pressed="false"><i class="filter-color-dot" data-color-name="${esc(color)}"></i><span>${esc(color)}</span></button>`).join('');
    const sizeButtons = sizes.map((size) => `<button type="button" class="filter-choice filter-size-choice" data-filter-toggle data-filter-type="size" data-value="${esc(size)}" aria-pressed="false">${esc(size)}</button>`).join('');
    const panelContent = `
      <div class="filter-panel-inner">
        <div class="filter-panel-heading"><div><span class="eyebrow">انتخاب دقیق‌تر</span><h3>فیلترهای محصولات</h3><p>نتایج را مطابق سلیقه‌ات محدود کن.</p></div><span class="filter-count-badge" data-filter-count>۰</span></div>
        <section class="filter-group"><h4>محدوده قیمت <small>تومان</small></h4><div class="filter-price-fields"><label><span>از</span><input type="number" min="0" inputmode="numeric" placeholder="حداقل" data-filter-price="minPrice" aria-label="حداقل قیمت"></label><i>—</i><label><span>تا</span><input type="number" min="0" inputmode="numeric" placeholder="حداکثر" data-filter-price="maxPrice" aria-label="حداکثر قیمت"></label></div><div class="filter-range-note">از ${toFa(minCatalogPrice)} تا ${toFa(maxCatalogPrice)} تومان</div></section>
        ${colors.length ? `<section class="filter-group"><h4>رنگ</h4><div class="filter-choice-list">${colorButtons}</div></section>` : ''}
        ${sizes.length ? `<section class="filter-group"><h4>سایز</h4><div class="filter-choice-list filter-size-list">${sizeButtons}</div></section>` : ''}
        <section class="filter-group filter-sale-group"><label class="filter-switch-row"><span><strong>فقط محصولات تخفیف‌دار</strong><small>انتخاب‌های دارای قیمت ویژه</small></span><input type="checkbox" data-filter-sale><i aria-hidden="true"></i></label></section>
        <div class="filter-panel-actions"><button class="filter-apply-button" type="button" data-filter-apply>اعمال فیلترها <span>←</span></button><button class="filter-reset-button" type="button" data-filter-reset>پاک کردن فیلترها</button></div>
      </div>`;

    const heading = $('.listing-heading', listing);
    const toolbar = $('.listing-toolbar', listing);
    const aside = document.createElement('aside');
    aside.className = 'category-filter-sidebar';
    aside.setAttribute('aria-label', 'فیلتر محصولات');
    aside.innerHTML = panelContent;
    const results = document.createElement('div');
    results.className = 'listing-results';
    if (toolbar) results.append(toolbar);
    results.append(grid);
    const layout = document.createElement('div');
    layout.className = 'listing-content-layout';
    layout.append(aside, results);
    listing.append(layout);

    const mobileTrigger = document.createElement('button');
    mobileTrigger.className = 'mobile-filter-trigger';
    mobileTrigger.type = 'button';
    mobileTrigger.innerHTML = `<span class="mobile-filter-trigger-icon" aria-hidden="true"><svg viewBox="0 0 20 20"><path d="M3 5h14M3 10h14M3 15h14"/><circle cx="7" cy="5" r="1.6"/><circle cx="13" cy="10" r="1.6"/><circle cx="8" cy="15" r="1.6"/></svg></span><span>فیلتر محصولات</span><b data-filter-count>۰</b>`;
    mobileTrigger.setAttribute('aria-haspopup', 'dialog');
    document.body.append(mobileTrigger);

    const modal = document.createElement('div');
    modal.className = 'filter-modal';
    modal.id = 'filter-modal';
    modal.hidden = true;
    modal.innerHTML = `<section class="filter-modal-panel" role="dialog" aria-modal="true" aria-labelledby="mobile-filter-title"><header class="filter-modal-head"><div><span class="eyebrow">شخصی‌سازی نتایج</span><h2 id="mobile-filter-title">فیلتر محصولات</h2></div><button type="button" class="icon-button" data-filter-close aria-label="بستن فیلترها">×</button></header><div class="filter-modal-content">${panelContent}</div><footer class="filter-modal-foot"><button class="filter-modal-apply" type="button" data-filter-apply>نمایش محصولات <span data-filter-result-count>${toFa(products.length)}</span></button><button type="button" data-filter-close class="filter-modal-cancel">انصراف</button></footer></section>`;
    $('#site-overlays')?.append(modal);

    const activeCount = (state) => state.colors.length + state.sizes.length + (state.minPrice !== '' ? 1 : 0) + (state.maxPrice !== '' ? 1 : 0) + (state.saleOnly ? 1 : 0);
    const sameState = () => JSON.stringify(draft) === JSON.stringify(applied);

    function syncControls() {
      $$('[data-filter-toggle]', listing).concat($$('[data-filter-toggle]', modal)).forEach((button) => {
        const type = button.dataset.filterType;
        const selected = type === 'color' ? draft.colors.includes(button.dataset.value) : draft.sizes.includes(button.dataset.value);
        button.classList.toggle('is-selected', selected);
        button.setAttribute('aria-pressed', String(selected));
      });
      $$('[data-filter-price]', listing).concat($$('[data-filter-price]', modal)).forEach((input) => {
        input.value = draft[input.dataset.filterPrice];
      });
      $$('[data-filter-sale]', listing).concat($$('[data-filter-sale]', modal)).forEach((input) => { input.checked = draft.saleOnly; });
      const changed = !sameState();
      const count = activeCount(draft);
      $$('[data-filter-count]').forEach((node) => { node.textContent = toFa(count); node.hidden = count === 0; });
      $$('[data-filter-apply]', listing).concat($$('[data-filter-apply]', modal)).forEach((button) => button.classList.toggle('is-ready', changed));
      const resultCount = filterProducts(draft).length;
      $$('[data-filter-result-count]').forEach((node) => { node.textContent = toFa(resultCount); });
    }

    function filterProducts(state) {
      const min = state.minPrice === '' ? null : Number(state.minPrice);
      const max = state.maxPrice === '' ? null : Number(state.maxPrice);
      return products.filter((product) => {
        if (min !== null && product.price < min) return false;
        if (max !== null && product.price > max) return false;
        if (state.colors.length && !state.colors.some((color) => (product.colors || []).includes(color))) return false;
        if (state.sizes.length && !state.sizes.some((size) => (product.sizes || []).includes(size))) return false;
        if (state.saleOnly && !(product.oldPrice && product.oldPrice > product.price)) return false;
        return true;
      });
    }

    function renderApplied() {
      const selected = filterProducts(applied);
      const visibleIds = new Set(selected.map((product) => product.id));
      let count = 0;
      $$('.product-card', grid).forEach((card) => {
        const visible = visibleIds.has(card.dataset.id);
        card.hidden = !visible;
        if (visible) count += 1;
      });
      const counter = $('#listing-count');
      if (counter) counter.textContent = `${toFa(count)} انتخاب`;
      let empty = $('.filter-empty-state', results);
      if (!count) {
        if (!empty) {
          empty = document.createElement('div');
          empty.className = 'filter-empty-state';
          empty.innerHTML = `<span>⌕</span><h3>با این فیلترها محصولی پیدا نشد.</h3><p>یکی از فیلترها را تغییر بده یا همه را پاک کن.</p><button type="button" data-filter-reset>پاک کردن فیلترها</button>`;
          results.append(empty);
        }
      } else if (empty) empty.remove();
      syncControls();
    }

    function applyFilters() {
      const min = draft.minPrice === '' ? null : Number(draft.minPrice);
      const max = draft.maxPrice === '' ? null : Number(draft.maxPrice);
      if (min !== null && max !== null && min > max) {
        announce('حداقل قیمت نمی‌تواند از حداکثر قیمت بیشتر باشد.');
        return;
      }
      if (sameState()) {
        announce('برای اعمال فیلترها، ابتدا حداقل یک گزینه را تغییر بده.');
        return;
      }
      applied = structuredClone(draft);
      renderApplied();
      closeModal();
      announce('فیلترها با موفقیت اعمال شدند.');
    }

    function resetFilters() {
      draft = structuredClone(defaults);
      applied = structuredClone(defaults);
      renderApplied();
      closeModal();
      announce('همه فیلترها پاک شدند.');
    }

    function openModal() {
      modal.hidden = false;
      document.body.classList.add('filter-modal-open', 'lock-scroll');
      requestAnimationFrame(() => modal.classList.add('is-open'));
      $('[data-filter-close]', modal)?.focus();
    }
    function closeModal() {
      if (modal.hidden) return;
      modal.classList.remove('is-open');
      window.setTimeout(() => { if (!modal.classList.contains('is-open')) modal.hidden = true; }, 220);
      document.body.classList.remove('filter-modal-open', 'lock-scroll');
      mobileTrigger.focus({ preventScroll:true });
    }

    document.addEventListener('click', (event) => {
      const toggle = event.target.closest('[data-filter-toggle]');
      if (toggle && listing.contains(toggle) || toggle && modal.contains(toggle)) {
        const type = toggle.dataset.filterType;
        const value = toggle.dataset.value;
        const key = type === 'color' ? 'colors' : 'sizes';
        draft[key] = draft[key].includes(value) ? draft[key].filter((item) => item !== value) : [...draft[key], value];
        syncControls();
        return;
      }
      if (event.target.closest('[data-filter-apply]')) { applyFilters(); return; }
      if (event.target.closest('[data-filter-reset]')) { resetFilters(); return; }
      if (event.target.closest('.mobile-filter-trigger')) { openModal(); return; }
      if (event.target.closest('[data-filter-close]') || event.target === modal) { closeModal(); return; }
    });

    document.addEventListener('input', (event) => {
      const priceInput = event.target.closest('[data-filter-price]');
      if (priceInput && (listing.contains(priceInput) || modal.contains(priceInput))) {
        const key = priceInput.dataset.filterPrice;
        const clean = normalize(priceInput.value).replace(/[^0-9]/g, '');
        draft[key] = clean;
        syncControls();
      }
    });
    document.addEventListener('change', (event) => {
      const saleInput = event.target.closest('[data-filter-sale]');
      if (saleInput && (listing.contains(saleInput) || modal.contains(saleInput))) {
        draft.saleOnly = saleInput.checked;
        syncControls();
      }
      if (event.target.matches('#product-sort')) window.setTimeout(renderApplied, 0);
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !modal.hidden) closeModal();
    });

    // رنگ‌های خنثی برند؛ رنگ‌ها فقط در نمایش swatch استفاده می‌شوند.
    const swatches = { 'عاجی':'#F0EBDD', 'سفید':'#FFFFFF', 'خاکی':'#B7A58A', 'اسپرسو':'#44362F', 'سبز زیتونی':'#66735A', 'سبز':'#82916F', 'مشکی':'#242424', 'بژ گرم':'#D4C0A5', 'قهوه‌ای':'#806247', 'قهوه ای':'#806247' };
    $$('[data-color-name]').forEach((dot) => { dot.style.setProperty('--filter-swatch', swatches[dot.dataset.colorName] || '#B7A58A'); });
    mobileTrigger.querySelector('[data-filter-count]').hidden = true;
    syncControls();
  }

  /* --------------------- PRODUCT DETAIL -------------------- */
  function setupProductDetail() {
    if (page !== 'product') return;
    const detail = $('[data-product-detail]');
    if (!detail) return;
    const id = new URLSearchParams(window.location.search).get('id');
    const product = catalog.find((item) => item.id === id);
    if (!product) return;

    const imageWrap = $('.product-detail-image', detail);
    const copy = $('.product-detail-copy', detail);
    const description = $('.product-detail-description', copy);
    const colorOption = $$('.detail-option', copy).find((node) => node.querySelector('[data-color-choice]'));
    const sizeOption = $$('.detail-option', copy).find((node) => node.querySelector('#product-size'));
    if (!imageWrap || !copy || !sizeOption) return;

    imageWrap.classList.add('product-image-editorial');
    imageWrap.insertAdjacentHTML('beforeend', `<span class="product-image-index">SHAHBAZ / 01</span><span class="product-image-caption">جزئیات یک انتخاب خوب</span>`);

    const eyebrow = $('.eyebrow', copy);
    if (eyebrow) {
      const meta = document.createElement('div');
      meta.className = 'product-detail-meta';
      eyebrow.replaceWith(meta);
      meta.append(eyebrow);
      meta.insertAdjacentHTML('beforeend', `<span>کد محصول: ${esc(product.id.toUpperCase())}</span>`);
    }

    const sizeSelect = $('#product-size', sizeOption);
    let syncBuyBarVariants = () => {};
    const sizeLabel = $('label', sizeOption);
    const sizeChoices = (product.sizes || []).map((size, index) => `<button type="button" class="detail-size-choice ${index === 0 ? 'is-selected' : ''}" data-size-choice="${esc(size)}" aria-pressed="${index === 0}">${esc(size)}</button>`).join('');
    if (sizeLabel) sizeLabel.remove();
    sizeSelect.classList.add('native-size-source');
    sizeOption.insertAdjacentHTML('afterbegin', `<div class="size-option-head"><span>انتخاب سایز</span><button type="button" class="size-guide-trigger" data-size-guide-open>راهنمای انتخاب سایز <span>↗</span></button></div><div class="detail-size-options">${sizeChoices}</div>`);
    sizeSelect.addEventListener('change', () => {
      $$('[data-size-choice]', sizeOption).forEach((button) => {
        const chosen = button.dataset.sizeChoice === sizeSelect.value;
        button.classList.toggle('is-selected', chosen);
        button.setAttribute('aria-pressed', String(chosen));
      });
      syncBuyBarVariants();
    });

    if (colorOption) {
      const swatches = { 'عاجی':'#F0EBDD', 'سفید':'#FFFFFF', 'خاکی':'#B7A58A', 'اسپرسو':'#44362F', 'سبز زیتونی':'#66735A', 'سبز':'#82916F', 'مشکی':'#242424', 'بژ گرم':'#D4C0A5', 'قهوه‌ای':'#806247', 'قهوه ای':'#806247' };
      $$('[data-color-choice]', colorOption).forEach((button) => {
        button.style.setProperty('--product-swatch', swatches[button.dataset.colorValue] || '#B7A58A');
        button.setAttribute('aria-label', `رنگ ${button.dataset.colorValue}`);
      });
    }

    const trust = $('.detail-trust', copy);
    if (trust) {
      trust.insertAdjacentHTML('afterend', `
        <details class="product-info-accordion"><summary>مشخصات انتخاب</summary><div><p>رنگ‌های موجود: ${(product.colors || []).map(esc).join('، ') || 'ثبت نشده'}</p><p>سایزبندی: ${(product.sizes || []).map(esc).join('، ') || 'ثبت نشده'}</p></div></details>
        <details class="product-info-accordion"><summary>درباره این محصول</summary><div><p>${esc(product.description)}</p><p>مشخصات و اطلاعات این نسخه نمایشی از کاتالوگ فعلی شهباز نمایش داده می‌شود.</p></div></details>`);
    }

    const addButton = $('[data-add-to-cart]', copy);
    if (addButton) {
      const priceNode = $('.detail-price strong', copy);
      const buyBar = document.createElement('div');
      buyBar.className = 'product-mobile-buybar';
      buyBar.innerHTML = `<div><span>قیمت محصول</span><strong>${priceNode?.textContent || ''}</strong></div><button class="button button-dark" type="button" data-add-to-cart data-id="${esc(product.id)}" data-size="${esc(sizeSelect.value || '')}" data-color="${esc(product.colors?.[0] || '')}">افزودن به سبد ${'←'}</button>`;
      document.body.append(buyBar);
      const mobileBuyButton = $('[data-add-to-cart]', buyBar);
      syncBuyBarVariants = () => {
        mobileBuyButton.dataset.size = sizeSelect.value || '';
        mobileBuyButton.dataset.color = $('.color-chip.is-selected', detail)?.dataset.colorValue || '';
      };
      syncBuyBarVariants();
    }

    const modal = document.createElement('div');
    modal.className = 'size-guide-modal';
    modal.id = 'size-guide-modal';
    modal.hidden = true;
    let guideContent = '';
    if (product.category === 'bags-shoes' && (product.sizes || []).some((size) => /^\d/.test(size))) {
      guideContent = `<div class="size-guide-steps"><article><b>۰۱</b><strong>طول پا را اندازه بگیر</strong><p>پاشنه را به دیوار بچسبان و فاصله تا نوک بلندترین انگشت را اندازه بگیر.</p></article><article><b>۰۲</b><strong>هر دو پا را بررسی کن</strong><p>اندازه هر دو پا را بگیر و برای انتخاب، اندازه پای بزرگ‌تر را در نظر بگیر.</p></article><article><b>۰۳</b><strong>فضای راحتی را در نظر بگیر</strong><p>فرم کفش و نوع جورابی که می‌پوشی روی حس نهایی سایز اثر می‌گذارد.</p></article></div>`;
    } else if (product.category === 'kids') {
      guideContent = `<div class="size-guide-steps"><article><b>۰۱</b><strong>سن و قد کودک</strong><p>بازه سنی درج‌شده کنار هر سایز را با سن و قد فعلی کودک مقایسه کن.</p></article><article><b>۰۲</b><strong>فضای حرکت</strong><p>برای لباس روزمره، آزادی حرکت را در نظر بگیر و از انتخاب بیش از حد جذب پرهیز کن.</p></article><article><b>۰۳</b><strong>بین دو سایز</strong><p>اگر کودک بین دو بازه قرار دارد، اندازه‌های واقعی محصول را از جدول رسمی فروشنده بررسی کن.</p></article></div>`;
    } else if (product.category === 'bags-shoes') {
      guideContent = `<div class="size-guide-steps"><article><b>۰۱</b><strong>تک‌سایز</strong><p>این محصول با یک سایز عرضه شده و انتخاب سایز جداگانه‌ای ندارد.</p></article><article><b>۰۲</b><strong>ابعاد محصول</strong><p>برای اطمینان از تناسب، ابعاد دقیق محصول باید در مشخصات رسمی فروشگاه درج شود.</p></article></div>`;
    } else {
      guideContent = `<div class="size-guide-steps"><article><b>۰۱</b><strong>دور سینه</strong><p>متر را از برجسته‌ترین قسمت سینه عبور بده و آن را موازی سطح زمین نگه دار.</p></article><article><b>۰۲</b><strong>دور کمر</strong><p>باریک‌ترین قسمت کمر را بدون سفت‌کردن متر اندازه بگیر.</p></article><article><b>۰۳</b><strong>دور باسن</strong><p>متر را از برجسته‌ترین قسمت باسن عبور بده تا اندازه دقیق‌تری داشته باشی.</p></article></div>`;
    }
    modal.innerHTML = `<section class="size-guide-panel" role="dialog" aria-modal="true" aria-labelledby="size-guide-title"><header class="size-guide-head"><div><span class="eyebrow">راهنمای همراه انتخاب</span><h2 id="size-guide-title">سایز مناسب خودت را پیدا کن</h2><p>چند نکته ساده برای انتخاب مطمئن‌تر</p></div><button type="button" class="icon-button" data-size-guide-close aria-label="بستن راهنمای سایز">×</button></header><div class="size-guide-content">${guideContent}<p class="size-guide-note">جدول اندازه‌های عددی این محصول هنوز در کاتالوگ نمونه ثبت نشده است؛ برای انتشار نهایی، اندازه‌های تأییدشده تولیدکننده باید به این بخش اضافه شوند.</p></div><footer><button type="button" class="button button-dark" data-size-guide-close>متوجه شدم</button></footer></section>`;
    $('#site-overlays')?.append(modal);

    function closeGuide() {
      modal.classList.remove('is-open');
      window.setTimeout(() => { if (!modal.classList.contains('is-open')) modal.hidden = true; }, 240);
      document.body.classList.remove('size-guide-open', 'lock-scroll');
      $('[data-size-guide-open]', sizeOption)?.focus({ preventScroll:true });
    }
    document.addEventListener('click', (event) => {
      if (event.target.closest('[data-size-guide-open]')) {
        modal.hidden = false;
        document.body.classList.add('size-guide-open', 'lock-scroll');
        requestAnimationFrame(() => modal.classList.add('is-open'));
        $('[data-size-guide-close]', modal)?.focus();
      } else if (event.target.closest('[data-size-guide-close]') || event.target === modal) {
        closeGuide();
      }
      const sizeButton = event.target.closest('[data-size-choice]');
      if (sizeButton && sizeOption.contains(sizeButton)) {
        sizeSelect.value = sizeButton.dataset.sizeChoice;
        sizeSelect.dispatchEvent(new Event('change', { bubbles:true }));
      }
      const colorButton = event.target.closest('[data-color-choice]');
      if (colorButton && detail.contains(colorButton)) requestAnimationFrame(syncBuyBarVariants);
    });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !modal.hidden) closeGuide(); });
  }

  setupLiveSearch();
  setupCategoryFilters();
  setupProductDetail();
})();
