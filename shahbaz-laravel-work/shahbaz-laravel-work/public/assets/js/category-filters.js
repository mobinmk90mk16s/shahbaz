/* Responsive server-side category filters. Selection is submitted to Laravel; it is not persisted in browser storage. */
(() => {
  'use strict';
  const sidebar = document.querySelector('#category-filter-sidebar');
  const trigger = document.querySelector('[data-filter-open]');
  const backdrop = document.querySelector('[data-filter-backdrop]');
  const closeButton = document.querySelector('[data-filter-close]');
  if (!sidebar || !trigger || !backdrop) return;

  document.querySelectorAll('[data-submit-on-change]').forEach((control) => {
    control.addEventListener('change', () => control.form?.requestSubmit());
  });

  const setOpen = (open) => {
    sidebar.classList.toggle('is-mobile-open', open);
    backdrop.hidden = !open;
    document.body.classList.toggle('filter-modal-open', open);
    trigger.setAttribute('aria-expanded', String(open));
    if (open) closeButton?.focus(); else trigger.focus({ preventScroll: true });
  };
  trigger.addEventListener('click', () => setOpen(true));
  closeButton?.addEventListener('click', () => setOpen(false));
  backdrop.addEventListener('click', () => setOpen(false));
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && sidebar.classList.contains('is-mobile-open')) setOpen(false); });

  const colors = { 'عاجی':'#F0EBDD', 'سفید':'#FFFFFF', 'خاکی':'#B7A58A', 'اسپرسو':'#44362F', 'سبز زیتونی':'#66735A', 'سبز':'#82916F', 'مشکی':'#242424', 'بژ گرم':'#D4C0A5', 'قهوه‌ای':'#806247', 'قهوه ای':'#806247' };
  sidebar.querySelectorAll('.filter-color-dot').forEach((dot) => {
    const label = dot.closest('label');
    const value = label?.querySelector('input')?.value;
    if (value) dot.style.setProperty('--filter-swatch', colors[value] || '#B7A58A');
  });
})();
