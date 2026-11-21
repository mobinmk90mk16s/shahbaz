/* Live product discovery backed by the Laravel catalog endpoint. */
(() => {
  'use strict';
  const headerForm = document.querySelector('#search-form');
  const headerInput = document.querySelector('#site-search');
  const mount = document.querySelector('#site-overlays');
  const endpoint = document.body.dataset.searchSuggestUrl;
  if (!headerForm || !headerInput || !mount || !endpoint) return;

  const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, (char) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' })[char]);
  const money = new Intl.NumberFormat('fa-IR');
  const overlay = document.createElement('div');
  overlay.className = 'search-glass';
  overlay.hidden = true;
  overlay.innerHTML = `
    <section class="search-glass-panel" role="dialog" aria-modal="true" aria-labelledby="live-search-title">
      <div class="search-glass-head"><div><span class="eyebrow">جست‌وجوی هوشمند شهباز</span><h2 id="live-search-title">چی توی ذهنته؟</h2></div><button class="icon-button" type="button" data-search-close aria-label="بستن جست‌وجو">×</button></div>
      <form class="search-glass-form" method="GET" action="${escapeHtml(headerForm.action)}" role="search"><span class="search-glass-icon" aria-hidden="true">⌕</span><input name="q" type="search" autocomplete="off" placeholder="نام محصول، رنگ یا دسته‌بندی را بنویس..." aria-label="جست‌وجو در تمام محصولات"><button type="submit">مشاهده همه نتایج</button></form>
      <div class="search-live-meta"><span data-search-caption>محصولات پیشنهادی</span><span>جست‌وجو در تمام محصولات شهباز</span></div>
      <div class="search-live-results" data-search-results aria-live="polite"></div>
      <div class="search-glass-foot"><span>برای جست‌وجوی سریع، نام محصول یا بخشی از آن را وارد کن.</span><kbd>ESC</kbd></div>
    </section>`;
  mount.append(overlay);

  const overlayInput = overlay.querySelector('input[name="q"]');
  const results = overlay.querySelector('[data-search-results]');
  const caption = overlay.querySelector('[data-search-caption]');
  let active = false;
  let debounceTimer = null;
  let controller = null;
  let requestSequence = 0;
  let preventReopen = false;

  const renderResults = (items) => {
    if (!items.length) {
      results.innerHTML = '<div class="search-live-empty"><span>⌕</span><strong>چیزی با این عبارت پیدا نکردیم.</strong><p>املای کلمه را بررسی کن یا عبارت ساده‌تری بنویس.</p></div>';
      return;
    }
    results.innerHTML = items.map((item) => `
      <a class="search-live-item" href="${escapeHtml(item.url)}">
        <span class="search-live-thumb"><img src="${escapeHtml(item.image)}" alt="" loading="lazy"></span>
        <span class="search-live-info"><small>${escapeHtml(item.category)}</small><strong>${escapeHtml(item.name)}</strong><span>${money.format(Number(item.price) || 0)} تومان</span></span>
        <span class="search-live-arrow" aria-hidden="true">←</span>
      </a>`).join('');
  };

  const fetchResults = async (raw) => {
    const query = raw.trim();
    const sequence = ++requestSequence;
    controller?.abort();
    controller = new AbortController();
    const url = new URL(endpoint, window.location.origin);
    if (query) url.searchParams.set('q', query);
    try {
      const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin', signal: controller.signal });
      if (!response.ok) throw new Error(`Search request failed (${response.status})`);
      const payload = await response.json();
      if (sequence !== requestSequence) return;
      const items = Array.isArray(payload.items) ? payload.items : [];
      caption.textContent = query ? `${money.format(payload.total || items.length)} نتیجه برای «${query}»` : 'محصولات پیشنهادی';
      renderResults(items);
    } catch (error) {
      if (error.name !== 'AbortError') {
        caption.textContent = 'جست‌وجو موقتاً در دسترس نیست';
        results.textContent = 'برای دیدن نتایج، جست‌وجو را در صفحه نتایج ادامه بده.';
      }
    }
  };

  const scheduleSearch = (value) => {
    window.clearTimeout(debounceTimer);
    debounceTimer = window.setTimeout(() => fetchResults(value), 220);
  };
  const open = (value = '') => {
    if (active) return;
    active = true;
    overlay.hidden = false;
    overlay.classList.remove('is-closing');
    document.body.classList.add('search-open', 'lock-scroll');
    overlayInput.value = value || headerInput.value || '';
    fetchResults(overlayInput.value);
    window.requestAnimationFrame(() => overlayInput.focus({ preventScroll: true }));
  };
  const close = (restoreFocus = false) => {
    if (!active) return;
    active = false;
    controller?.abort();
    overlay.classList.add('is-closing');
    document.body.classList.remove('search-open', 'lock-scroll');
    window.setTimeout(() => { if (!active) { overlay.hidden = true; overlay.classList.remove('is-closing'); } }, 180);
    if (restoreFocus) {
      preventReopen = true;
      headerInput.focus({ preventScroll: true });
      window.requestAnimationFrame(() => { preventReopen = false; });
    }
  };

  headerInput.addEventListener('focus', () => { if (!preventReopen) open(headerInput.value); });
  headerInput.addEventListener('click', () => open(headerInput.value));
  overlayInput.addEventListener('input', () => { headerInput.value = overlayInput.value; scheduleSearch(overlayInput.value); });
  overlay.addEventListener('click', (event) => {
    if (event.target === overlay || event.target.closest('[data-search-close]')) close(true);
  });
  overlay.querySelector('form').addEventListener('submit', (event) => {
    event.preventDefault();
    const query = overlayInput.value.trim();
    window.location.assign(`${headerForm.action}?q=${encodeURIComponent(query)}`);
  });
  headerForm.addEventListener('submit', (event) => {
    event.preventDefault();
    const query = headerInput.value.trim();
    window.location.assign(`${headerForm.action}?q=${encodeURIComponent(query)}`);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && active) { event.stopPropagation(); close(true); }
  }, true);
})();
