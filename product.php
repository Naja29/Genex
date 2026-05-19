<?php
require_once __DIR__ . '/includes/functions.php';
$db = getDB();

// Load product 
$id = (int)get('id');
if (!$id) { header('Location: shop.php'); exit; }

$stmt = $db->prepare('
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.is_active = 1
');
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) { header('Location: shop.php'); exit; }

// Specs
$specs = $db->prepare('SELECT * FROM product_specs WHERE product_id = ? ORDER BY sort_order ASC');
$specs->execute([$id]);
$specs = $specs->fetchAll();

// Additional images
$images = $db->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC LIMIT 5');
$images->execute([$id]);
$images = $images->fetchAll();

// Related products (same category, not this one)
$related = $db->prepare('
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
    ORDER BY p.is_featured DESC, p.created_at DESC
    LIMIT 4
');
$related->execute([$p['category_id'], $id]);
$related = $related->fetchAll();

// Derived values 
$price     = (float)$p['price'];
$oldPrice  = $p['old_price'] ? (float)$p['old_price'] : null;
$discount  = $oldPrice ? round((1 - $price / $oldPrice) * 100) : 0;
$saving    = $oldPrice ? ($oldPrice - $price) : 0;
$inStock   = (bool)($p['in_stock'] ?? true);
$name      = htmlspecialchars($p['name']);
$catName   = htmlspecialchars($p['cat_name']);
$catSlug   = htmlspecialchars($p['cat_slug']);
$brand     = htmlspecialchars($p['brand'] ?? '');
$sku       = htmlspecialchars($p['sku'] ?? '');
$icon      = htmlspecialchars($p['icon'] ?? 'fas fa-box');
$rating    = (float)($p['rating'] ?? 0);
$badge     = $p['badge'] ?? '';
$waNumber  = getSetting('store_whatsapp', '94777237962');
$waText    = urlencode("Hi Genex, I want to order: $p[name]. Please confirm availability and price. Thank you!");
$waLink    = "https://wa.me/{$waNumber}?text={$waText}";

// Thumbnail
$thumb    = $p['thumbnail'] ?? '';
$hasThumb = $thumb && file_exists(__DIR__ . '/' . $thumb);
$thumbUrl = $hasThumb ? BASE_URL . htmlspecialchars($thumb) : '';

// Star rating HTML
function starRating(float $r): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $r >= $i ? '&#9733;' : ($r >= $i - 0.5 ? '&#9734;' : '&#9734;');
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($p['short_description'] ?: $p['name']) ?> - Buy genuine <?= $catName ?> at best price in Sri Lanka from Genex.">
  <title><?= $name ?> | Genex - Global Xperience</title>
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

<!-- BREADCRUMB -->
<div style="background:var(--bg-2);border-bottom:1px solid var(--border);padding:12px 0">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <a href="shop.php">Shop</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <a href="shop.php?cat=<?= $catSlug ?>"><?= $catName ?></a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span><?= $name ?></span>
    </nav>
  </div>
</div>

<!-- PRODUCT DETAIL -->
<section class="section">
  <div class="container">
    <div class="pd-layout">

      <!-- GALLERY -->
      <div class="pd-gallery">
        <div class="pd-main-img" id="pdMainImg">
          <?php if ($hasThumb): ?>
            <img id="pdMainImgEl" src="<?= $thumbUrl ?>" alt="<?= $name ?>"
                 style="width:100%;height:100%;object-fit:contain"
                 onerror="this.style.display='none';document.getElementById('pdMainPlaceholder').style.display='flex'">
            <div class="pd-main-placeholder" id="pdMainPlaceholder" style="display:none"><i class="<?= $icon ?>"></i></div>
          <?php else: ?>
            <div class="pd-main-placeholder"><i class="<?= $icon ?>"></i></div>
          <?php endif ?>
          <span class="pd-zoom-hint"><i class="fas fa-search-plus"></i> Product Image</span>
        </div>

        <div class="pd-thumbs">
          <!-- Primary thumbnail -->
          <div class="pd-thumb active" data-img="<?= $thumbUrl ?>">
            <?php if ($hasThumb): ?>
              <img src="<?= $thumbUrl ?>" alt="<?= $name ?>" style="width:100%;height:100%;object-fit:cover;border-radius:4px">
            <?php else: ?>
              <div class="pd-thumb-placeholder"><i class="<?= $icon ?>"></i></div>
            <?php endif ?>
          </div>
          <!-- Additional images -->
          <?php foreach ($images as $img):
            $iUrl = BASE_URL . htmlspecialchars($img['image_path']);
          ?>
          <div class="pd-thumb" data-img="<?= $iUrl ?>">
            <img src="<?= $iUrl ?>" alt="<?= $name ?>" style="width:100%;height:100%;object-fit:cover;border-radius:4px"
                 onerror="this.parentElement.style.display='none'">
          </div>
          <?php endforeach ?>
          <!-- Filler thumbnails if fewer than 3 total -->
          <?php for ($fi = count($images); $fi < 2; $fi++): ?>
          <div class="pd-thumb" data-img="">
            <div class="pd-thumb-placeholder"><i class="<?= $icon ?>"></i></div>
          </div>
          <?php endfor ?>
        </div>
      </div>

      <!-- INFO PANEL -->
      <div class="pd-info">

        <div class="pd-top-badges">
          <?php if ($discount >= 5): ?>
            <span class="pd-badge sale">-<?= $discount ?>% OFF</span>
          <?php endif ?>
          <?php if ($badge === 'HOT'): ?>
            <span class="pd-badge hot"><i class="fas fa-fire"></i> Hot</span>
          <?php elseif ($badge === 'NEW'): ?>
            <span class="pd-badge new"><i class="fas fa-bolt"></i> New</span>
          <?php endif ?>
          <span class="pd-badge genuine"><i class="fas fa-shield-alt"></i> Genuine</span>
        </div>

        <h1 class="pd-name"><?= $name ?></h1>

        <div class="pd-meta">
          <?php if ($brand): ?>
          <div class="pd-brand">
            <div class="pd-brand-logo" id="pdBrandLogoWrap">
              <img src="images/brands/<?= strtolower(str_replace([' ','_'], '-', $p['brand'])) ?>.png"
                   alt="<?= $brand ?>"
                   onerror="document.getElementById('pdBrandLogoWrap').style.display='none'">
            </div>
            <div class="pd-brand-text">
              <span>Brand</span>
              <strong><?= $brand ?></strong>
            </div>
          </div>
          <?php endif ?>
          <?php if ($rating > 0): ?>
          <div class="pd-rating">
            <span class="pd-stars"><?= starRating($rating) ?></span>
            <span style="font-size:13.5px;font-weight:700;color:var(--text-2)"><?= number_format($rating, 1) ?></span>
          </div>
          <?php endif ?>
          <?php if ($sku): ?>
            <span style="font-size:12px;color:var(--text-dim)">SKU: <?= $sku ?></span>
          <?php endif ?>
        </div>

        <div class="pd-divider"></div>

        <div class="pd-price-block">
          <span class="pd-price">Rs. <?= number_format($price) ?></span>
          <?php if ($oldPrice): ?>
            <span class="pd-old-price">Rs. <?= number_format($oldPrice) ?></span>
            <span class="pd-discount">Save Rs. <?= number_format($saving) ?></span>
          <?php endif ?>
        </div>
        <p class="pd-tax"><i class="fas fa-info-circle"></i> Price inclusive of all taxes. Wholesale pricing available.</p>

        <span class="pd-availability <?= $inStock ? 'in-stock' : 'out-stock' ?>">
          <i class="fas fa-circle"></i> <?= $inStock ? 'In Stock - Ready to Ship' : 'Out of Stock' ?>
        </span>

        <?php if ($p['short_description']): ?>
        <p class="pd-short-desc"><?= htmlspecialchars($p['short_description']) ?></p>
        <?php endif ?>

        <!-- Key specs (first 6) -->
        <?php if ($specs): ?>
        <div class="pd-key-specs">
          <?php foreach (array_slice($specs, 0, 6) as $s): ?>
          <div class="pd-spec-row">
            <i class="fas fa-check-circle"></i>
            <span><strong><?= htmlspecialchars($s['spec_key']) ?>:</strong> <?= htmlspecialchars($s['spec_value']) ?></span>
          </div>
          <?php endforeach ?>
        </div>
        <?php endif ?>

        <div class="pd-divider"></div>

        <div class="pd-qty-row">
          <span class="pd-qty-label">Quantity:</span>
          <div class="pd-qty-selector">
            <button class="pd-qty-btn" id="pdQtyMinus" type="button"><i class="fas fa-minus"></i></button>
            <input class="pd-qty-val" id="pdQtyVal" type="text" value="1" readonly>
            <button class="pd-qty-btn" id="pdQtyPlus" type="button"><i class="fas fa-plus"></i></button>
          </div>
          <span style="font-size:12px;color:var(--text-dim)">Max 10 per order</span>
        </div>

        <div class="pd-actions">
          <div class="pd-actions-row">
            <button class="pd-btn-cart <?= $inStock ? '' : 'disabled' ?>" id="pdAddCart" type="button" <?= $inStock ? '' : 'disabled' ?>>
              <i class="fas fa-shopping-cart"></i> <?= $inStock ? 'Add to Cart' : 'Out of Stock' ?>
            </button>
            <button class="pd-btn-wish" id="pdWish" title="Add to Wishlist" type="button">
              <i class="far fa-heart"></i>
            </button>
          </div>
          <a href="<?= $waLink ?>" target="_blank" rel="noopener" class="pd-btn-wa">
            <i class="fab fa-whatsapp"></i> Order via WhatsApp
          </a>
        </div>

        <div class="pd-trust">
          <div class="pd-trust-item"><i class="fas fa-shield-alt"></i><span>100% Genuine</span></div>
          <div class="pd-trust-item"><i class="fas fa-undo-alt"></i><span>7-Day Returns</span></div>
          <div class="pd-trust-item"><i class="fas fa-truck"></i><span>Island-wide Delivery</span></div>
        </div>

      </div>
    </div>

    <!-- TABS -->
    <div class="pd-tabs" data-anim="up">
      <div class="pd-tabs-nav">
        <button class="pd-tab-btn active" data-tab="description">Description</button>
        <?php if ($specs): ?>
          <button class="pd-tab-btn" data-tab="specifications">Specifications</button>
        <?php endif ?>
        <button class="pd-tab-btn" data-tab="warranty">Warranty &amp; Returns</button>
      </div>

      <!-- Description -->
      <div class="pd-tab-panel active" id="tab-description">
        <div class="pd-desc-body">
          <?php if ($p['description']): ?>
            <?= nl2br(htmlspecialchars($p['description'])) ?>
          <?php else: ?>
            <p style="color:var(--text-muted)">No description available for this product.</p>
          <?php endif ?>
        </div>
      </div>

      <!-- Specifications -->
      <?php if ($specs): ?>
      <div class="pd-tab-panel" id="tab-specifications">
        <table class="pd-specs-table">
          <?php if ($brand): ?>
            <tr><td>Brand</td><td><?= $brand ?></td></tr>
          <?php endif ?>
          <?php if ($sku): ?>
            <tr><td>SKU</td><td><?= $sku ?></td></tr>
          <?php endif ?>
          <?php foreach ($specs as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['spec_key']) ?></td>
              <td><?= htmlspecialchars($s['spec_value']) ?></td>
            </tr>
          <?php endforeach ?>
        </table>
      </div>
      <?php endif ?>

      <!-- Warranty -->
      <div class="pd-tab-panel" id="tab-warranty">
        <div class="pd-warranty-box">
          <h4><i class="fas fa-shield-alt" style="color:var(--primary);margin-right:8px"></i>Warranty &amp; Return Policy</h4>
          <div class="pd-warranty-item">
            <i class="fas fa-check-circle"></i>
            <span><strong>Manufacturer Warranty</strong> - All products come with official manufacturer warranty against defects.</span>
          </div>
          <div class="pd-warranty-item">
            <i class="fas fa-check-circle"></i>
            <span><strong>7-Day Return Window</strong> - If you receive a defective or incorrect item, contact us within 7 days for a replacement or refund.</span>
          </div>
          <div class="pd-warranty-item">
            <i class="fas fa-check-circle"></i>
            <span><strong>Original Packaging Required</strong> - Items must be returned in original packaging unless defective on first use.</span>
          </div>
          <div class="pd-warranty-item">
            <i class="fas fa-info-circle"></i>
            <span><strong>Not covered:</strong> Physical damage, improper installation, overclocking damage, or user-caused issues.</span>
          </div>
          <div class="pd-warranty-item">
            <i class="fab fa-whatsapp" style="color:#25d366"></i>
            <span>For warranty claims, contact us on <a href="<?= $waLink ?>" target="_blank" style="color:var(--primary)">WhatsApp</a>.</span>
          </div>
        </div>
      </div>
    </div>

    <!-- RELATED PRODUCTS -->
    <?php if ($related): ?>
    <div class="pd-related" data-anim="up">
      <div class="section-header" style="margin-bottom:28px">
        <span class="section-tag">You May Also Like</span>
        <h2 class="section-title">Related <em>Products</em></h2>
      </div>
      <div class="products-grid">
        <?php foreach ($related as $r):
          $rThumb    = $r['thumbnail'] ?? '';
          $rHasThumb = $rThumb && file_exists(__DIR__ . '/' . $rThumb);
          $rIcon     = htmlspecialchars($r['icon'] ?? 'fas fa-box');
          $rPrice    = (float)$r['price'];
          $rOld      = $r['old_price'] ? (float)$r['old_price'] : null;
          $rName     = htmlspecialchars($r['name']);
          $rSlug     = htmlspecialchars($r['cat_slug']);
          $rBadge    = $r['badge'] ?? '';
          $rBadgeCls = match($rBadge) { 'HOT'=>'hot','NEW'=>'new','SALE'=>'sale', default=>'' };
        ?>
        <div class="product-card"
          data-category="<?= $rSlug ?>"
          data-name="<?= $rName ?>"
          data-price="<?= (int)$rPrice ?>">
          <div class="product-img-area">
            <span class="stock-tag <?= $r['in_stock'] ? 'in-stock' : 'out-stock' ?>">
              <?= $r['in_stock'] ? 'In Stock' : 'Out of Stock' ?>
            </span>
            <?php if ($rBadge): ?>
              <span class="p-badge <?= $rBadgeCls ?>"><?= htmlspecialchars($rBadge) ?></span>
            <?php endif ?>
            <?php if ($rHasThumb): ?>
              <img src="<?= BASE_URL . htmlspecialchars($rThumb) ?>" alt="<?= $rName ?>" loading="lazy"
                   onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
              <div class="product-img-placeholder" style="display:none"><i class="<?= $rIcon ?>"></i></div>
            <?php else: ?>
              <div class="product-img-placeholder"><i class="<?= $rIcon ?>"></i></div>
            <?php endif ?>
            <div class="p-hover-btns">
              <button class="p-hover-btn" title="Add to Wishlist"><i class="far fa-heart"></i></button>
              <a href="product.php?id=<?= $r['id'] ?>" class="p-hover-btn" title="View Details"><i class="fas fa-eye"></i></a>
            </div>
          </div>
          <div class="product-body">
            <div class="p-cat"><?= htmlspecialchars($r['cat_name']) ?></div>
            <div class="p-name"><?= $rName ?></div>
            <div class="p-pricing">
              <span class="p-price">Rs. <?= number_format($rPrice) ?></span>
              <?php if ($rOld): ?>
                <span class="p-old">Rs. <?= number_format($rOld) ?></span>
              <?php endif ?>
            </div>
            <div class="p-card-actions">
              <button class="btn-cart"><i class="fas fa-shopping-cart"></i> Add to Cart</button>
              <a href="product.php?id=<?= $r['id'] ?>" class="btn-view" title="View Details"><i class="fas fa-eye"></i></a>
            </div>
          </div>
        </div>
        <?php endforeach ?>
      </div>
    </div>
    <?php endif ?>

  </div>
</section>

<div id="footer-slot"></div>
<script src="components/footer.js"></script>

<button class="scroll-top" id="scrollTopBtn" aria-label="Scroll to top">
  <i class="fas fa-chevron-up"></i>
</button>

<script src="assets/js/main.js"></script>
<script>
(function () {
  const productData = {
    name:     <?= json_encode($p['name']) ?>,
    category: <?= json_encode($p['cat_slug']) ?>,
    price:    <?= (int)$price ?>,
    icon:     <?= json_encode($icon) ?>
  };

  /* Thumbnail switching */
  const mainImg   = document.getElementById('pdMainImg');
  const mainEl    = document.getElementById('pdMainImgEl');
  const mainPh    = document.getElementById('pdMainPlaceholder');

  document.querySelectorAll('.pd-thumb').forEach(thumb => {
    thumb.addEventListener('click', () => {
      document.querySelectorAll('.pd-thumb').forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
      const imgSrc = thumb.dataset.img;
      if (imgSrc && mainEl) {
        mainEl.src = imgSrc;
        mainEl.style.display = '';
        if (mainPh) mainPh.style.display = 'none';
      }
    });
  });

  /* Quantity selector */
  const qtyVal   = document.getElementById('pdQtyVal');
  const qtyMinus = document.getElementById('pdQtyMinus');
  const qtyPlus  = document.getElementById('pdQtyPlus');

  qtyMinus.addEventListener('click', () => { const v = parseInt(qtyVal.value); if (v > 1) qtyVal.value = v - 1; });
  qtyPlus.addEventListener('click',  () => { const v = parseInt(qtyVal.value); if (v < 10) qtyVal.value = v + 1; });

  /* Add to cart */
  const addBtn = document.getElementById('pdAddCart');
  if (addBtn && !addBtn.disabled) {
    addBtn.addEventListener('click', function () {
      const qty = parseInt(qtyVal.value) || 1;
      if (!window.GenexCart) return;

      for (let i = 0; i < qty; i++) {
        GenexCart.addItem(productData);
      }

      this.innerHTML = '<i class="fas fa-check"></i> Added to Cart!';
      this.style.background = '#16a34a';
      setTimeout(() => {
        this.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
        this.style.background = '';
      }, 2000);
    });
  }

  /* Wishlist toggle */
  const wishBtn = document.getElementById('pdWish');
  wishBtn.addEventListener('click', function () {
    if (!window.GenexWishlist) return;
    const inList = GenexWishlist.has(productData.name);
    inList ? GenexWishlist.remove(productData.name) : GenexWishlist.add(productData.name);
    const icon = this.querySelector('i');
    icon.className         = inList ? 'far fa-heart' : 'fas fa-heart';
    this.style.background  = inList ? '' : '#ef4444';
    this.style.color       = inList ? '' : '#fff';
    this.style.borderColor = inList ? '' : 'transparent';
  });

  // Restore wishlist state on load
  if (window.GenexWishlist && GenexWishlist.has(productData.name)) {
    const icon = wishBtn.querySelector('i');
    icon.className         = 'fas fa-heart';
    wishBtn.style.background  = '#ef4444';
    wishBtn.style.color       = '#fff';
    wishBtn.style.borderColor = 'transparent';
  }

  /* Tabs */
  document.querySelectorAll('.pd-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const tab = btn.dataset.tab;
      document.querySelectorAll('.pd-tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.pd-tab-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('tab-' + tab).classList.add('active');
    });
  });
})();
</script>
</body>
</html>
