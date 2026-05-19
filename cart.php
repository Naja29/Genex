<?php
require_once __DIR__ . '/includes/functions.php';
$db = getDB();

$waNumber = getSetting('store_whatsapp', '94777237962');
$waClean  = preg_replace('/\D/', '', $waNumber);

// "You Might Also Like" - pick 4 featured/random active products
$related = $db->query('
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
    ORDER BY p.is_featured DESC, RAND()
    LIMIT 4
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Cart | Genex - Global Xperience</title>
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
      <span>Your Cart</span>
    </nav>
  </div>
</div>

<!-- Page Hero -->
<section class="page-hero" style="padding:40px 0 36px">
  <div class="hero-dot-bg"></div>
  <div class="page-hero-orb page-hero-orb-1"></div>
  <div class="container page-hero-inner">
    <div class="page-hero-text" data-anim="up">
      <span class="section-tag">Review &amp; Order</span>
      <h1>Your <em>Cart</em></h1>
    </div>
  </div>
</section>

<!-- Cart Content -->
<section class="section" style="padding-top:32px">
  <div class="container">

    <!-- Empty state (shown by JS if needed) -->
    <div id="cartEmptyState" style="display:none">
      <div class="cart-empty-state">
        <i class="fas fa-shopping-cart"></i>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added anything yet.</p>
        <a href="shop.php" class="btn btn-primary" style="margin-top:8px"><i class="fas fa-store"></i> Browse Products</a>
      </div>
    </div>

    <!-- Cart layout (shown when has items) -->
    <div class="cart-page-layout" id="cartPageLayout">

      <!-- Items table -->
      <div>
        <div class="cart-table-wrap">
          <div class="cart-table-head">
            <span>Product</span>
            <span style="text-align:center">Price</span>
            <span style="text-align:center">Quantity</span>
            <span></span>
          </div>
          <div class="cart-table-body" id="cartTableBody">
            <!-- rows rendered by JS -->
          </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;flex-wrap:wrap;gap:12px">
          <a href="shop.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
          <button class="cart-clear-btn" id="cartClearBtn" type="button"><i class="fas fa-trash-alt"></i> Clear Cart</button>
        </div>
      </div>

      <!-- Summary -->
      <div class="cart-summary-card" id="cartSummaryCard">
        <h3>Order Summary</h3>

        <div class="cart-summary-row">
          <span id="summaryItemCount">0 items</span>
          <strong id="summarySubtotal">Rs. 0</strong>
        </div>
        <div class="cart-summary-row">
          <span>Delivery</span>
          <strong style="color:var(--text-dim)">Calculated on order</strong>
        </div>

        <div class="cart-summary-total">
          <span>Total</span>
          <strong id="summaryTotal">Rs. 0</strong>
        </div>

        <p class="cart-summary-note">
          <i class="fas fa-shield-alt"></i> All products are 100% genuine with warranty
        </p>

        <div class="cart-summary-actions">
          <a href="checkout.php" class="btn btn-primary" style="justify-content:center">
            <i class="fas fa-shopping-bag"></i> Place Order Online
          </a>
          <a id="waOrderBtn" href="#" target="_blank" rel="noopener" class="btn btn-green" style="justify-content:center">
            <i class="fab fa-whatsapp"></i> Order via WhatsApp
          </a>
          <a href="contact.php" class="btn btn-ghost" style="justify-content:center">
            <i class="fas fa-envelope"></i> Send Inquiry Instead
          </a>
        </div>

        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
          <p style="font-size:11.5px;color:var(--text-dim);line-height:1.7">
            <i class="fas fa-info-circle" style="color:var(--primary)"></i>
            We'll confirm availability, final price and delivery details via WhatsApp before you pay. No online payment required.
          </p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- You Might Also Like -->
<?php if ($related): ?>
<section class="section section-dark" id="relatedSection">
  <div class="container">
    <div class="section-header" data-anim="up">
      <span class="section-tag">Keep Shopping</span>
      <h2 class="section-title">You Might <em>Also Like</em></h2>
    </div>
    <div class="products-grid">
      <?php foreach ($related as $p):
        $inStock  = (int)($p['in_stock'] ?? 1);
        $onSale   = $p['old_price'] ? 1 : 0;
        $badge    = $p['badge'] ?? '';
        $price    = (float)$p['price'];
        $oldPrice = $p['old_price'] ? (float)$p['old_price'] : null;
        $name     = htmlspecialchars($p['name']);
        $catSlug  = htmlspecialchars($p['cat_slug']);
        $brand    = strtolower(htmlspecialchars($p['brand'] ?? ''));
        $icon     = htmlspecialchars($p['icon'] ?? 'fas fa-box');
        $thumb    = $p['thumbnail'] ?? '';
        $hasThumb = $thumb && file_exists(__DIR__ . '/' . $thumb);
        $imgUrl   = $hasThumb ? htmlspecialchars(BASE_URL . $thumb) : '';
        $link     = 'product.php?id=' . (int)$p['id'];
        $badgeCls = match($badge) { 'HOT'=>'hot','NEW'=>'new','SALE'=>'sale', default=>'' };
      ?>
      <div class="product-card"
        data-category="<?= $catSlug ?>"
        data-brand="<?= $brand ?>"
        data-price="<?= (int)$price ?>"
        data-name="<?= $name ?>"
        data-instock="<?= $inStock ?>"
        data-sale="<?= $onSale ?>">

        <div class="product-img-area">
          <span class="stock-tag <?= $inStock ? 'in-stock' : 'out-stock' ?>">
            <?= $inStock ? 'In Stock' : 'Out of Stock' ?>
          </span>
          <?php if ($badge): ?>
            <span class="p-badge <?= $badgeCls ?>"><?= htmlspecialchars($badge) ?></span>
          <?php endif ?>

          <?php if ($hasThumb): ?>
            <img src="<?= $imgUrl ?>" alt="<?= $name ?>" loading="lazy"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="product-img-placeholder" style="display:none"><i class="<?= $icon ?>"></i></div>
          <?php else: ?>
            <div class="product-img-placeholder"><i class="<?= $icon ?>"></i></div>
          <?php endif ?>

          <div class="p-hover-btns">
            <button class="p-hover-btn" title="Add to Wishlist"><i class="far fa-heart"></i></button>
          </div>
        </div>

        <div class="product-body">
          <span class="p-cat"><?= htmlspecialchars($p['cat_name']) ?></span>
          <div class="p-name"><?= $name ?></div>
          <div class="p-pricing">
            <span class="p-price">Rs. <?= number_format($price) ?></span>
            <?php if ($oldPrice): ?>
              <span class="p-old">Rs. <?= number_format($oldPrice) ?></span>
            <?php endif ?>
          </div>
          <div class="p-card-actions">
            <button class="btn-cart"><i class="fas fa-shopping-cart"></i> Add to Cart</button>
            <a href="<?= $link ?>" class="btn-view" title="View Details"><i class="fas fa-eye"></i></a>
          </div>
        </div>
      </div>
      <?php endforeach ?>
    </div>
  </div>
</section>
<?php endif ?>

<div id="footer-slot"></div>
<script src="components/footer.js"></script>
<button class="scroll-top" id="scrollTopBtn" aria-label="Scroll to top"><i class="fas fa-chevron-up"></i></button>
<script src="assets/js/main.js"></script>
<script>
(function () {
  const WA_NUMBER = '<?= $waClean ?>';

  function renderCart() {
    const items   = GenexCart.getItems();
    const count   = GenexCart.getCount();
    const total   = GenexCart.getTotal();
    const layout  = document.getElementById('cartPageLayout');
    const empty   = document.getElementById('cartEmptyState');
    const tbody   = document.getElementById('cartTableBody');
    const waBtn   = document.getElementById('waOrderBtn');

    document.getElementById('summaryItemCount').textContent = count + ' item' + (count !== 1 ? 's' : '');
    document.getElementById('summarySubtotal').textContent  = GenexCart.fmt(total);
    document.getElementById('summaryTotal').textContent     = GenexCart.fmt(total);
    if (waBtn) waBtn.href = GenexCart.buildWaText(WA_NUMBER) || '#';

    if (!items.length) {
      layout.style.display  = 'none';
      empty.style.display   = '';
      return;
    }

    layout.style.display  = '';
    empty.style.display   = 'none';

    tbody.innerHTML = items.map(item => `
      <div class="cart-row" data-id="${item.id}">
        <div class="cart-row-product">
          <div class="cart-row-ico"><i class="${item.icon}"></i></div>
          <div>
            <div class="cart-row-name">${item.name}</div>
            <div class="cart-row-cat">${item.category}</div>
          </div>
        </div>
        <div class="cart-row-price">${GenexCart.fmt(item.price)}</div>
        <div class="cart-row-qty">
          <button type="button" data-action="dec" data-id="${item.id}"><i class="fas fa-minus"></i></button>
          <span>${item.qty}</span>
          <button type="button" data-action="inc" data-id="${item.id}"><i class="fas fa-plus"></i></button>
        </div>
        <button class="cart-row-remove" type="button" data-action="remove" data-id="${item.id}" title="Remove"><i class="fas fa-trash-alt"></i></button>
      </div>`).join('');

    tbody.querySelectorAll('[data-action]').forEach(btn => {
      btn.addEventListener('click', () => {
        const id     = btn.dataset.id;
        const action = btn.dataset.action;
        if (action === 'remove') {
          GenexCart.removeItem(id);
        } else {
          const it = GenexCart.getItems().find(i => i.id === id);
          if (!it) return;
          GenexCart.updateQty(id, action === 'inc' ? it.qty + 1 : it.qty - 1);
        }
        renderCart();
      });
    });
  }

  document.getElementById('cartClearBtn').addEventListener('click', () => {
    if (confirm('Clear all items from your cart?')) { GenexCart.clear(); renderCart(); }
  });

  renderCart();
})();
</script>
</body>
</html>
