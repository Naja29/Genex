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

$brands = $db->query("
    SELECT DISTINCT LOWER(brand) AS brand_key, brand
    FROM products
    WHERE brand != '' AND brand IS NOT NULL AND is_active = 1
    ORDER BY brand ASC
")->fetchAll();

$products = $db->query('
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
')->fetchAll();

// URL pre-select values (passed to JS)
$initCat    = htmlspecialchars(get('cat'));
$initSearch = htmlspecialchars(get('q'));
$totalAll   = count($products);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Shop computer parts, electronics and accessories at Genex. Processors, RAM, GPUs, storage, monitors, peripherals and more. Best prices in Sri Lanka.">
  <title>Shop | Genex - Global Xperience</title>
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
      <span class="section-tag">Our Products</span>
      <h1>Shop <em>All Products</em></h1>
      <p>Browse our full range of genuine computer parts, electronics and accessories. Wholesale &amp; Retail prices available.</p>
    </div>
    <nav class="breadcrumb" aria-label="Breadcrumb" data-anim="up" data-delay="2">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Shop</span>
    </nav>
  </div>
</section>

<!-- SHOP LAYOUT -->
<section class="section">
  <div class="container">
    <div class="shop-layout">

      <!-- SIDEBAR -->
      <aside class="shop-sidebar" id="shopSidebar">

        <div class="sidebar-header">
          <span>Filter Products</span>
          <button class="sidebar-close" id="sidebarClose" aria-label="Close filters"><i class="fas fa-times"></i></button>
        </div>

        <!-- Search -->
        <div class="sf-block">
          <div class="sf-search">
            <i class="fas fa-search"></i>
            <input type="text" id="shopSearch" placeholder="Search products..." value="<?= $initSearch ?>">
          </div>
        </div>

        <!-- Categories -->
        <div class="sf-block">
          <h4 class="sf-title">Categories</h4>
          <ul class="sf-cat-list">
            <li>
              <button class="sf-cat active" data-filter="all">
                All Products <span class="sf-count" id="countAll"><?= $totalAll ?></span>
              </button>
            </li>
            <?php foreach ($categories as $cat): ?>
            <li>
              <button class="sf-cat" data-filter="<?= htmlspecialchars($cat['slug']) ?>">
                <i class="<?= htmlspecialchars($cat['icon'] ?? 'fas fa-box') ?>"></i>
                <?= htmlspecialchars($cat['name']) ?>
                <span class="sf-count"><?= $cat['product_count'] ?></span>
              </button>
            </li>
            <?php endforeach ?>
          </ul>
        </div>

        <!-- Brands -->
        <?php if ($brands): ?>
        <div class="sf-block">
          <h4 class="sf-title">Brands</h4>
          <ul class="sf-brand-list">
            <?php foreach ($brands as $b): ?>
            <li>
              <label class="sf-check">
                <input type="checkbox" value="<?= htmlspecialchars($b['brand_key']) ?>">
                <?= htmlspecialchars($b['brand']) ?>
              </label>
            </li>
            <?php endforeach ?>
          </ul>
        </div>
        <?php endif ?>

        <!-- Availability -->
        <div class="sf-block">
          <h4 class="sf-title">Availability</h4>
          <ul class="sf-brand-list">
            <li><label class="sf-check"><input type="checkbox" value="instock" id="filterInStock"> In Stock Only</label></li>
            <li><label class="sf-check"><input type="checkbox" value="sale" id="filterSale"> On Sale</label></li>
          </ul>
        </div>

        <button class="btn btn-ghost btn-sm sf-reset" id="sfReset"><i class="fas fa-undo"></i> Reset Filters</button>
      </aside>

      <div class="sidebar-overlay" id="sidebarOverlay"></div>

      <!-- PRODUCTS AREA -->
      <div class="shop-content">

        <!-- Toolbar -->
        <div class="shop-toolbar">
          <div class="st-left">
            <button class="filter-toggle-btn" id="filterToggleBtn"><i class="fas fa-sliders-h"></i> Filters</button>
            <span class="st-count" id="stCount">Showing <strong><?= $totalAll ?></strong> product<?= $totalAll != 1 ? 's' : '' ?></span>
          </div>
          <div class="st-right">
            <label class="st-sort-label" for="shopSort"><i class="fas fa-sort"></i></label>
            <select class="st-sort" id="shopSort">
              <option value="default">Default Sorting</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="name-asc">Name: A-Z</option>
            </select>
          </div>
        </div>

        <!-- No results -->
        <div class="shop-no-results" id="shopNoResults" style="display:none">
          <i class="fas fa-search"></i>
          <p>No products found matching your filters.</p>
          <button class="btn btn-ghost btn-sm" id="noResultsReset">Clear Filters</button>
        </div>

        <!-- Product Grid -->
        <div class="shop-products-grid" id="shopGrid">
          <?php if ($products): ?>
            <?php foreach ($products as $p):
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
          <?php else: ?>
            <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--text-muted)">
              <i class="fas fa-box-open" style="font-size:48px;margin-bottom:16px;display:block;opacity:.3"></i>
              <h3 style="margin-bottom:8px">No products yet</h3>
              <p>Add products from the <a href="admin/products.php" style="color:var(--primary)">admin panel</a>.</p>
            </div>
          <?php endif ?>
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
(function () {
  const grid       = document.getElementById('shopGrid');
  const cards      = Array.from(grid.querySelectorAll('.product-card'));
  const catBtns    = document.querySelectorAll('.sf-cat');
  const brandBoxes = document.querySelectorAll('.sf-brand-list input[type=checkbox]');
  const searchEl   = document.getElementById('shopSearch');
  const sortEl     = document.getElementById('shopSort');
  const stCount    = document.getElementById('stCount');
  const noResults  = document.getElementById('shopNoResults');

  let activeCat    = 'all';
  let activeBrands = [];
  let onlyInStock  = false;
  let onlyOnSale   = false;
  let searchQ      = '';
  let sortMode     = 'default';

  function applyFilters() {
    activeBrands = Array.from(brandBoxes)
      .filter(cb => cb.checked && !['instock','sale'].includes(cb.value))
      .map(cb => cb.value);
    onlyInStock = document.getElementById('filterInStock').checked;
    onlyOnSale  = document.getElementById('filterSale').checked;
    searchQ     = searchEl.value.trim().toLowerCase();
    sortMode    = sortEl.value;

    let visible = cards.filter(c => {
      if (activeCat !== 'all' && c.dataset.category !== activeCat) return false;
      if (activeBrands.length && !activeBrands.includes(c.dataset.brand)) return false;
      if (onlyInStock && c.dataset.instock !== '1') return false;
      if (onlyOnSale  && c.dataset.sale    !== '1') return false;
      if (searchQ && !c.dataset.name.toLowerCase().includes(searchQ)) return false;
      return true;
    });

    if (sortMode === 'price-asc')  visible.sort((a,b) => +a.dataset.price - +b.dataset.price);
    if (sortMode === 'price-desc') visible.sort((a,b) => +b.dataset.price - +a.dataset.price);
    if (sortMode === 'name-asc')   visible.sort((a,b) => a.dataset.name.localeCompare(b.dataset.name));

    const visSet = new Set(visible);
    cards.forEach(c => c.style.display = visSet.has(c) ? 'flex' : 'none');
    visible.forEach(c => grid.appendChild(c));

    stCount.innerHTML = `Showing <strong>${visible.length}</strong> product${visible.length !== 1 ? 's' : ''}`;
    noResults.style.display = visible.length === 0 ? 'flex' : 'none';
    grid.style.display      = visible.length === 0 ? 'none' : '';
  }

  catBtns.forEach(btn => btn.addEventListener('click', () => {
    catBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeCat = btn.dataset.filter;
    applyFilters();
  }));

  brandBoxes.forEach(cb => cb.addEventListener('change', applyFilters));
  searchEl.addEventListener('input', applyFilters);
  sortEl.addEventListener('change', applyFilters);

  function resetAll() {
    activeCat = 'all';
    catBtns.forEach(b => b.classList.toggle('active', b.dataset.filter === 'all'));
    brandBoxes.forEach(cb => cb.checked = false);
    searchEl.value = '';
    sortEl.value   = 'default';
    applyFilters();
  }
  document.getElementById('sfReset').addEventListener('click', resetAll);
  document.getElementById('noResultsReset').addEventListener('click', resetAll);

  // Mobile sidebar
  const sidebar   = document.getElementById('shopSidebar');
  const overlay   = document.getElementById('sidebarOverlay');
  const toggleBtn = document.getElementById('filterToggleBtn');
  const closeBtn  = document.getElementById('sidebarClose');
  function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('show'); document.body.style.overflow='hidden'; }
  function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('show'); document.body.style.overflow=''; }
  toggleBtn.addEventListener('click', openSidebar);
  closeBtn.addEventListener('click', closeSidebar);
  overlay.addEventListener('click', closeSidebar);

  // Pre-select from URL params
  const params = new URLSearchParams(window.location.search);
  const urlCat = params.get('cat');
  const urlQ   = params.get('q');

  if (urlCat) {
    const btn = Array.from(catBtns).find(b => b.dataset.filter === urlCat);
    if (btn) { catBtns.forEach(b => b.classList.remove('active')); btn.classList.add('active'); activeCat = urlCat; }
  }
  if (urlQ) searchEl.value = urlQ;

  applyFilters();
})();
</script>

</body>
</html>
