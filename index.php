<?php
require_once __DIR__ . '/includes/functions.php';
$db = getDB();

// Fetch data 
$categories = $db->query('
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY c.sort_order ASC, c.name ASC
')->fetchAll();

// Build lookup by slug and lowercase name for bento links
$catLookup = [];
foreach ($categories as $cat) {
    $catLookup[strtolower($cat['slug'])] = $cat;
    $catLookup[strtolower($cat['name'])] = $cat;
}

// Static bento layout matching original design
// [name, subtitle, icon, --ci color, --cc color, type: feat|wide|normal]
$bentoItems = [
    ['Mobile Displays & Accessories', 'All major brands',   'fas fa-mobile-alt',       'rgba(236,72,153,.15)', '#ec4899', 'feat'],
    ['Graphics Cards',                'NVIDIA & AMD',        'fas fa-desktop',          'rgba(118,185,0,.12)',  '#76b900', 'normal'],
    ['Storage',                       'SSD & HDD',           'fas fa-hdd',              'rgba(37,211,102,.10)', '#22c55e', 'normal'],
    ['RAM',                           'DDR4 & DDR5',         'fas fa-memory',           'rgba(168,85,247,.12)', '#a855f7', 'normal'],
    ['Monitors',                      'FHD & 4K',            'fas fa-tv',               'rgba(59,130,246,.12)', '#3b82f6', 'normal'],
    ['Processors',                    'Intel & AMD CPUs',    'fas fa-microchip',        'rgba(212,146,10,.12)', '#d4920a', 'wide'],
    ['Motherboards',                  'All Chipsets',        'fas fa-server',           'rgba(245,158,11,.12)', '#f59e0b', 'normal'],
    ['Keyboards',                     'Mech & Membrane',     'fas fa-keyboard',         'rgba(16,185,129,.12)', '#10b981', 'normal'],
    ['Mouse',                         'Gaming & Office',     'fas fa-mouse',            'rgba(239,68,68,.12)',  '#ef4444', 'normal'],
    ['Cables',                        'All Types',           'fas fa-plug',             'rgba(251,191,36,.12)', '#fbbf24', 'normal'],
    ['Earphones',                     'Wired & Wireless',    'fas fa-headphones',       'rgba(167,139,250,.12)','#a78bfa', 'wide'],
    ['Power Banks',                   '10k–20k mAh',         'fas fa-battery-full',     'rgba(52,211,153,.12)', '#34d399', 'normal'],
    ['Chargers',                      'Fast Charging',       'fas fa-charging-station', 'rgba(251,146,60,.12)', '#fb923c', 'normal'],
];

$featured = $db->query('
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_featured = 1 AND p.is_active = 1
    ORDER BY p.created_at DESC
    LIMIT 10
')->fetchAll();

$newArrivals = $db->query('
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
    LIMIT 5
')->fetchAll();

$productCount = $db->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
$waNumber     = getSetting('store_whatsapp', '94777237962');

// Category colour palette (cycles if more than 13 categories)
$palette = [
    ['rgba(236,72,153,.12)','#ec4899'],
    ['rgba(118,185,0,.12)', '#76b900'],
    ['rgba(37,211,102,.10)','#22c55e'],
    ['rgba(168,85,247,.12)','#a855f7'],
    ['rgba(59,130,246,.12)','#3b82f6'],
    ['rgba(212,146,10,.12)','#d4920a'],
    ['rgba(245,158,11,.12)','#f59e0b'],
    ['rgba(16,185,129,.12)','#10b981'],
    ['rgba(239,68,68,.12)', '#ef4444'],
    ['rgba(251,191,36,.12)','#fbbf24'],
    ['rgba(167,139,250,.12)','#a78bfa'],
    ['rgba(52,211,153,.12)','#34d399'],
    ['rgba(251,146,60,.12)','#fb923c'],
];

// Unique category slugs from featured products (for tab filter)
$featCats = [];
foreach ($featured as $p) {
    $slug = $p['cat_slug'];
    if (!isset($featCats[$slug])) $featCats[$slug] = $p['cat_name'];
}

// Helper: render one product card
function productCard(array $p, string $badgeOverride = ''): string {
    $badge    = $badgeOverride ?: ($p['badge'] ?? '');
    $inStock  = ($p['in_stock'] ?? 1) ? 'in-stock' : 'out-stock';
    $stockLbl = ($p['in_stock'] ?? 1) ? 'In Stock'  : 'Out of Stock';
    $slug     = htmlspecialchars($p['cat_slug'] ?? '');
    $name     = htmlspecialchars($p['name']);
    $price    = (float)$p['price'];
    $oldPrice = $p['old_price'] ? (float)$p['old_price'] : null;
    $icon     = htmlspecialchars($p['icon'] ?? 'fas fa-box');
    $thumb    = $p['thumbnail'] ?? '';
    $hasThumb = $thumb && file_exists(__DIR__ . '/' . $thumb);
    $imgUrl   = $hasThumb ? htmlspecialchars(BASE_URL . $thumb) : '';
    $link     = 'product.php?id=' . (int)$p['id'];

    $badgeHtml = '';
    if ($badge) {
        $cls = match($badge) { 'HOT'=>'badge-hot','NEW'=>'badge-new','SALE'=>'badge-sale', default=>'' };
        $badgeHtml = "<span class='p-badge $cls'>" . htmlspecialchars($badge) . "</span>";
    }

    $imgHtml = $hasThumb
        ? "<img src='$imgUrl' alt='$name' loading='lazy' onerror=\"this.style.display='none';this.nextElementSibling.style.display='flex'\">"
          . "<div class='product-img-placeholder' style='display:none'><i class='$icon'></i></div>"
        : "<div class='product-img-placeholder'><i class='$icon'></i></div>";

    $oldHtml = $oldPrice ? "<span class='p-old'>Rs. " . number_format($oldPrice) . "</span>" : '';

    return "
    <div class='product-card' data-category='$slug' data-name='$name' data-price='" . (int)$price . "'>
      <div class='product-img-area'>
        <span class='stock-tag $inStock'>$stockLbl</span>
        $badgeHtml
        $imgHtml
        <div class='p-hover-btns'>
          <button class='p-hover-btn' title='Add to Wishlist'><i class='far fa-heart'></i></button>
        </div>
      </div>
      <div class='product-body'>
        <span class='p-cat'>" . htmlspecialchars($p['cat_name']) . "</span>
        <div class='p-name'>$name</div>
        <div class='p-pricing'>
          <span class='p-price'>Rs. " . number_format($price) . "</span>
          $oldHtml
        </div>
        <div class='p-card-actions'>
          <button class='btn-cart'><i class='fas fa-shopping-cart'></i> Add to Cart</button>
          <a href='$link' class='btn-view' title='View Details'><i class='fas fa-eye'></i></a>
        </div>
      </div>
    </div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Genex - Global Experience. Premium computer parts, electronics & accessories. Wholesale & Retail. Best prices & fast service in Sri Lanka.">
  <title>Genex - Global Experience | Computer Parts &amp; Electronics</title>
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

<!-- HERO -->
<section class="hero">
  <div class="hero-dot-bg"></div>
  <div class="hero-orb hero-orb-1"></div>
  <div class="hero-orb hero-orb-2"></div>
  <div class="hero-orb hero-orb-3"></div>
  <div class="container hero-split">
    <div class="hero-left">
      <span class="hero-badge"><i class="fas fa-bolt"></i> New Arrivals 2025</span>
      <h1 class="hero-title">Premium<br><em>Computer Parts</em><br>&amp; Electronics</h1>
      <p class="hero-sub">Sri Lanka's trusted source for processors, graphics cards, RAM, storage and accessories. Wholesale &amp; Retail island-wide.</p>
      <div class="hero-btns">
        <a href="shop.php" class="btn btn-gold"><i class="fas fa-shopping-bag"></i> Shop Now</a>
        <a href="https://wa.me/<?= htmlspecialchars($waNumber) ?>" target="_blank" rel="noopener" class="btn btn-green">
          <i class="fab fa-whatsapp"></i> WhatsApp Order
        </a>
      </div>
      <div class="hero-trust">
        <span><i class="fas fa-shield-alt"></i> Genuine Products</span>
        <span><i class="fas fa-truck"></i> Fast Delivery</span>
        <span><i class="fas fa-headset"></i> 24/7 Support</span>
      </div>
    </div>
    <div class="hero-right">
      <div class="hero-showcase">
        <div class="hsc-glow"></div>
        <div class="hsc-ring"></div>
        <?php if (!empty($featured[0])): $fp = $featured[0]; ?>
        <div class="hsc-card hsc-c1">
          <div class="hsc-ico" style="--ci:rgba(76,158,255,.15);--cc:#4c9eff"><i class="<?= htmlspecialchars($fp['icon'] ?? 'fas fa-microchip') ?>"></i></div>
          <div class="hsc-info">
            <span class="hsc-name"><?= htmlspecialchars($fp['name']) ?></span>
            <span class="hsc-price">Rs. <?= number_format((float)$fp['price']) ?></span>
          </div>
          <?php if ($fp['badge']): ?><span class="hsc-badge hsc-<?= strtolower($fp['badge']) ?>"><?= $fp['badge'] ?></span><?php endif ?>
        </div>
        <?php else: ?>
        <div class="hsc-card hsc-c1">
          <div class="hsc-ico" style="--ci:rgba(76,158,255,.15);--cc:#4c9eff"><i class="fas fa-microchip"></i></div>
          <div class="hsc-info"><span class="hsc-name">Intel Core i9-13900K</span><span class="hsc-price">Rs. 145,000</span></div>
          <span class="hsc-badge hsc-hot">Hot</span>
        </div>
        <?php endif ?>
        <?php if (!empty($featured[1])): $fp2 = $featured[1]; ?>
        <div class="hsc-card hsc-c2">
          <div class="hsc-ico" style="--ci:rgba(118,185,0,.15);--cc:#76b900"><i class="<?= htmlspecialchars($fp2['icon'] ?? 'fas fa-desktop') ?>"></i></div>
          <div class="hsc-info">
            <span class="hsc-name"><?= htmlspecialchars($fp2['name']) ?></span>
            <span class="hsc-price">Rs. <?= number_format((float)$fp2['price']) ?></span>
          </div>
          <?php if ($fp2['badge']): ?><span class="hsc-badge hsc-<?= strtolower($fp2['badge']) ?>"><?= $fp2['badge'] ?></span><?php endif ?>
        </div>
        <?php else: ?>
        <div class="hsc-card hsc-c2">
          <div class="hsc-ico" style="--ci:rgba(118,185,0,.15);--cc:#76b900"><i class="fas fa-desktop"></i></div>
          <div class="hsc-info"><span class="hsc-name">RTX 4070 Super 12GB</span><span class="hsc-price">Rs. 238,000</span></div>
          <span class="hsc-badge hsc-new">New</span>
        </div>
        <?php endif ?>
        <?php if (!empty($featured[2])): $fp3 = $featured[2]; ?>
        <div class="hsc-card hsc-c3">
          <div class="hsc-ico" style="--ci:rgba(212,146,10,.15);--cc:#d4920a"><i class="<?= htmlspecialchars($fp3['icon'] ?? 'fas fa-memory') ?>"></i></div>
          <div class="hsc-info">
            <span class="hsc-name"><?= htmlspecialchars($fp3['name']) ?></span>
            <span class="hsc-price">Rs. <?= number_format((float)$fp3['price']) ?></span>
          </div>
          <?php if ($fp3['badge']): ?><span class="hsc-badge hsc-<?= strtolower($fp3['badge']) ?>"><?= $fp3['badge'] ?></span><?php endif ?>
        </div>
        <?php else: ?>
        <div class="hsc-card hsc-c3">
          <div class="hsc-ico" style="--ci:rgba(212,146,10,.15);--cc:#d4920a"><i class="fas fa-memory"></i></div>
          <div class="hsc-info"><span class="hsc-name">Kingston 32GB DDR5</span><span class="hsc-price">Rs. 48,500</span></div>
          <span class="hsc-badge hsc-sale">Sale</span>
        </div>
        <?php endif ?>
        <div class="hsc-pill hsc-p1"><i class="fas fa-users"></i> 1,000+ Customers</div>
        <div class="hsc-pill hsc-p2"><i class="fas fa-box-open"></i> <?= number_format($productCount) ?>+ Products</div>
      </div>
    </div>
  </div>
</section>

<!-- STATS BAR -->
<div class="stats-bar">
  <div class="stats-grid">
    <div class="stat-item" data-anim="up" data-delay="1">
      <div class="stat-ico"><i class="fas fa-box-open"></i></div>
      <div><div class="stat-num" data-count="<?= $productCount ?>"><?= $productCount ?>+</div><div class="stat-label">Products Available</div></div>
    </div>
    <div class="stat-item" data-anim="up" data-delay="2">
      <div class="stat-ico"><i class="fas fa-users"></i></div>
      <div><div class="stat-num" data-count="1000">0</div><div class="stat-label">Happy Customers</div></div>
    </div>
    <div class="stat-item" data-anim="up" data-delay="3">
      <div class="stat-ico"><i class="fas fa-calendar-alt"></i></div>
      <div><div class="stat-num" data-count="5">0</div><div class="stat-label">Years of Experience</div></div>
    </div>
    <div class="stat-item" data-anim="up" data-delay="4">
      <div class="stat-ico"><i class="fas fa-headset"></i></div>
      <div><div class="stat-num">24/7</div><div class="stat-label">Customer Support</div></div>
    </div>
  </div>
</div>

<!-- BENTO CATEGORY GRID -->
<section class="bento-section">
  <div class="container">
    <div class="section-header" data-anim="up">
      <span class="section-tag">Browse Our Range</span>
      <h2 class="section-title">Shop by <em>Category</em></h2>
      <p class="section-desc">Find exactly what you need from our wide range of computer parts, electronics and accessories.</p>
    </div>

    <div class="bento-grid">
      <?php foreach ($bentoItems as $i => [$name, $sub, $icon, $ci, $cc, $type]):
        $isFeat = $type === 'feat';
        $isWide = $type === 'wide';
        $cls    = $isFeat ? 'bcat bcat-feat' : ($isWide ? 'bcat bcat-wide' : 'bcat');
        $style  = $isFeat
          ? "background:linear-gradient(145deg,#1a0d14,#150910);border-color:rgba(236,72,153,.22)"
          : "--ci:$ci;--cc:$cc";
        $dbCat  = $catLookup[strtolower($name)] ?? null;
        $link   = $dbCat ? 'shop.php?cat=' . urlencode($dbCat['slug']) : 'shop.php?q=' . urlencode($name);
        $count  = $dbCat['product_count'] ?? 0;
      ?>
      <a href="<?= $link ?>" class="<?= $cls ?>" style="<?= $style ?>" data-anim="up" data-delay="<?= ($i % 5) + 1 ?>">
        <div class="bcat-icon" <?= $isFeat ? "style='--ci:rgba(236,72,153,.15);--cc:#ec4899'" : '' ?>>
          <i class="<?= htmlspecialchars($icon) ?>"></i>
        </div>
        <div class="bcat-body">
          <span class="bcat-name"><?= htmlspecialchars($name) ?></span>
          <span class="bcat-sub"><?= htmlspecialchars($sub) ?><?= $count ? " &middot; $count items" : '' ?></span>
          <?php if ($isFeat): ?>
            <span class="bcat-explore" style="color:#ec4899">Explore <i class="fas fa-arrow-right"></i></span>
          <?php endif ?>
        </div>
        <i class="<?= htmlspecialchars($icon) ?> bcat-bg-ico"<?= $isFeat ? ' style="color:rgba(236,72,153,.07)"' : '' ?>></i>
      </a>
      <?php endforeach ?>

      <!-- View All -->
      <a href="shop.php" class="bcat bcat-wide bcat-viewall" data-anim="up">
        <div class="bcat-icon"><i class="fas fa-th-large"></i></div>
        <div class="bcat-body">
          <span class="bcat-name">View All Categories</span>
          <span class="bcat-sub">Browse our complete range</span>
          <span class="bcat-explore">Go to Shop <i class="fas fa-arrow-right"></i></span>
        </div>
        <i class="fas fa-th-large bcat-bg-ico"></i>
      </a>
    </div>
  </div>
</section>

<!-- SPECIAL OFFERS -->
<section class="section section-dark section-bg-img" id="specialOffersSection">
  <div class="container">
    <div class="section-head-row" data-anim="up">
      <div>
        <span class="section-tag">Exclusive Products</span>
        <h2 class="section-title" style="margin-bottom:0">Special <em>Offers</em></h2>
      </div>
      <div class="tab-row">
        <button class="tab-btn active" data-filter="all">All</button>
        <?php foreach ($featCats as $slug => $name): ?>
          <button class="tab-btn" data-filter="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($name) ?></button>
        <?php endforeach ?>
      </div>
    </div>

    <div class="products-grid" id="productsGrid">
      <?php if ($featured): ?>
        <?php foreach ($featured as $p): echo productCard($p); endforeach ?>
      <?php else: ?>
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted)">
          <i class="fas fa-star" style="font-size:32px;margin-bottom:12px;display:block;opacity:.4"></i>
          <p>No featured products yet. Mark products as featured in the admin panel.</p>
        </div>
      <?php endif ?>
    </div>

    <div class="see-all-row" data-anim="up">
      <a href="shop.php" class="btn btn-ghost"><i class="fas fa-th-large"></i> View All Products</a>
    </div>
  </div>
</section>

<!-- OUR BRANDS -->
<section class="brands-section">
  <div class="container">
    <div class="section-header" data-anim="up">
      <span class="section-tag">Our Partners</span>
      <h2 class="section-title">Trusted <em>Brands</em></h2>
    </div>
  </div>
  <div class="brands-marquee-wrap">
    <div class="brands-marquee-track">
      <?php
      $brands = ['intel','amd','asus','msi','kingston','samsung','seagate','wd','logitech','corsair','nvidia','lg','redragon','jbl','sony'];
      $names  = ['Intel','AMD','ASUS','MSI','Kingston','Samsung','Seagate','WD','Logitech','Corsair','NVIDIA','LG','Redragon','JBL','Sony'];
      foreach (array_merge($brands, $brands) as $bi => $b):
        $n = $names[$bi % count($names)];
      ?>
        <div class="bm-item"><div class="bm-card"><img src="images/brands/<?= $b ?>.png" alt="<?= $n ?>"></div></div>
      <?php endforeach ?>
    </div>
  </div>
</section>

<!-- PROMO BANNERS -->
<section class="section">
  <div class="container">
    <div class="promo-banners-grid" data-anim="up">
      <a href="shop.php" class="promo-card promo-card-1">
        <div class="promo-body">
          <span class="promo-eyebrow">Up to 20% Off</span>
          <h3 class="promo-h">Latest Processors<br>&amp; Motherboards</h3>
          <p class="promo-sub">Intel 13th Gen &amp; AMD Ryzen 7000</p>
          <span class="promo-link">Shop Now <i class="fas fa-arrow-right"></i></span>
        </div>
      </a>
      <a href="shop.php" class="promo-card promo-card-2">
        <div class="promo-body">
          <span class="promo-eyebrow">Hot Deals</span>
          <h3 class="promo-h">Graphics Cards<br>&amp; Monitors</h3>
          <p class="promo-sub">NVIDIA RTX &amp; AMD RX Series</p>
          <span class="promo-link">Shop Now <i class="fas fa-arrow-right"></i></span>
        </div>
      </a>
      <a href="wholesale.php" class="promo-card promo-card-3">
        <div class="promo-body">
          <span class="promo-eyebrow">Wholesale</span>
          <h3 class="promo-h">Bulk Orders<br>Best Prices</h3>
          <p class="promo-sub">Register as a wholesale buyer today</p>
          <span class="promo-link">Register Now <i class="fas fa-arrow-right"></i></span>
        </div>
      </a>
    </div>
  </div>
</section>

<!-- NEW ARRIVALS -->
<section class="section section-dark section-bg-img" id="newArrivalsSection">
  <div class="container">
    <div class="section-head-row" data-anim="up">
      <div>
        <span class="section-tag">Our Products</span>
        <h2 class="section-title" style="margin-bottom:0">New <em>Arrivals</em></h2>
      </div>
      <a href="shop.php" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-right"></i> View All</a>
    </div>

    <div class="products-grid">
      <?php if ($newArrivals): ?>
        <?php foreach ($newArrivals as $i => $p): echo productCard($p, $p['badge'] ?: 'NEW'); endforeach ?>
      <?php else: ?>
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted)">
          <i class="fas fa-box-open" style="font-size:32px;margin-bottom:12px;display:block;opacity:.4"></i>
          <p>No products yet. Add products in the admin panel.</p>
        </div>
      <?php endif ?>
    </div>
  </div>
</section>

<!-- WHY CHOOSE US -->
<section class="section">
  <div class="container">
    <div class="section-header" data-anim="up">
      <span class="section-tag">Our Promise</span>
      <h2 class="section-title">Why Choose <em>Genex</em></h2>
      <p class="section-desc">We're committed to delivering quality products with the best service in Sri Lanka.</p>
    </div>
    <div class="why-grid">
      <div class="why-card" data-anim="up" data-delay="1">
        <div class="why-ico"><i class="fas fa-tags"></i></div>
        <div class="why-title">Best Prices</div>
        <p class="why-desc">Competitive wholesale and retail pricing with regular deals. We guarantee the lowest prices in Sri Lanka.</p>
      </div>
      <div class="why-card" data-anim="up" data-delay="2">
        <div class="why-ico"><i class="fas fa-truck"></i></div>
        <div class="why-title">Fast Delivery</div>
        <p class="why-desc">Quick reliable island-wide delivery. Orders processed and dispatched within 24 hours.</p>
      </div>
      <div class="why-card" data-anim="up" data-delay="3">
        <div class="why-ico"><i class="fas fa-shield-alt"></i></div>
        <div class="why-title">Quality Assured</div>
        <p class="why-desc">All products are 100% genuine with manufacturer warranty. Zero compromises on quality.</p>
      </div>
      <div class="why-card" data-anim="up" data-delay="4">
        <div class="why-ico"><i class="fas fa-headset"></i></div>
        <div class="why-title">Expert Support</div>
        <p class="why-desc">Our experts are always available to help you choose the right product and provide after-sales support.</p>
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
</body>
</html>
