/* Shahbaz storefront progressive-enhancement interactions. No persistent data is stored here. */
(() => {
  'use strict';
  const menu = document.querySelector('[data-mobile-menu]');
  const overlay = document.querySelector('[data-mobile-menu-close]');
  const open = document.querySelector('[data-mobile-menu-open]');
  const closeButtons = document.querySelectorAll('[data-mobile-menu-close]');
  const setMenu = (active) => {
    if (!menu || !overlay) return;
    menu.classList.toggle('is-open', active);
    overlay.classList.toggle('is-visible', active);
    overlay.hidden = !active;
    menu.setAttribute('aria-hidden', String(!active));
    open?.setAttribute('aria-expanded', String(active));
    document.body.classList.toggle('mobile-menu-open', active);
  };
  open?.addEventListener('click', () => setMenu(true));
  closeButtons.forEach((button) => button.addEventListener('click', () => setMenu(false)));
  menu?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setMenu(false)));
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape') setMenu(false); });
})();
