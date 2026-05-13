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
          <a href="https://web.facebook.com/genecoretech" target="_blank" rel="noopener" class="soc-btn" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="soc-btn" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="https://wa.me/94777237962" target="_blank" class="soc-btn" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
          <a href="#" class="soc-btn" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
          <a href="#" class="soc-btn" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
        </div>
      </div>

      <div class="footer-col" data-anim="up" data-delay="2">
        <h4>Quick Links</h4>
        <ul class="footer-links">
          <li><a href="index.html">Home</a></li>
          <li><a href="shop.html">Shop</a></li>
          <li><a href="wholesale.html">Wholesale</a></li>
          <li><a href="brands.html">Brands</a></li>
          <li><a href="about.html">About Us</a></li>
          <li><a href="contact.html">Contact</a></li>
          <li><a href="returns.html">Return Policy</a></li>
        </ul>
      </div>

      <div class="footer-col" data-anim="up" data-delay="3">
        <h4>Categories</h4>
        <ul class="footer-links">
          <li><a href="#">Processors</a></li>
          <li><a href="#">Motherboards</a></li>
          <li><a href="#">RAM</a></li>
          <li><a href="#">Storage - SSD &amp; HDD</a></li>
          <li><a href="#">Graphics Cards</a></li>
          <li><a href="#">Monitors</a></li>
          <li><a href="#">Keyboards &amp; Mouse</a></li>
          <li><a href="#">Mobile Displays</a></li>
        </ul>
      </div>

      <div class="footer-col" data-anim="up" data-delay="4">
        <h4>Contact Us</h4>
        <ul class="contact-list">
          <li><i class="fas fa-map-marker-alt"></i><span>Lenabatuwa, Kamburupitiya,<br>Sri Lanka - 81100</span></li>
          <li><i class="fas fa-phone-alt"></i><a href="tel:+94777237962">+94 77 723 7962</a></li>
          <li><i class="fab fa-whatsapp"></i><a href="https://wa.me/94777237962" target="_blank">+94 77 723 7962</a></li>
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
          &copy; <span id="footerYear"></span> <a href="index.html">Genex</a>. All rights reserved.
          &nbsp;·&nbsp;
          <span class="footer-credit">Designed &amp; developed by <a href="https://www.asseminate.com/" target="_blank" rel="noopener">Asseminate</a></span>
        </p>

        <!-- Policy links right -->
        <nav class="footer-policy-links" aria-label="Legal">
          <a href="privacy.html">Privacy Policy</a>
          <span class="fp-div"></span>
          <a href="terms.html">Terms &amp; Conditions</a>
          <span class="fp-div"></span>
          <a href="returns.html">Return Policy</a>
          <span class="fp-div"></span>
          <a href="shipping.html">Shipping Policy</a>
          <span class="fp-div"></span>
          <a href="faq.html">FAQ</a>
        </nav>
      </div>

    </div>
  </div>
</footer>

  `;

  // Set year
  const yr = document.getElementById('footerYear');
  if (yr) yr.textContent = new Date().getFullYear();
})();
