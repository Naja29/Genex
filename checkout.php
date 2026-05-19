<?php
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout | Genex - Global Xperience</title>
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

<!-- Breadcrumb -->
<div style="background:var(--bg-2);border-bottom:1px solid var(--border);padding:12px 0">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <a href="cart.php">Cart</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Checkout</span>
    </nav>
  </div>
</div>

<!-- Page Hero -->
<section class="page-hero" style="padding:40px 0 36px">
  <div class="hero-dot-bg"></div>
  <div class="page-hero-orb page-hero-orb-1"></div>
  <div class="container page-hero-inner">
    <div class="page-hero-text" data-anim="up">
      <span class="section-tag">Almost There</span>
      <h1>Complete Your <em>Order</em></h1>
    </div>
  </div>
</section>

<!-- Main -->
<section class="section" style="padding-top:36px">
  <div class="container">

    <!-- Empty cart redirect notice -->
    <div id="emptyNotice" style="display:none;text-align:center;padding:60px 20px">
      <i class="fas fa-shopping-cart" style="font-size:48px;color:var(--text-dim);margin-bottom:16px;display:block"></i>
      <h3 style="margin-bottom:8px">Your cart is empty</h3>
      <p style="color:var(--text-muted);margin-bottom:20px">Add items to your cart before checking out.</p>
      <a href="shop.php" class="btn btn-primary"><i class="fas fa-store"></i> Browse Products</a>
    </div>

    <!-- Success state -->
    <div id="successState" style="display:none;text-align:center;padding:60px 20px;max-width:540px;margin:0 auto">
      <div style="width:72px;height:72px;border-radius:50%;background:rgba(16,185,129,.12);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
        <i class="fas fa-check-circle" style="font-size:36px;color:#10b981"></i>
      </div>
      <h2 style="margin-bottom:8px">Order Placed!</h2>
      <p style="color:var(--text-muted);margin-bottom:16px">Thank you for your order. We've received it and will contact you shortly via WhatsApp to confirm.</p>
      <div id="successOrderNum" style="background:var(--bg-2);border:1px solid var(--border);border-left:4px solid var(--primary);border-radius:8px;padding:14px 20px;margin-bottom:24px;text-align:left">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);margin-bottom:4px">Your Order Number</div>
        <div id="orderNumText" style="font-size:22px;font-weight:800;color:var(--text)"></div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Save this for your reference</div>
      </div>
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:24px">
        <i class="fas fa-envelope" style="color:var(--primary)"></i>
        A confirmation email has been sent if you provided your email address.
      </p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="shop.php" class="btn btn-ghost"><i class="fas fa-store"></i> Continue Shopping</a>
        <a href="index.php" class="btn btn-primary"><i class="fas fa-home"></i> Go Home</a>
      </div>
    </div>

    <!-- Checkout grid -->
    <div id="checkoutGrid" style="display:grid;grid-template-columns:1fr 360px;gap:28px;align-items:start">

      <!-- LEFT: Customer form -->
      <div>
        <!-- Error banner -->
        <div id="errBanner" style="display:none;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#f87171;font-size:13.5px">
          <i class="fas fa-exclamation-circle"></i> <span id="errText"></span>
        </div>

        <div class="card" style="margin-bottom:20px">
          <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:20px;display:flex;align-items:center;gap:8px">
            <i class="fas fa-user" style="color:var(--primary)"></i> Your Details
          </div>

          <form id="checkoutForm">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
              <div>
                <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                  Full Name <span style="color:var(--red)">*</span>
                </label>
                <input type="text" id="ckName" class="form-input" placeholder="Your full name" required>
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                  Phone Number <span style="color:var(--red)">*</span>
                </label>
                <input type="tel" id="ckPhone" class="form-input" placeholder="e.g. 077 000 0000" required>
              </div>
            </div>

            <div style="margin-bottom:16px">
              <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                Email Address <span style="font-weight:400;text-transform:none;font-size:11px">(optional - for confirmation email)</span>
              </label>
              <input type="email" id="ckEmail" class="form-input" placeholder="your@email.com">
            </div>

            <div style="margin-bottom:16px">
              <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                Delivery Address <span style="color:var(--red)">*</span>
              </label>
              <textarea id="ckAddress" class="form-textarea" rows="3" placeholder="Your full delivery address including city and postal code" required></textarea>
            </div>

            <div style="margin-bottom:24px">
              <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">
                Order Notes <span style="font-weight:400;text-transform:none;font-size:11px">(optional)</span>
              </label>
              <textarea id="ckNotes" class="form-textarea" rows="2" placeholder="Special requests, colour preferences, delivery instructions..."></textarea>
            </div>

            <button type="submit" id="placeBtn" class="btn btn-primary" style="width:100%;justify-content:center;font-size:15px;padding:14px">
              <i class="fas fa-shopping-bag"></i> Place Order
            </button>
          </form>
        </div>

        <!-- Trust badges -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
          <div style="background:var(--bg-2);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center">
            <i class="fas fa-shield-alt" style="font-size:20px;color:var(--primary);margin-bottom:6px;display:block"></i>
            <div style="font-size:11.5px;font-weight:600;color:var(--text)">100% Genuine</div>
            <div style="font-size:10.5px;color:var(--text-muted)">Authentic products</div>
          </div>
          <div style="background:var(--bg-2);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center">
            <i class="fas fa-truck" style="font-size:20px;color:var(--primary);margin-bottom:6px;display:block"></i>
            <div style="font-size:11.5px;font-weight:600;color:var(--text)">Island-wide Delivery</div>
            <div style="font-size:10.5px;color:var(--text-muted)">Sri Lanka</div>
          </div>
          <div style="background:var(--bg-2);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center">
            <i class="fas fa-undo" style="font-size:20px;color:var(--primary);margin-bottom:6px;display:block"></i>
            <div style="font-size:11.5px;font-weight:600;color:var(--text)">Easy Returns</div>
            <div style="font-size:10.5px;color:var(--text-muted)">7-day policy</div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Order summary -->
      <div style="position:sticky;top:80px">
        <div class="card">
          <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:16px;display:flex;align-items:center;gap:8px">
            <i class="fas fa-receipt" style="color:var(--primary)"></i> Order Summary
          </div>

          <div id="ckItemsList" style="margin-bottom:16px;display:flex;flex-direction:column;gap:10px">
            <!-- rendered by JS -->
          </div>

          <div style="border-top:1px solid var(--border);padding-top:14px;margin-bottom:16px">
            <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);margin-bottom:8px">
              <span>Subtotal</span><span id="ckSubtotal">Rs. 0.00</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);margin-bottom:12px">
              <span>Delivery</span><span style="color:var(--green)">Confirmed on order</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:17px;font-weight:800;color:var(--text)">
              <span>Total</span><span id="ckTotal" style="color:var(--primary)">Rs. 0.00</span>
            </div>
          </div>

          <div style="background:rgba(212,146,10,.06);border:1px solid rgba(212,146,10,.2);border-radius:8px;padding:12px 14px;font-size:12px;color:var(--text-muted);line-height:1.7">
            <i class="fas fa-info-circle" style="color:var(--primary)"></i>
            We'll confirm availability and final price via WhatsApp before delivery. <strong>No online payment required.</strong>
          </div>
        </div>

        <a href="cart.php" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:12px">
          <i class="fas fa-arrow-left"></i> Edit Cart
        </a>
      </div>

    </div>
  </div>
