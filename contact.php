<?php
require_once __DIR__ . '/includes/functions.php';

$phone      = getSetting('store_whatsapp', '94777237962');
$phoneClean = preg_replace('/\D/', '', $phone);
$phoneFmt   = '+' . substr($phoneClean, 0, 2) . ' ' . substr($phoneClean, 2, 2) . ' ' . substr($phoneClean, 4, 3) . ' ' . substr($phoneClean, 7);
$email      = getSetting('store_email', 'genecoretech@gmail.com');
$address    = getSetting('store_address', 'Lenabatuwa, Kamburupitiya, Sri Lanka - 81100');
$hours      = getSetting('store_hours', 'Mon - Sat: 8:00 AM - 7:00 PM');
$facebook   = getSetting('facebook_url', 'https://web.facebook.com/genecoretech');
$instagram  = getSetting('instagram_url', '#');
$youtube    = getSetting('youtube_url', '#');
$tiktok     = getSetting('tiktok_url', '#');
$mapEmbed   = getSetting('store_map_embed', 'https://maps.google.com/maps?q=Kamburupitiya,Sri+Lanka&output=embed');
$mapLink    = getSetting('store_map_link', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Contact Genex - Get in touch for wholesale inquiries, product questions or support. Call, WhatsApp or visit us in Kamburupitiya, Sri Lanka.">
  <title>Contact Us | Genex - Global Xperience</title>
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
      <span class="section-tag">Get In Touch</span>
      <h1>Contact <em>Us</em></h1>
      <p>Have a question, wholesale inquiry or need help choosing the right product? We're here for you.</p>
    </div>
    <nav class="breadcrumb" aria-label="Breadcrumb" data-anim="up" data-delay="2">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Contact Us</span>
    </nav>
  </div>
</section>

<!-- CONTACT INFO CARDS -->
<section class="section" style="padding-bottom:0">
  <div class="container">
    <div class="contact-cards-grid">

      <div class="contact-card" data-anim="up" data-delay="1">
        <div class="cc-ico" style="--cic:rgba(212,146,10,.12);--cicc:#d4920a"><i class="fas fa-map-marker-alt"></i></div>
        <div class="cc-body">
          <strong>Our Location</strong>
          <span><?= nl2br(htmlspecialchars($address)) ?></span>
        </div>
      </div>

      <div class="contact-card" data-anim="up" data-delay="2">
        <div class="cc-ico" style="--cic:rgba(37,211,102,.12);--cicc:#25d366"><i class="fab fa-whatsapp"></i></div>
        <div class="cc-body">
          <strong>WhatsApp / Call</strong>
          <a href="https://wa.me/<?= htmlspecialchars($phoneClean) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($phoneFmt) ?></a>
        </div>
      </div>

      <div class="contact-card" data-anim="up" data-delay="3">
        <div class="cc-ico" style="--cic:rgba(59,130,246,.12);--cicc:#3b82f6"><i class="fas fa-envelope"></i></div>
        <div class="cc-body">
          <strong>Email Us</strong>
          <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a>
        </div>
      </div>

      <div class="contact-card" data-anim="up" data-delay="4">
        <div class="cc-ico" style="--cic:rgba(168,85,247,.12);--cicc:#a855f7"><i class="fas fa-clock"></i></div>
        <div class="cc-body">
          <strong>Opening Hours</strong>
          <span><?= nl2br(htmlspecialchars($hours)) ?><br>Sunday: Closed</span>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- CONTACT FORM + MAP -->
<section class="section">
  <div class="container">
    <div class="contact-main-grid">

      <!-- Form -->
      <div class="contact-form-wrap" data-anim="left">
        <div class="cf-header">
          <span class="section-tag">Send a Message</span>
          <h2 class="section-title" style="text-align:left;margin-bottom:6px">We'd Love to <em>Hear From You</em></h2>
          <p style="color:var(--text-muted);font-size:13.5px">Fill in the form below and we'll get back to you as soon as possible.</p>
        </div>

        <form class="contact-form" id="contactForm" novalidate>
          <div class="cf-row">
            <div class="cf-group">
              <label for="cf-name">Full Name <span class="cf-req">*</span></label>
              <div class="cf-input-wrap">
                <i class="fas fa-user"></i>
                <input type="text" id="cf-name" placeholder="Your full name" required>
              </div>
            </div>
            <div class="cf-group">
              <label for="cf-phone">Phone / WhatsApp</label>
              <div class="cf-input-wrap">
                <i class="fab fa-whatsapp"></i>
                <input type="tel" id="cf-phone" placeholder="+94 77 000 0000">
              </div>
            </div>
          </div>

          <div class="cf-group">
            <label for="cf-email">Email Address <span class="cf-req">*</span></label>
            <div class="cf-input-wrap">
              <i class="fas fa-envelope"></i>
              <input type="email" id="cf-email" placeholder="your@email.com" required>
            </div>
          </div>

          <div class="cf-group">
            <label for="cf-subject">Subject</label>
            <div class="cf-input-wrap">
              <i class="fas fa-tag"></i>
              <select id="cf-subject">
                <option value="" disabled selected>Select a subject</option>
                <option>Product Inquiry</option>
                <option>Wholesale / Bulk Order</option>
                <option>Price Quote</option>
                <option>After-Sales Support</option>
                <option>Delivery Inquiry</option>
                <option>Other</option>
              </select>
            </div>
          </div>

          <div class="cf-group">
            <label for="cf-message">Message <span class="cf-req">*</span></label>
            <div class="cf-input-wrap cf-textarea-wrap">
              <i class="fas fa-comment-alt"></i>
              <textarea id="cf-message" rows="5" placeholder="Write your message here..." required></textarea>
            </div>
          </div>

          <button type="submit" class="btn btn-gold cf-submit">
            <i class="fas fa-paper-plane"></i> Send Message
          </button>

          <div class="cf-success" id="cfSuccess">
            <i class="fas fa-check-circle"></i>
            <span>Thank you! Your message has been sent. We'll get back to you shortly.</span>
          </div>
        </form>

        <!-- Quick contact options -->
        <div class="cf-quick">
          <p>Prefer a faster response?</p>
          <a href="https://wa.me/<?= htmlspecialchars($phoneClean) ?>" target="_blank" rel="noopener" class="btn btn-green btn-sm"><i class="fab fa-whatsapp"></i> Chat on WhatsApp</a>
          <a href="tel:+<?= htmlspecialchars($phoneClean) ?>" class="btn btn-ghost btn-sm"><i class="fas fa-phone-alt"></i> Call Now</a>
        </div>
      </div>

      <!-- Map -->
      <div class="contact-map-wrap" data-anim="right">
        <div class="contact-map">
          <iframe
            src="<?= htmlspecialchars($mapEmbed) ?>"
            width="100%" height="100%" style="border:0;filter:invert(90%) hue-rotate(180deg)"
            allowfullscreen loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="Genex Store Location">
          </iframe>
        </div>
        <?php if ($mapLink): ?>
        <a href="<?= htmlspecialchars($mapLink) ?>" target="_blank" rel="noopener"
           class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;margin-top:12px">
          <i class="fas fa-directions" style="color:#4285f4"></i> Get Directions on Google Maps
        </a>
        <?php endif ?>
        <div class="contact-social">
          <p>Follow us on social media</p>
          <div class="socials">
            <?php if ($facebook && $facebook !== '#'): ?>
            <a href="<?= htmlspecialchars($facebook) ?>" target="_blank" rel="noopener" class="soc-btn" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <?php endif ?>
            <?php if ($instagram && $instagram !== '#'): ?>
            <a href="<?= htmlspecialchars($instagram) ?>" target="_blank" rel="noopener" class="soc-btn" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <?php else: ?>
            <a href="#" class="soc-btn" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <?php endif ?>
            <a href="https://wa.me/<?= htmlspecialchars($phoneClean) ?>" target="_blank" rel="noopener" class="soc-btn" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            <?php if ($youtube && $youtube !== '#'): ?>
            <a href="<?= htmlspecialchars($youtube) ?>" target="_blank" rel="noopener" class="soc-btn" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            <?php else: ?>
            <a href="#" class="soc-btn" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            <?php endif ?>
            <?php if ($tiktok && $tiktok !== '#'): ?>
            <a href="<?= htmlspecialchars($tiktok) ?>" target="_blank" rel="noopener" class="soc-btn" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
            <?php else: ?>
            <a href="#" class="soc-btn" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
            <?php endif ?>
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
document.getElementById('contactForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const nameEl    = document.getElementById('cf-name');
  const emailEl   = document.getElementById('cf-email');
  const messageEl = document.getElementById('cf-message');
  let valid = true;

  [nameEl, emailEl, messageEl].forEach(el => {
    if (!el.value.trim()) {
      el.closest('.cf-input-wrap').classList.add('cf-error');
      valid = false;
    } else {
      el.closest('.cf-input-wrap').classList.remove('cf-error');
    }
  });
  if (!valid) return;

  const btn = this.querySelector('.cf-submit');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
  btn.disabled  = true;

  fetch('api/contact.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      name:    nameEl.value.trim(),
      phone:   document.getElementById('cf-phone').value.trim(),
      email:   emailEl.value.trim(),
      subject: document.getElementById('cf-subject').value,
      message: messageEl.value.trim(),
    })
  })
  .then(r => r.json())
  .then(res => {
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message';
    btn.disabled  = false;
    if (res.success) {
      document.getElementById('contactForm').reset();
      document.getElementById('cfSuccess').classList.add('show');
      setTimeout(() => document.getElementById('cfSuccess').classList.remove('show'), 6000);
    } else {
      alert(res.error || 'Failed to send. Please try again.');
    }
  })
  .catch(() => {
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message';
    btn.disabled  = false;
    alert('Network error. Please try again.');
  });
});

document.querySelectorAll('.cf-input-wrap input, .cf-input-wrap textarea').forEach(el => {
  el.addEventListener('input', () => el.closest('.cf-input-wrap').classList.remove('cf-error'));
});
</script>
</body>
</html>
