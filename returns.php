<?php
require_once __DIR__ . '/includes/functions.php';

$phone      = getSetting('store_whatsapp', '94777237962');
$phoneClean = preg_replace('/\D/', '', $phone);
$email      = getSetting('email', 'genecoretech@gmail.com');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Genex Return & Refund Policy - Learn about our 7-day return window, conditions, refund process and how to initiate a return.">
  <title>Return Policy | Genex - Global Xperience</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="page-loader"><div class="loader-bar"></div></div>

<div id="header-slot"></div>
<script src="assets/js/cart.js"></script>
<script src="assets/js/wishlist.js"></script>
<script src="components/header.js"></script>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="hero-dot-bg"></div>
  <div class="page-hero-orb page-hero-orb-1"></div>
  <div class="page-hero-orb page-hero-orb-2"></div>
  <div class="container page-hero-inner">
    <div class="page-hero-text" data-anim="up">
      <span class="section-tag">Our Policies</span>
      <h1>Return &amp; Refund <em>Policy</em></h1>
      <p>We want you to be completely satisfied. Here's everything you need to know about returns and refunds at Genex.</p>
    </div>
    <nav class="breadcrumb" aria-label="Breadcrumb" data-anim="up" data-delay="2">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Return Policy</span>
    </nav>
  </div>
</section>