</section>

<div id="footer-slot"></div>
<script src="components/footer.js"></script>
<script src="assets/js/animations.js"></script>

<style>
.form-input,.form-textarea{
  width:100%;background:var(--bg-3);border:1px solid var(--border);border-radius:8px;
  padding:11px 14px;color:var(--text);font-family:'Poppins',sans-serif;font-size:13.5px;
  outline:none;transition:.2s;
}
.form-input:focus,.form-textarea:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(212,146,10,.12)}
.form-textarea{resize:vertical;min-height:80px}
.btn-primary{background:var(--gold);color:#fff;border:none;border-radius:50px;padding:11px 24px;font-weight:700;font-size:13.5px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:.2s}
.btn-primary:hover{background:var(--primary-dark,#b8780a);transform:translateY(-1px)}
.btn-primary:disabled{opacity:.6;cursor:not-allowed;transform:none}
@media(max-width:860px){
  #checkoutGrid{grid-template-columns:1fr!important}
  #checkoutGrid>div:last-child{position:static!important}
}
</style>

<script>
(function () {
  const items    = GenexCart.getItems();
  const grid     = document.getElementById('checkoutGrid');
  const empty    = document.getElementById('emptyNotice');
  const success  = document.getElementById('successState');
  const errBanner= document.getElementById('errBanner');
  const errText  = document.getElementById('errText');

  if (!items.length) {
    grid.style.display  = 'none';
    empty.style.display = 'block';
    return;
  }

  const list     = document.getElementById('ckItemsList');
  const subtotal = GenexCart.getTotal();

  items.forEach(item => {
    const div = document.createElement('div');
    div.style.cssText = 'display:flex;justify-content:space-between;align-items:start;gap:10px';
    div.innerHTML = `
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:600;color:var(--text);line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${item.name}</div>
        <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px">Qty: ${item.qty} × ${GenexCart.fmt(item.price)}</div>
      </div>
      <div style="font-size:13px;font-weight:700;color:var(--primary);flex-shrink:0">${GenexCart.fmt(item.price * item.qty)}</div>
    `;
    list.appendChild(div);
  });

  document.getElementById('ckSubtotal').textContent = GenexCart.fmt(subtotal);
  document.getElementById('ckTotal').textContent    = GenexCart.fmt(subtotal);

  document.getElementById('checkoutForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    errBanner.style.display = 'none';

    const name    = document.getElementById('ckName').value.trim();
    const phone   = document.getElementById('ckPhone').value.trim();
    const email   = document.getElementById('ckEmail').value.trim();
    const address = document.getElementById('ckAddress').value.trim();
    const notes   = document.getElementById('ckNotes').value.trim();

    if (!name || !phone || !address) {
      showError('Please fill in all required fields.');
      return;
    }

    const btn = document.getElementById('placeBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing Order...';

    try {
      const res = await fetch('api/checkout.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ name, phone, email, address, notes, cart: items }),
      });

      const data = await res.json();

      if (data.success) {
        GenexCart.clear();
        grid.style.display    = 'none';
        success.style.display = 'block';
        document.getElementById('orderNumText').textContent = data.order_number;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        showError(data.error || 'Something went wrong. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-shopping-bag"></i> Place Order';
      }
    } catch (err) {
      showError('Network error. Please check your connection and try again.');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-shopping-bag"></i> Place Order';
    }
  });

  function showError(msg) {
    errText.textContent     = msg;
    errBanner.style.display = 'block';
    errBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
})();
</script>

</body>
</html>
