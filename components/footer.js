(function () {
  const slot = document.getElementById('footer-slot');
  if (!slot) return;

  slot.innerHTML = `

<!-- Newsletter -->
<section class="newsletter-section">
  <div class="container newsletter-inner">
    <div class="nl-text" data-anim="left">
      <span class="nl-tag">Stay Updated</span>
      <h3>Never Miss a <em style="background:var(--gold);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-style:normal">Deal</em></h3>
      <p>Subscribe for exclusive offers, new arrivals &amp; wholesale updates.</p>
    </div>
    <div class="nl-form" data-anim="right">
      <input type="email" placeholder="Enter your email address">
      <button type="button"><i class="fas fa-paper-plane"></i> Subscribe</button>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">

      <div data-anim="up" data-delay="1">
        <img src="images/logo.jpg" alt="Genex" class="footer-logo">
        <p class="footer-about">Genex - Global Experience. Your trusted source for premium computer parts, electronics and accessories. Wholesale &amp; Retail with the best prices and fast service across Sri Lanka.</p>
        <div class="socials">
          <a href="https://web.facebook.com/genecoretech" target="_blank" rel="noopener" class="soc-btn" aria-label="Facebook" id="gnxSocFb"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="soc-btn" aria-label="Instagram" id="gnxSocIg" style="display:none"><i class="fab fa-instagram"></i></a>
          <a href="https://wa.me/94777237962" target="_blank" class="soc-btn" aria-label="WhatsApp" id="gnxSocWa"><i class="fab fa-whatsapp"></i></a>
          <a href="#" class="soc-btn" aria-label="YouTube" id="gnxSocYt" style="display:none"><i class="fab fa-youtube"></i></a>
          <a href="#" class="soc-btn" aria-label="TikTok" id="gnxSocTt" style="display:none"><i class="fab fa-tiktok"></i></a>
        </div>
      </div>

      <div class="footer-col" data-anim="up" data-delay="2">
        <h4>Quick Links</h4>
        <ul class="footer-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="shop.php">Shop</a></li>
          <li><a href="wholesale.php">Wholesale</a></li>
          <li><a href="brands.php">Brands</a></li>
          <li><a href="about.php">About Us</a></li>
          <li><a href="contact.php">Contact</a></li>
          <li><a href="returns.php">Return Policy</a></li>
        </ul>
      </div>

      <div class="footer-col" data-anim="up" data-delay="3">
        <h4>Categories</h4>
        <ul class="footer-links" id="gnxFooterCats">
          <li><a href="shop.php">All Products</a></li>
        </ul>
      </div>

      <div class="footer-col" data-anim="up" data-delay="4">
        <h4>Contact Us</h4>
        <ul class="contact-list">
          <li><i class="fas fa-map-marker-alt"></i><span>Lenabatuwa, Kamburupitiya,<br>Sri Lanka - 81100</span></li>
          <li><i class="fas fa-phone-alt"></i><a href="tel:+94777237962" id="gnxFooterPhone">+94 77 723 7962</a></li>
          <li><i class="fab fa-whatsapp"></i><a href="https://wa.me/94777237962" target="_blank" id="gnxFooterWa">+94 77 723 7962</a></li>
          <li><i class="fas fa-envelope"></i><a href="mailto:genecoretech@gmail.com">genecoretech@gmail.com</a></li>
          <li><i class="fas fa-clock"></i><span>Mon - Sat: 8:00 AM - 7:00 PM</span></li>
        </ul>
      </div>

    </div>
  </div>

  <div class="footer-bottom">
    <div class="footer-bottom-inner">

      <div class="footer-bottom-row">
        <!-- Copyright left -->
        <p class="footer-copy">
          &copy; <span id="footerYear"></span> <a href="index.php">Genex</a>. All rights reserved.
          &nbsp;·&nbsp;
          <span class="footer-credit">Designed &amp; developed by <a href="https://www.asseminate.com/" target="_blank" rel="noopener">Asseminate</a></span>
        </p>

        <!-- Policy links right -->
        <nav class="footer-policy-links" aria-label="Legal">
          <a href="privacy.php">Privacy Policy</a>
          <span class="fp-div"></span>
          <a href="terms.php">Terms &amp; Conditions</a>
          <span class="fp-div"></span>
          <a href="returns.php">Return Policy</a>
          <span class="fp-div"></span>
          <a href="shipping.php">Shipping Policy</a>
          <span class="fp-div"></span>
          <a href="faq.php">FAQ</a>
        </nav>
      </div>

    </div>
  </div>
</footer>

  `;

  // Set year
  const yr = document.getElementById('footerYear');
  if (yr) yr.textContent = new Date().getFullYear();

  // Load settings (socials, WhatsApp, phone) from API
  (function loadSettings() {
    var base = (function () {
      var s = document.querySelector('script[src*="components/footer.js"]');
      if (s) return s.src.replace('components/footer.js', '');
      return location.origin + location.pathname.replace(/\/[^/]*$/, '/');
    })();

    fetch(base + 'api/settings.php')
      .then(function (r) { return r.json(); })
      .then(function (cfg) {
        var wa    = cfg.whatsapp || '94777237962';
        var phone = cfg.phone    || '+94 77 723 7962';
        var waUrl = 'https://wa.me/' + wa;

        // Social icons: show only if URL is set
        function wire(id, url) {
          var el = document.getElementById(id);
          if (!el) return;
          if (url) { el.href = url; el.style.display = ''; el.target = '_blank'; el.rel = 'noopener'; }
          else     { el.style.display = 'none'; }
        }
        wire('gnxSocFb', cfg.facebook);
        wire('gnxSocIg', cfg.instagram);
        wire('gnxSocYt', cfg.youtube);
        wire('gnxSocTt', cfg.tiktok);

        // WhatsApp social icon always shown (uses store WA number)
        var socWa = document.getElementById('gnxSocWa');
        if (socWa) { socWa.href = waUrl; socWa.style.display = ''; }

        // Contact list
        var footerPhone = document.getElementById('gnxFooterPhone');
        var footerWa    = document.getElementById('gnxFooterWa');
        if (footerPhone) { footerPhone.href = 'tel:' + phone.replace(/\s/g, ''); footerPhone.textContent = phone; }
        if (footerWa)    { footerWa.href = waUrl; footerWa.textContent = phone; }
      })
      .catch(function () {});
  })();

  // Load categories dynamically
  (function loadFooterCats() {
    var base = (function () {
      var s = document.querySelector('script[src*="components/footer.js"]');
      if (s) return s.src.replace('components/footer.js', '');
      return location.origin + location.pathname.replace(/\/[^/]*$/, '/');
    })();

    fetch(base + 'api/categories.php')
      .then(function (r) { return r.json(); })
      .then(function (cats) {
        var ul = document.getElementById('gnxFooterCats');
        if (!ul) return;
        var html = '<li><a href="shop.php">All Products</a></li>';
        cats.slice(0, 8).forEach(function (c) {
          html += '<li><a href="shop.php?cat=' + encodeURIComponent(c.slug) + '">' + c.name + '</a></li>';
        });
        ul.innerHTML = html;
      })
      .catch(function () {});
  })();
})();
