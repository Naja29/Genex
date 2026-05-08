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

    <a href="index.html" class="site-logo">
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
          <a href="tel:+94777237962" class="hp-num">+94 77 723 7962</a>
        </div>
      </div>

      <div class="header-icons">
        <a href="#" class="h-icon" title="Wishlist"><i class="far fa-heart"></i></a>
        <a href="#" class="h-icon" title="My Account"><i class="far fa-user"></i></a>
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
        <a href="index.html" class="nav-link">Home</a>
      </li>

      <li class="nav-item">
        <a href="shop.html" class="nav-link">
          Shop <i class="fas fa-chevron-down arr"></i>
        </a>
        <div class="nav-drop">
          <a href="#"><i class="fas fa-microchip"></i> Processors</a>
          <a href="#"><i class="fas fa-server"></i> Motherboards</a>
          <a href="#"><i class="fas fa-memory"></i> RAM</a>
          <a href="#"><i class="fas fa-hdd"></i> Storage - SSD &amp; HDD</a>
          <a href="#"><i class="fas fa-desktop"></i> Graphics Cards</a>
          <a href="#"><i class="fas fa-tv"></i> Monitors</a>
          <a href="#"><i class="fas fa-keyboard"></i> Keyboards</a>
          <a href="#"><i class="fas fa-mouse"></i> Mouse</a>
          <a href="#"><i class="fas fa-plug"></i> Cables</a>
          <a href="#"><i class="fas fa-battery-full"></i> Power Banks</a>
          <a href="#"><i class="fas fa-charging-station"></i> Chargers</a>
          <a href="#"><i class="fas fa-headphones"></i> Earphones</a>
          <a href="#"><i class="fas fa-mobile-alt"></i> Mobile Displays &amp; Accessories</a>
        </div>
      </li>

      <li class="nav-item">
        <a href="brands.html" class="nav-link">Brands</a>
      </li>

      <li class="nav-item">
        <a href="wholesale.html" class="nav-link">Wholesale</a>
      </li>

      <li class="nav-item">
        <a href="about.html" class="nav-link">About Us</a>
      </li>

      <li class="nav-item">
        <a href="contact.html" class="nav-link">Contact</a>
      </li>

    </ul>

    <a href="https://wa.me/94777237962" target="_blank" rel="noopener" class="nav-wa-btn">
      <i class="fab fa-whatsapp"></i> Order via WhatsApp
    </a>
  </div>
</nav>

<!-- Category Strip -->
<div class="cat-strip">
  <div class="cat-strip-inner">
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-microchip"></i></div>
      <span>Processors</span>
    </a>
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-server"></i></div>
      <span>Motherboards</span>
    </a>
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-memory"></i></div>
      <span>RAM</span>
    </a>
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-hdd"></i></div>
      <span>Storage</span>
    </a>
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-desktop"></i></div>
      <span>Graphics Cards</span>
    </a>
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-tv"></i></div>
      <span>Monitors</span>
    </a>
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-keyboard"></i></div>
      <span>Keyboards</span>
    </a>
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-mouse"></i></div>
      <span>Mouse</span>
    </a>
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-plug"></i></div>
      <span>Cables</span>
    </a>
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-battery-full"></i></div>
      <span>Power Banks</span>
    </a>
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-charging-station"></i></div>
      <span>Chargers</span>
    </a>
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-headphones"></i></div>
      <span>Earphones</span>
    </a>
    <a href="#" class="cat-strip-item">
      <div class="cat-icon-box"><i class="fas fa-mobile-alt"></i></div>
      <span>Mobile Displays</span>
    </a>
  </div>
</div>

<!-- Mobile Nav Overlay -->
<div class="mobile-nav" id="mobileNav">
  <a href="index.html"><i class="fas fa-home"></i> Home</a>
  <a href="shop.html" style="font-weight:700;color:#fff"><i class="fas fa-th-large"></i> Shop by Category</a>
  <div class="mobile-nav-cats">
    <a href="#"><i class="fas fa-microchip"></i> Processors</a>
    <a href="#"><i class="fas fa-server"></i> Motherboards</a>
    <a href="#"><i class="fas fa-memory"></i> RAM</a>
    <a href="#"><i class="fas fa-hdd"></i> Storage (SSD / HDD)</a>
    <a href="#"><i class="fas fa-desktop"></i> Graphics Cards</a>
    <a href="#"><i class="fas fa-tv"></i> Monitors</a>
    <a href="#"><i class="fas fa-keyboard"></i> Keyboards &amp; Mouse</a>
    <a href="#"><i class="fas fa-plug"></i> Cables &amp; Accessories</a>
    <a href="#"><i class="fas fa-battery-full"></i> Power Banks &amp; Chargers</a>
    <a href="#"><i class="fas fa-headphones"></i> Earphones</a>
    <a href="#"><i class="fas fa-mobile-alt"></i> Mobile Displays</a>
  </div>
  <a href="brands.html"><i class="fas fa-star"></i> Brands</a>
  <a href="wholesale.html"><i class="fas fa-boxes"></i> Wholesale</a>
  <a href="about.html"><i class="fas fa-info-circle"></i> About Us</a>
  <a href="contact.html"><i class="fas fa-envelope"></i> Contact</a>
  <a href="https://wa.me/94777237962" target="_blank" style="color:#25d366;margin-top:10px">
    <i class="fab fa-whatsapp"></i> WhatsApp Order
  </a>
</div>

  `;

  // Set active nav link based on current page
  const page = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-link').forEach(link => {
    const href = link.getAttribute('href').split('/').pop();
    if (href === page || (page === '' && href === 'index.html')) {
      link.classList.add('active');
    }
  });

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
