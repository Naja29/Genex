<?php
require_once __DIR__ . '/includes/functions.php';

$phone      = getSetting('store_whatsapp', '94777237962');
$phoneClean = preg_replace('/\D/', '', $phone);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Genex Shipping & Delivery Policy - Island-wide delivery across Sri Lanka. Learn about delivery times, charges, tracking and more.">
  <title>Shipping Policy | Genex - Global Xperience</title>

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
      <h1>Shipping &amp; Delivery <em>Policy</em></h1>
      <p>Fast, reliable island-wide delivery. Here's everything you need to know about how we ship your order.</p>
    </div>
    <nav class="breadcrumb" aria-label="Breadcrumb" data-anim="up" data-delay="2">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Shipping Policy</span>
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
          <li><a href="#coverage"><span class="toc-num">01</span> Delivery Coverage</a></li>
          <li><a href="#processing"><span class="toc-num">02</span> Order Processing</a></li>
          <li><a href="#timeframes"><span class="toc-num">03</span> Delivery Timeframes</a></li>
          <li><a href="#charges"><span class="toc-num">04</span> Shipping Charges</a></li>
          <li><a href="#tracking"><span class="toc-num">05</span> Order Tracking</a></li>
          <li><a href="#failed"><span class="toc-num">06</span> Failed Deliveries</a></li>
          <li><a href="#large"><span class="toc-num">07</span> Large & Fragile Items</a></li>
          <li><a href="#contact"><span class="toc-num">08</span> Contact Us</a></li>
        </ul>
      </aside>

      <!-- Body -->
      <div data-anim="right">
        <div class="policy-last-updated"><i class="fas fa-calendar-alt"></i> Last Updated: May 2025</div>

        <div class="policy-highlight">
          <p><i class="fas fa-truck" style="color:var(--primary);margin-right:8px"></i> <strong>Quick Summary:</strong> We deliver island-wide within 1–3 business days. Free delivery on orders over Rs. 50,000. All orders are tracked and dispatched within 24 hours of confirmation.</p>
        </div>

        <!-- 01 -->
        <div class="policy-section" id="coverage">
          <h2><span class="ps-num">01</span> Delivery Coverage</h2>
          <p>Genex delivers to all 25 districts across Sri Lanka. We use trusted courier partners to ensure your order reaches you safely wherever you are on the island - from Colombo to Jaffna, Kandy to Hambantota.</p>
          <p>We currently do <strong>not</strong> offer international shipping. All deliveries are within Sri Lanka only.</p>
          <h3>Our Courier Partners</h3>
          <ul>
            <li>Pronto Delivery Services</li>
            <li>Kapruka Courier</li>
            <li>Lanka Sathosa Courier (for selected areas)</li>
            <li>Direct delivery for Matara district and nearby areas</li>
          </ul>
        </div>

        <!-- 02 -->
        <div class="policy-section" id="processing">
          <h2><span class="ps-num">02</span> Order Processing</h2>
          <p>Orders are processed after payment confirmation (or cash-on-delivery arrangement). Here is how it works:</p>
          <ol>
            <li>You send your order via WhatsApp or through our online cart.</li>
            <li>We confirm availability and the final price within a few hours.</li>
            <li>You confirm the order and arrange payment.</li>
            <li>We pack and dispatch your order - typically within <strong>same day or next business day</strong>.</li>
          </ol>
          <div class="policy-highlight">
            <p>Orders placed and confirmed before <strong>12:00 PM (noon)</strong> on business days are usually dispatched the same day. Orders confirmed after noon or over the weekend are dispatched the following business day.</p>
          </div>
          <p>Business days are Monday to Saturday. We do not process or dispatch orders on Sundays or public holidays.</p>
        </div>

        <!-- 03 -->
        <div class="policy-section" id="timeframes">
          <h2><span class="ps-num">03</span> Delivery Timeframes</h2>
          <p>Estimated delivery times from the date of dispatch:</p>

          <div style="overflow-x:auto;margin:16px 0">
            <table style="width:100%;border-collapse:collapse;font-size:13.5px">
              <thead>
                <tr style="background:var(--bg-3);color:var(--text)">
                  <th style="padding:12px 16px;text-align:left;border-bottom:1px solid var(--border)">Region</th>
                  <th style="padding:12px 16px;text-align:left;border-bottom:1px solid var(--border)">Districts</th>
                  <th style="padding:12px 16px;text-align:left;border-bottom:1px solid var(--border)">Estimated Time</th>
                </tr>
              </thead>
              <tbody style="color:var(--text-muted)">
                <tr style="border-bottom:1px solid var(--border)">
                  <td style="padding:12px 16px"><strong style="color:var(--primary)">Southern Province</strong></td>
                  <td style="padding:12px 16px">Matara, Galle, Hambantota</td>
                  <td style="padding:12px 16px">Same day - 1 business day</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border);background:rgba(255,255,255,.02)">
                  <td style="padding:12px 16px"><strong style="color:var(--text-2)">Western Province</strong></td>
                  <td style="padding:12px 16px">Colombo, Gampaha, Kalutara</td>
                  <td style="padding:12px 16px">1-2 business days</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border)">
                  <td style="padding:12px 16px"><strong style="color:var(--text-2)">Central &amp; Sabaragamuwa</strong></td>
                  <td style="padding:12px 16px">Kandy, Nuwara Eliya, Kegalle, Ratnapura</td>
                  <td style="padding:12px 16px">1-2 business days</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border);background:rgba(255,255,255,.02)">
                  <td style="padding:12px 16px"><strong style="color:var(--text-2)">North Western &amp; North Central</strong></td>
                  <td style="padding:12px 16px">Kurunegala, Puttalam, Anuradhapura, Polonnaruwa</td>
                  <td style="padding:12px 16px">2-3 business days</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border)">
                  <td style="padding:12px 16px"><strong style="color:var(--text-2)">Uva &amp; Eastern</strong></td>
                  <td style="padding:12px 16px">Badulla, Monaragala, Ampara, Batticaloa, Trincomalee</td>
                  <td style="padding:12px 16px">2-3 business days</td>
                </tr>
                <tr style="background:rgba(255,255,255,.02)">
                  <td style="padding:12px 16px"><strong style="color:var(--text-2)">Northern Province</strong></td>
                  <td style="padding:12px 16px">Jaffna, Kilinochchi, Mannar, Mullaitivu, Vavuniya</td>
                  <td style="padding:12px 16px">3-4 business days</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p>These are estimates and actual delivery may vary based on courier capacity, weather conditions, and other factors outside our control.</p>
        </div>

        <!-- 04 -->
        <div class="policy-section" id="charges">
          <h2><span class="ps-num">04</span> Shipping Charges</h2>
          <p>Shipping charges are calculated based on the weight, size and destination of your order:</p>
          <ul>
            <li><strong>Small items</strong> (e.g., RAM, cables, accessories under 500g): Rs. 300 - Rs. 500</li>
            <li><strong>Medium items</strong> (e.g., hard drives, keyboards, mice, 500g-2kg): Rs. 500 - Rs. 800</li>
            <li><strong>Large items</strong> (e.g., monitors, PC cases, 2kg+): Rs. 800 - Rs. 1,500</li>
          </ul>
          <div class="policy-highlight">
            <p><i class="fas fa-star" style="color:var(--primary);margin-right:6px"></i> <strong>Free Delivery</strong> on all orders over <strong>Rs. 50,000</strong> within Sri Lanka.</p>
          </div>
          <p>The exact shipping cost will be confirmed via WhatsApp before you finalise your order. There are no hidden charges - you only pay what is agreed.</p>
        </div>

        <!-- 05 -->
        <div class="policy-section" id="tracking">
          <h2><span class="ps-num">05</span> Order Tracking</h2>
          <p>Once your order has been dispatched, we will send you the tracking number and courier details via WhatsApp. You can use this to track your parcel in real time on the courier's website.</p>
          <p>If you have difficulty tracking your order or have not received a tracking update within 24 hours of dispatch, please contact us and we will look into it immediately.</p>
        </div>

        <!-- 06 -->
        <div class="policy-section" id="failed">
          <h2><span class="ps-num">06</span> Failed or Missed Deliveries</h2>
          <p>If a delivery attempt is unsuccessful (e.g., no one available, incorrect address), the courier will typically:</p>
          <ol>
            <li>Contact you on the phone number provided to arrange re-delivery.</li>
            <li>Hold the parcel at a nearby depot for collection (usually 3-5 days).</li>
            <li>Return the parcel to us if uncollected.</li>
          </ol>
          <p>If the parcel is returned to us due to a failed delivery that was not our fault (wrong address provided, uncollected), re-delivery charges will apply. Please ensure your delivery address and phone number are accurate when placing an order.</p>
          <div class="policy-highlight">
            <p>If your parcel was not delivered and you believe the courier made an error, contact us immediately. We will investigate with the courier on your behalf.</p>
          </div>
        </div>

        <!-- 07 -->
        <div class="policy-section" id="large">
          <h2><span class="ps-num">07</span> Large &amp; Fragile Items</h2>
          <p>For large or fragile items such as monitors, UPS units and PC towers, we take extra care with packaging to ensure safe delivery. These items are double-boxed and secured with foam padding.</p>
          <p>Please inspect your order upon delivery before signing the courier's receipt. If you notice any external packaging damage, note it on the receipt and contact us immediately with photos. This helps us process damage claims faster.</p>
        </div>

        <!-- 08 -->
        <div class="policy-section" id="contact">
          <h2><span class="ps-num">08</span> Contact Us</h2>
          <p>For all shipping and delivery queries, contact our team directly. We are happy to provide updates, track your order or resolve any delivery issues.</p>
          <div class="policy-contact-box">
            <div class="pcb-ico"><i class="fas fa-truck"></i></div>
            <div class="pcb-text">
              <strong>Delivery Support</strong>
              <span>Mon - Sat, 8:00 AM - 7:00 PM</span>
            </div>
            <div class="pcb-links">
              <a href="https://wa.me/<?= htmlspecialchars($phoneClean) ?>" target="_blank" rel="noopener" class="btn btn-gold btn-sm"><i class="fab fa-whatsapp"></i> WhatsApp</a>
              <a href="contact.php" class="btn btn-ghost btn-sm"><i class="fas fa-envelope"></i> Contact Form</a>
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
