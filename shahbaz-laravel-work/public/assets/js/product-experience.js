/* Product option swatches, size guide and sticky mobile purchase action. */
(() => {
  'use strict';
  const detail = document.querySelector('[data-product-detail]');
  if (!detail) return;

  const sizeGuide = document.querySelector('[data-size-guide]');
  const openGuide = document.querySelector('[data-size-guide-open]');
  const closeGuideButtons = sizeGuide?.querySelectorAll('[data-size-guide-close]') || [];
  const buyForm = detail.querySelector('form[data-product-buy-form]');
  const mobileAdd = document.querySelector('[data-mobile-add]');
  const swatches = { 'عاجی':'#F0EBDD', 'سفید':'#FFFFFF', 'خاکی':'#B7A58A', 'اسپرسو':'#44362F', 'سبز زیتونی':'#66735A', 'سبز':'#82916F', 'مشکی':'#242424', 'بژ گرم':'#D4C0A5', 'قهوه‌ای':'#806247', 'قهوه ای':'#806247' };

  detail.querySelectorAll('.color-chip').forEach((chip) => {
    const input = chip.querySelector('input[type="radio"]');
    const color = input?.value;
    if (color) chip.style.setProperty('--product-swatch', swatches[color] || '#B7A58A');
  });

  const close = () => {
    if (!sizeGuide) return;
    sizeGuide.classList.remove('is-open');
    document.body.classList.remove('size-guide-open', 'lock-scroll');
    window.setTimeout(() => { if (!sizeGuide.classList.contains('is-open')) sizeGuide.hidden = true; }, 240);
    openGuide?.focus({ preventScroll: true });
  };
  openGuide?.addEventListener('click', () => {
    if (!sizeGuide) return;
    sizeGuide.hidden = false;
    document.body.classList.add('size-guide-open', 'lock-scroll');
    window.requestAnimationFrame(() => sizeGuide.classList.add('is-open'));
    sizeGuide.querySelector('[data-size-guide-close]')?.focus();
  });
  closeGuideButtons.forEach((button) => button.addEventListener('click', close));
  sizeGuide?.addEventListener('click', (event) => { if (event.target === sizeGuide) close(); });
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && sizeGuide && !sizeGuide.hidden) close(); });

  mobileAdd?.addEventListener('click', () => buyForm?.requestSubmit());
})();
