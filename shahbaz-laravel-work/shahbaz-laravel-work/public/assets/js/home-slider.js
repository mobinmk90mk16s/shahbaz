/* SHAHBAZ home carousel: accessible controls, autoplay pause, keyboard and touch support. */
(() => {
  'use strict';
  const root = document.querySelector('[data-hero-slider]');
  if (!root) return;
  const slides = [...root.querySelectorAll('[data-hero-slide]')];
  const dots = [...root.querySelectorAll('[data-hero-dot]')];
  const previous = root.querySelector('[data-hero-prev]');
  const next = root.querySelector('[data-hero-next]');
  if (slides.length < 2 || dots.length !== slides.length) return;

  const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
  let current = 0;
  let timer = null;
  let pointerInside = false;
  let focusInside = false;
  let touchStartX = null;

  function show(index) {
    current = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => {
      const active = i === current;
      slide.classList.toggle('is-active', active);
      slide.setAttribute('aria-hidden', String(!active));
      slide.inert = !active;
    });
    dots.forEach((dot, i) => {
      const active = i === current;
      dot.classList.toggle('is-active', active);
      dot.setAttribute('aria-pressed', String(active));
    });
  }
  function stop() { if (timer) window.clearInterval(timer); timer = null; }
  function start() {
    stop();
    if (reduceMotion || document.hidden || pointerInside || focusInside) return;
    timer = window.setInterval(() => show(current + 1), 6200);
  }
  function pause() { stop(); }

  previous?.addEventListener('click', () => { show(current - 1); start(); });
  next?.addEventListener('click', () => { show(current + 1); start(); });
  dots.forEach((dot) => dot.addEventListener('click', () => { show(Number(dot.dataset.heroDot)); start(); }));
  root.addEventListener('mouseenter', () => { pointerInside = true; pause(); });
  root.addEventListener('mouseleave', () => { pointerInside = false; start(); });
  root.addEventListener('focusin', () => { focusInside = true; pause(); });
  root.addEventListener('focusout', (event) => {
    if (!root.contains(event.relatedTarget)) { focusInside = false; start(); }
  });
  root.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') { show(current + 1); start(); }
    if (event.key === 'ArrowRight') { show(current - 1); start(); }
  });
  root.addEventListener('touchstart', (event) => { touchStartX = event.changedTouches[0]?.clientX ?? null; pause(); }, { passive:true });
  root.addEventListener('touchend', (event) => {
    const endX = event.changedTouches[0]?.clientX;
    if (touchStartX !== null && endX !== undefined) {
      const delta = endX - touchStartX;
      if (Math.abs(delta) > 42) show(delta < 0 ? current + 1 : current - 1);
    }
    touchStartX = null; start();
  }, { passive:true });
  document.addEventListener('visibilitychange', () => document.hidden ? pause() : start());
  show(0);
  start();
})();