<!-- POLICY CONTENT -->
<section class="section">
  <div class="container">
    <div class="policy-layout">

      <!-- TOC -->
      <aside class="policy-toc" data-anim="left">
        <h4>Contents</h4>
        <ul>
          <li><a href="#commitment"><span class="toc-num">01</span> Our Commitment</a></li>
          <li><a href="#eligibility"><span class="toc-num">02</span> Eligibility</a></li>
          <li><a href="#non-returnable"><span class="toc-num">03</span> Non-Returnable Items</a></li>
          <li><a href="#how-to-return"><span class="toc-num">04</span> How to Return</a></li>
          <li><a href="#refunds"><span class="toc-num">05</span> Refund Process</a></li>
          <li><a href="#damaged"><span class="toc-num">06</span> Damaged or Defective</a></li>
          <li><a href="#contact"><span class="toc-num">07</span> Contact Us</a></li>
        </ul>
      </aside>

      <!-- Body -->
      <div data-anim="right">
        <div class="policy-last-updated"><i class="fas fa-calendar-alt"></i> Last Updated: May 2025</div>

        <div class="policy-highlight">
          <p><i class="fas fa-info-circle" style="color:var(--primary);margin-right:8px"></i> <strong>Quick Summary:</strong> We accept returns within 7 days of delivery for defective, damaged or incorrectly supplied items. Contact us on WhatsApp before returning any product.</p>
        </div>

        <!-- 01 -->
        <div class="policy-section" id="commitment">
          <h2><span class="ps-num">01</span> Our Commitment</h2>
          <p>At Genex, your satisfaction is our top priority. We are committed to supplying 100% genuine, high-quality products. In the rare event that something goes wrong, we aim to resolve your issue quickly and fairly.</p>
          <p>This policy applies to all purchases made through our online store, via WhatsApp orders, and in-store at our Kamburupitiya location.</p>
        </div>

        <!-- 02 -->
        <div class="policy-section" id="eligibility">
          <h2><span class="ps-num">02</span> Eligibility for Returns</h2>
          <p>To be eligible for a return, the following conditions must be met:</p>
          <ul>
            <li>The return request is made within <strong>7 calendar days</strong> of receiving the product.</li>
            <li>The item is in its original, unused condition - not installed, assembled, or modified.</li>
            <li>All original packaging, accessories, manuals, warranty cards, and seals are intact and undamaged.</li>
            <li>You have proof of purchase (invoice, WhatsApp order confirmation, or receipt).</li>
          </ul>
          <h3>Acceptable reasons for return:</h3>
          <ul>
            <li>Product is defective or not functioning as expected</li>
            <li>Wrong item received (different from what was ordered)</li>
            <li>Product was damaged during delivery</li>
            <li>Item is significantly different from its description</li>
          </ul>
          <div class="policy-highlight">
            <p>Change-of-mind returns are accepted only if the product is completely sealed and unused. A restocking fee of 5–10% may apply. Please contact us first to discuss.</p>
          </div>
        </div>

        <!-- 03 -->
        <div class="policy-section" id="non-returnable">
          <h2><span class="ps-num">03</span> Non-Returnable Items</h2>
          <p>The following items are not eligible for return unless they are defective or incorrectly supplied:</p>
          <ul>
            <li>Products that have been installed, used, or modified</li>
            <li>Products with broken seals or missing original packaging</li>
            <li>Software, digital downloads, and licence keys (once revealed/activated)</li>
            <li>Consumable items such as thermal paste, cleaning kits, cable ties</li>
            <li>Custom-built or specially ordered items configured to your specification</li>
            <li>Products damaged due to misuse, improper installation, or physical damage by the customer</li>
            <li>Items returned more than 7 days after delivery</li>
          </ul>
        </div>

        <!-- 04 -->
        <div class="policy-section" id="how-to-return">
          <h2><span class="ps-num">04</span> How to Return a Product</h2>
          <p>Follow these steps to initiate a return:</p>
          <ol>
            <li><strong>Contact us first</strong> - reach us on WhatsApp (<?= htmlspecialchars($phone) ?>) or email (<?= htmlspecialchars($email) ?>) within 7 days of receiving your item. Do not send the item back without prior approval.</li>
            <li><strong>Provide details</strong> - share your order number/invoice, the reason for return, and photos or a video of the issue if applicable.</li>
            <li><strong>Await approval</strong> - our team will review your request and confirm eligibility within 24 hours.</li>
            <li><strong>Return the item</strong> - once approved, securely pack the item in its original packaging and send it to our address. We recommend using a trackable courier.</li>
            <li><strong>Inspection</strong> - upon receipt, we will inspect the item within 2 business days.</li>
            <li><strong>Resolution</strong> - we will notify you of the outcome (replacement, repair, or refund) and process it promptly.</li>
          </ol>
          <h3>Return Address:</h3>
          <p style="background:var(--bg-2);border:1px solid var(--border);border-radius:var(--r);padding:16px 20px;color:var(--text-2)">
            <strong>Genex - Global Xperience</strong><br>
            Lenabatuwa, Kamburupitiya<br>
            Matara District, Sri Lanka - 81100<br>
            Phone: <?= htmlspecialchars($phone) ?>
          </p>
          <p style="margin-top:12px"><strong>Note:</strong> Return shipping costs are the customer's responsibility unless the return is due to a defect or error on our part, in which case we will cover the return courier charge.</p>
        </div>

        <!-- 05 -->
        <div class="policy-section" id="refunds">
          <h2><span class="ps-num">05</span> Refund Process</h2>
          <p>Once the returned item has been received and inspected, we will notify you via WhatsApp or email about the outcome.</p>
          <h3>If the return is approved:</h3>
          <ul>
            <li><strong>Replacement:</strong> We will dispatch a replacement product within 1–3 business days.</li>
            <li><strong>Refund:</strong> Refunds are processed within <strong>3–5 business days</strong> via bank transfer or original payment method.</li>
            <li><strong>Store Credit:</strong> With your agreement, we may issue store credit for future purchases.</li>
          </ul>
          <h3>If the return is rejected:</h3>
          <p>If the returned item does not meet the eligibility criteria (e.g., shows signs of use or damage by the customer), we will notify you and return the item to you at your cost.</p>
          <div class="policy-highlight">
            <p>Original delivery charges are non-refundable unless the return is due to a defective or incorrect item sent by us.</p>
          </div>
        </div>

        <!-- 06 -->
        <div class="policy-section" id="damaged">
          <h2><span class="ps-num">06</span> Damaged or Defective Items</h2>
          <p>If your order arrives damaged or is found to be defective upon first use, please act immediately:</p>
          <ul>
            <li>Photograph the damaged packaging and product before opening further.</li>
            <li>Contact us within <strong>24 hours</strong> of delivery for delivery damage, or within 7 days for defects discovered on first use.</li>
            <li>We will arrange a free replacement or full refund including shipping costs.</li>
          </ul>
          <p>For warranty claims after the 7-day return window, the product will be assessed and handled through the manufacturer's warranty process. We will assist you throughout this process.</p>
        </div>

        <!-- 07 -->
        <div class="policy-section" id="contact">
          <h2><span class="ps-num">07</span> Contact Us</h2>
          <p>For any return or refund queries, reach us through the following channels. We aim to respond within a few hours during business hours.</p>
          <div class="policy-contact-box">
            <div class="pcb-ico"><i class="fab fa-whatsapp"></i></div>
            <div class="pcb-text">
              <strong>WhatsApp (Fastest)</strong>
              <span>Available Mon–Sat, 8:00 AM – 7:00 PM</span>
            </div>
            <div class="pcb-links">
              <a href="https://wa.me/<?= htmlspecialchars($phoneClean) ?>" target="_blank" rel="noopener" class="btn btn-gold btn-sm"><i class="fab fa-whatsapp"></i> Chat Now</a>
              <a href="contact.php" class="btn btn-ghost btn-sm"><i class="fas fa-envelope"></i> Email Us</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<div id="footer-slot"></div>
<script src="components/footer.js"></script>

<button class="scroll-top" id="scrollTopBtn" aria-label="Scroll to top">
  <i class="fas fa-chevron-up"></i>
</button>

<script src="assets/js/main.js"></script>
<script>
const sections = document.querySelectorAll('.policy-section[id]');
const tocLinks = document.querySelectorAll('.policy-toc a');
window.addEventListener('scroll', () => {
  let cur = '';
  sections.forEach(s => { if (scrollY >= s.offsetTop - 130) cur = s.id; });
  tocLinks.forEach(a => {
    a.classList.toggle('toc-active', a.getAttribute('href') === '#' + cur);
  });
}, { passive: true });
</script>
</body>
</html>
