(function (window) {
  const KEY = 'genex_wishlist';

  function load()      { try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch { return []; } }
  function save(items) { localStorage.setItem(KEY, JSON.stringify(items)); }
  function slugify(s)  { return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''); }

  const Wishlist = {
    getItems()  { return load(); },
    getCount()  { return load().length; },
    has(name)   { return !!load().find(i => i.id === slugify(name)); },

    addItem(product) {
      const items = load();
      const id    = slugify(product.name);
      if (!items.find(i => i.id === id)) {
        items.push({
          id,
          name:     product.name,
          category: product.category || '',
          price:    product.price,
          oldPrice: product.oldPrice || null,
          icon:     product.icon || 'fas fa-box'
        });
        save(items);
      }
      this.updateBadge();
      return items;
    },

    removeItem(name) {
      const id    = slugify(name);
      const items = load().filter(i => i.id !== id);
      save(items);
      this.updateBadge();
      return items;
    },

    toggle(product) {
      const id = slugify(product.name);
      if (load().find(i => i.id === id)) {
        this.removeItem(product.name);
        return false;
      } else {
        this.addItem(product);
        return true;
      }
    },

    clear() { save([]); this.updateBadge(); },

    updateBadge() {
      const n = this.getCount();
      document.querySelectorAll('#wishCount').forEach(b => {
        b.textContent   = n;
        b.style.display = n > 0 ? 'flex' : 'none';
        b.style.transform = 'scale(1.5)';
        setTimeout(() => b.style.transform = '', 200);
      });
    },

    fmt(n) { return 'Rs. ' + n.toLocaleString(); }
  };

  window.GenexWishlist = Wishlist;
})(window);
