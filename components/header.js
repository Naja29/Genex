(function () {
  const slot = document.getElementById('header-slot');
  if (!slot) return;

  slot.innerHTML = `

<!-- Announcement Bar -->
<div class="ann-bar">
  <div class="ann-wrap">
    <div class="ann-track">
      <span><i class="fas fa-tag"></i> Free delivery on orders over Rs. 10,000</span>
      <span><i class="fas fa-boxes"></i> Wholesale prices available - Contact us today</span>
      <span><i class="fas fa-shield-alt"></i> 100% Genuine products with manufacturer warranty</span>
      <span><i class="fas fa-headset"></i> 24/7 Customer support</span>
      <span><i class="fas fa-truck"></i> Fast island-wide delivery</span>
      <span><i class="fas fa-star"></i> Best prices guaranteed - Retail &amp; Wholesale</span>
      <span><i class="fas fa-tag"></i> Free delivery on orders over Rs. 10,000</span>
      <span><i class="fas fa-boxes"></i> Wholesale prices available - Contact us today</span>
      <span><i class="fas fa-shield-alt"></i> 100% Genuine products with manufacturer warranty</span>
      <span><i class="fas fa-headset"></i> 24/7 Customer support</span>
      <span><i class="fas fa-truck"></i> Fast island-wide delivery</span>
      <span><i class="fas fa-star"></i> Best prices guaranteed - Retail &amp; Wholesale</span>
    </div>
  </div>
</div>

<!-- Site Header -->
<header class="site-header" id="siteHeader">
  <div class="header-inner">

    <a href="index.php" class="site-logo">
      <img src="images/logo.jpg" alt="Genex - Global Xperience">
    </a>

    <div class="header-search">
      <input type="text" placeholder="What Are You Looking For..." id="searchInput">
      <button type="button" aria-label="Search"><i class="fas fa-search"></i></button>
    </div>

    <div class="header-right">
      <div class="header-phone">
        <i class="fas fa-phone-alt"></i>
        <div>
          <span class="hp-label">Call Us Now</span>
          <a href="tel:+94777237962" class="hp-num" id="gnxHeaderPhone">+94 77 723 7962</a>
        </div>
      </div>

      <div class="header-icons">
        <a href="#" class="h-icon" title="Wishlist" style="position:relative"><i class="far fa-heart"></i><span class="wish-badge" id="wishCount">0</span></a>
        <a href="account.php" class="h-icon" title="My Account"><i class="far fa-user"></i></a>
        <a href="#" class="h-icon" title="Cart" style="position:relative">
          <i class="fas fa-shopping-cart"></i>
          <span class="cart-badge" id="cartCount">0</span>
        </a>
      </div>

      <button class="hamburger" id="hamburger" aria-label="Toggle Menu">
        <span></span><span></span><span></span>
      </button>
    </div>

  </div>
</header>

<!-- Main Navigation -->
<nav class="main-nav" id="mainNav">
  <div class="nav-inner">
    <ul class="nav-list">

      <li class="nav-item">
        <a href="index.php" class="nav-link">Home</a>
      </li>

      <li class="nav-item">
        <a href="shop.php" class="nav-link">
          Shop <i class="fas fa-chevron-down arr"></i>
        </a>
        <div class="nav-drop" id="gnxNavDrop">
          <a href="shop.php"><i class="fas fa-th-large"></i> All Products</a>
        </div>
      </li>

      <li class="nav-item">
        <a href="brands.php" class="nav-link">Brands</a>
      </li>

      <li class="nav-item">
        <a href="wholesale.php" class="nav-link">Wholesale</a>
      </li>

      <li class="nav-item">
        <a href="about.php" class="nav-link">About Us</a>
      </li>

      <li class="nav-item">
        <a href="contact.php" class="nav-link">Contact</a>
      </li>

    </ul>

    <a href="https://wa.me/94777237962" target="_blank" rel="noopener" class="nav-wa-btn" id="gnxNavWa">
      <i class="fab fa-whatsapp"></i> Order via WhatsApp
    </a>
  </div>
</nav>

<!-- Category Strip -->
<div class="cat-strip">
  <div class="cat-strip-inner" id="gnxCatStrip"></div>
</div>

<!-- Mobile Nav Overlay -->
<div class="mobile-nav" id="mobileNav">
  <a href="index.php"><i class="fas fa-home"></i> Home</a>
  <a href="shop.php" style="font-weight:700;color:#fff"><i class="fas fa-th-large"></i> Shop by Category</a>
  <div class="mobile-nav-cats" id="gnxMobileCats"></div>
  <a href="brands.php"><i class="fas fa-star"></i> Brands</a>
  <a href="wholesale.php"><i class="fas fa-boxes"></i> Wholesale</a>
  <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
  <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
  <a href="https://wa.me/94777237962" target="_blank" style="color:#25d366;margin-top:10px" id="gnxMobileWa">
    <i class="fab fa-whatsapp"></i> WhatsApp Order
  </a>
</div>

  `;

  // Inject cart drawer after header slot
  const drawerHTML = `
<div class="cart-overlay" id="cartOverlay"></div>
<div class="cart-drawer" id="cartDrawer">
  <div class="cart-drawer-head">
    <h3><i class="fas fa-shopping-cart" style="color:var(--primary)"></i> Your Cart <span class="cart-drawer-count" id="drawerCount">0</span></h3>
    <button class="cart-drawer-close" id="cartDrawerClose" type="button"><i class="fas fa-times"></i></button>
  </div>
  <div class="cart-drawer-body" id="drawerBody"></div>
  <div class="cart-drawer-footer" id="drawerFooter">
    <div class="cart-drawer-total">
      <span>Subtotal</span>
      <strong id="drawerTotal">Rs. 0</strong>
    </div>
    <p class="cart-drawer-note"><i class="fas fa-truck"></i> Delivery calculated at checkout</p>
    <a href="cart.php" class="btn btn-primary" style="width:100%;justify-content:center"><i class="fas fa-shopping-bag"></i> View Full Cart</a>
    <a id="drawerWaBtn" href="#" target="_blank" rel="noopener" class="btn btn-green" style="width:100%;justify-content:center"><i class="fab fa-whatsapp"></i> Order via WhatsApp</a>
  </div>
</div>`;
  document.body.insertAdjacentHTML('beforeend', drawerHTML);

  // Inject wishlist drawer
  const wishDrawerHTML = `
<div class="wish-overlay" id="wishOverlay"></div>
<div class="wish-drawer" id="wishDrawer">
  <div class="wish-drawer-head">
    <h3><i class="fas fa-heart" style="color:#ef4444"></i> Wishlist <span class="wish-drawer-count" id="wishDrawerCount">0</span></h3>
    <button class="wish-drawer-close" id="wishDrawerClose" type="button"><i class="fas fa-times"></i></button>
  </div>
  <div class="wish-drawer-body" id="wishDrawerBody"></div>
  <div class="wish-drawer-footer" id="wishDrawerFooter">
    <a href="wishlist.php" class="btn btn-ghost" style="width:100%;justify-content:center"><i class="fas fa-heart"></i> View Full Wishlist</a>
    <button id="wishMoveAllBtn" type="button" class="btn btn-primary" style="width:100%;justify-content:center"><i class="fas fa-shopping-cart"></i> Move All to Cart</button>
  </div>
</div>`;
  document.body.insertAdjacentHTML('beforeend', wishDrawerHTML);

  // Wishlist drawer open/close
  function openWishDrawer() {
    document.getElementById('wishDrawer').classList.add('open');
    document.getElementById('wishOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
    renderWishDrawer();
  }
  function closeWishDrawer() {
    document.getElementById('wishDrawer').classList.remove('open');
    document.getElementById('wishOverlay').classList.remove('show');
    document.body.style.overflow = '';
  }

  function renderWishDrawer() {
    if (!window.GenexWishlist) return;
    const items       = GenexWishlist.getItems();
    const body        = document.getElementById('wishDrawerBody');
    const footer      = document.getElementById('wishDrawerFooter');
    const countEl     = document.getElementById('wishDrawerCount');

    countEl.textContent = items.length;
    footer.style.display = items.length ? 'flex' : 'none';

    if (!items.length) {
      body.innerHTML = `
        <div class="wish-drawer-empty">
          <i class="far fa-heart"></i>
          <p>Your wishlist is empty</p>
          <a href="shop.php" class="btn btn-primary" style="margin-top:4px"><i class="fas fa-store"></i> Browse Products</a>
        </div>`;
      return;
    }

    body.innerHTML = items.map(item => `
      <div class="wish-item" data-id="${item.id}">
        <div class="wish-item-ico"><i class="${item.icon}"></i></div>
        <div class="wish-item-info">
          <div class="wish-item-name" title="${item.name}">${item.name}</div>
          <div class="wish-item-cat">${item.category}</div>
          <div class="wish-item-price">${GenexWishlist.fmt(item.price)}</div>
        </div>
        <div class="wish-item-actions">
          <button class="wish-item-cart" type="button" data-action="cart" data-id="${item.id}" title="Add to Cart"><i class="fas fa-cart-plus"></i></button>
          <button class="wish-item-remove" type="button" data-action="remove" data-id="${item.id}" title="Remove"><i class="fas fa-trash-alt"></i></button>
        </div>
      </div>`).join('');

    body.querySelectorAll('[data-action]').forEach(btn => {
      btn.addEventListener('click', () => {
        const item = GenexWishlist.getItems().find(i => i.id === btn.dataset.id);
        if (!item) return;
        if (btn.dataset.action === 'cart') {
          if (window.GenexCart) { GenexCart.addItem(item); }
          btn.innerHTML = '<i class="fas fa-check"></i>';
          btn.style.background = '#16a34a';
          setTimeout(() => { btn.innerHTML = '<i class="fas fa-cart-plus"></i>'; btn.style.background = ''; }, 1400);
        } else {
          GenexWishlist.removeItem(item.name);
          renderWishDrawer();
        }
      });
    });

    const moveAll = document.getElementById('wishMoveAllBtn');
    if (moveAll) {
      moveAll.onclick = () => {
        if (!window.GenexCart) return;
        GenexWishlist.getItems().forEach(i => GenexCart.addItem(i));
        GenexWishlist.clear();
        renderWishDrawer();
      };
    }
  }

  // Wire wishlist icon
  document.addEventListener('click', e => {
    const wishIcon = e.target.closest('a[title="Wishlist"]');
    if (wishIcon) { e.preventDefault(); openWishDrawer(); }
    if (e.target.closest('#wishOverlay') || e.target.closest('#wishDrawerClose')) closeWishDrawer();
  });

  // Cart drawer open/close
  function openCartDrawer() {
    document.getElementById('cartDrawer').classList.add('open');
    document.getElementById('cartOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
    renderDrawer();
  }
  function closeCartDrawer() {
    document.getElementById('cartDrawer').classList.remove('open');
    document.getElementById('cartOverlay').classList.remove('show');
    document.body.style.overflow = '';
  }

  function renderDrawer() {
    if (!window.GenexCart) return;
    const items   = GenexCart.getItems();
    const body    = document.getElementById('drawerBody');
    const footer  = document.getElementById('drawerFooter');
    const countEl = document.getElementById('drawerCount');
    const totalEl = document.getElementById('drawerTotal');
    const waBtn   = document.getElementById('drawerWaBtn');

    countEl.textContent = GenexCart.getCount();
    totalEl.textContent = GenexCart.fmt(GenexCart.getTotal());
    if (waBtn) waBtn.href = GenexCart.buildWaText() || '#';

    if (!items.length) {
      footer.style.display = 'none';
      body.innerHTML = `
        <div class="cart-drawer-empty">
          <i class="fas fa-shopping-cart"></i>
          <p>Your cart is empty</p>
          <a href="shop.php" class="btn btn-primary" style="margin-top:4px"><i class="fas fa-store"></i> Browse Products</a>
        </div>`;
      return;
    }

    footer.style.display = 'flex';
    body.innerHTML = items.map(item => `
      <div class="cart-item" data-id="${item.id}">
        <div class="cart-item-ico"><i class="${item.icon}"></i></div>
        <div class="cart-item-info">
          <div class="cart-item-name" title="${item.name}">${item.name}</div>
          <div class="cart-item-cat">${item.category}</div>
          <div class="cart-item-row">
            <span class="cart-item-price">${GenexCart.fmt(item.price)}</span>
            <div class="cart-item-qty-ctrl">
              <button type="button" data-action="dec" data-id="${item.id}"><i class="fas fa-minus"></i></button>
              <span>${item.qty}</span>
              <button type="button" data-action="inc" data-id="${item.id}"><i class="fas fa-plus"></i></button>
            </div>
          </div>
        </div>
        <button class="cart-item-remove" type="button" data-action="remove" data-id="${item.id}" title="Remove"><i class="fas fa-trash-alt"></i></button>
      </div>`).join('');

    body.querySelectorAll('[data-action]').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const action = btn.dataset.action;
        if (action === 'remove') GenexCart.removeItem(id);
        else {
          const item = GenexCart.getItems().find(i => i.id === id);
          if (!item) return;
          GenexCart.updateQty(id, action === 'inc' ? item.qty + 1 : item.qty - 1);
        }
        renderDrawer();
      });
    });
  }

  // Wire cart icon to open drawer
  document.addEventListener('click', e => {
    const cartIcon = e.target.closest('a[title="Cart"], .h-icon[title="Cart"]');
    if (cartIcon) { e.preventDefault(); openCartDrawer(); }
    if (e.target.closest('#cartOverlay') || e.target.closest('#cartDrawerClose')) closeCartDrawer();
  });

  // Init badges on load
  document.addEventListener('DOMContentLoaded', () => {
    if (window.GenexCart)     GenexCart.updateBadge();
    if (window.GenexWishlist) GenexWishlist.updateBadge();
  });

  // Set active nav link based on current page
  const page = location.pathname.split('/').pop() || 'index.php';
  document.querySelectorAll('.nav-link').forEach(link => {
    const href = link.getAttribute('href').split('/').pop().split('?')[0];
    if (href === page || (page === '' && href === 'index.php')) {
      link.classList.add('active');
    }
  });

  // Header search
  const searchInput = document.getElementById('searchInput');
  const searchBtn   = document.querySelector('.header-search button');

  function doSearch() {
    const q = searchInput.value.trim();
    if (!q) return;
    const shopSearch = document.getElementById('shopSearch');
    if (shopSearch) {
      // Already on shop page - fill sidebar search and filter in place
      shopSearch.value = q;
      shopSearch.dispatchEvent(new Event('input'));
      shopSearch.closest('.sf-block').scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
      window.location.href = 'shop.php?q=' + encodeURIComponent(q);
    }
  }

  searchInput.addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });
  if (searchBtn) searchBtn.addEventListener('click', doSearch);

  // Load categories from API and populate nav-drop, cat-strip, mobile-nav
  (function loadCategories() {
    var base = (function () {
      var s = document.querySelector('script[src*="components/header.js"]');
      if (s) return s.src.replace('components/header.js', '');
      return location.origin + location.pathname.replace(/\/[^/]*$/, '/');
    })();

    fetch(base + 'api/categories.php')
      .then(function (r) { return r.json(); })
      .then(function (cats) {
        var navDrop    = document.getElementById('gnxNavDrop');
        var catStrip   = document.getElementById('gnxCatStrip');
        var mobileCats = document.getElementById('gnxMobileCats');

        var dropHtml   = '<a href="shop.php"><i class="fas fa-th-large"></i> All Products</a>';
        var stripHtml  = '';
        var mobileHtml = '';

        cats.forEach(function (c) {
          var url  = 'shop.php?cat=' + encodeURIComponent(c.slug);
          var icon = c.icon || 'fas fa-box';

          dropHtml  += '<a href="' + url + '"><i class="' + icon + '"></i> ' + c.name + '</a>';

          stripHtml += '<a href="' + url + '" class="cat-strip-item">' +
                         '<div class="cat-icon-box"><i class="' + icon + '"></i></div>' +
                         '<span>' + c.name + '</span>' +
                       '</a>';

          mobileHtml += '<a href="' + url + '"><i class="' + icon + '"></i> ' + c.name + '</a>';
        });

        if (navDrop)    navDrop.innerHTML    = dropHtml;
        if (catStrip)   catStrip.innerHTML   = stripHtml;
        if (mobileCats) mobileCats.innerHTML = mobileHtml;

        // Re-highlight active cat-strip item based on ?cat= param
        var urlCat = new URLSearchParams(location.search).get('cat');
        if (urlCat && catStrip) {
          catStrip.querySelectorAll('.cat-strip-item').forEach(function (el) {
            if (el.href.indexOf('cat=' + urlCat) !== -1) el.classList.add('active');
          });
        }
      })
      .catch(function () { /* silently fail - nav still shows All Products */ });
  })();

  // Load settings (WhatsApp, phone) from API
  (function loadSettings() {
    var base = (function () {
      var s = document.querySelector('script[src*="components/header.js"]');
      if (s) return s.src.replace('components/header.js', '');
      return location.origin + location.pathname.replace(/\/[^/]*$/, '/');
    })();

    fetch(base + 'api/settings.php')
      .then(function (r) { return r.json(); })
      .then(function (cfg) {
        var wa    = cfg.whatsapp || '94777237962';
        var phone = cfg.phone    || '+94 77 723 7962';
        var waUrl = 'https://wa.me/' + wa;

        // Expose for cart.js buildWaText()
        window.GENEX_WA = wa;

        var navWa    = document.getElementById('gnxNavWa');
        var mobileWa = document.getElementById('gnxMobileWa');
        var hPhone   = document.getElementById('gnxHeaderPhone');

        if (navWa)    navWa.href    = waUrl;
        if (mobileWa) mobileWa.href = waUrl;
        if (hPhone)   { hPhone.href = 'tel:' + phone.replace(/\s/g, ''); hPhone.textContent = phone; }
      })
      .catch(function () {});
  })();

  // Pin nav to top when it scrolls out of view
  const nav  = document.getElementById('mainNav');
  let ph = null;
  function updateNav() {
    if (!nav) return;
    const scrolled = window.scrollY > nav.offsetTop;
    if (scrolled && !nav.classList.contains('nav-pinned')) {
      ph = document.createElement('div');
      ph.style.height = nav.offsetHeight + 'px';
      nav.parentNode.insertBefore(ph, nav);
      nav.classList.add('nav-pinned');
    } else if (!scrolled && nav.classList.contains('nav-pinned')) {
      nav.classList.remove('nav-pinned');
      if (ph) { ph.remove(); ph = null; }
    }
  }
  window.addEventListener('scroll', updateNav, { passive: true });
  window.addEventListener('resize', updateNav);
})();
