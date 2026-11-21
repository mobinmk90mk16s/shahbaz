/* One-time migration of legacy browser data. Old keys are removed only after the server confirms persistence. */
(() => {
  'use strict';
  const endpoint = document.body.dataset.guestMigrationUrl;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  if (!endpoint || !csrf) return;

  const parse = (key, fallback) => {
    try {
      const value = JSON.parse(localStorage.getItem(key) ?? 'null');
      return value === null ? fallback : value;
    } catch { return fallback; }
  };
  const keys = {
    cart: ['shahbaz.cart.v2', 'shahbaz-cart-v1'],
    wishlist: ['shahbaz.wishlist.v2', 'shahbaz-wishlist-v1'],
    profile: ['shahbaz.profile.v1']
  };
  const firstExisting = (names, fallback) => {
    for (const key of names) {
      const raw = localStorage.getItem(key);
      if (raw !== null) return parse(key, fallback);
    }
    return fallback;
  };
  const cart = firstExisting(keys.cart, []);
  const wishlist = firstExisting(keys.wishlist, []);
  const profile = firstExisting(keys.profile, null);
  const hasData = (Array.isArray(cart) && cart.length) || (Array.isArray(wishlist) && wishlist.length) || (profile && typeof profile === 'object');
  if (!hasData) return;

  fetch(endpoint, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
    body: JSON.stringify({ cart: Array.isArray(cart) ? cart : [], wishlist: Array.isArray(wishlist) ? wishlist : [], profile: profile && typeof profile === 'object' ? profile : undefined })
  }).then((response) => {
    if (!response.ok) throw new Error(`Migration failed (${response.status})`);
    return response.json();
  }).then((result) => {
    if (result?.ok !== true) throw new Error('Migration was not confirmed by the server.');
    Object.values(keys).flat().forEach((key) => localStorage.removeItem(key));
    window.dispatchEvent(new CustomEvent('shahbaz:legacy-migrated'));
  }).catch((error) => {
    console.warn('Shahbaz legacy data migration was not completed. Existing browser data was kept.', error);
  });
})();
