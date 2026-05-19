<?php
require_once __DIR__ . '/includes/functions.php';
$db = getDB();

$phone      = getSetting('store_whatsapp', '94777237962');
$phoneClean = preg_replace('/\D/', '', $phone);

// Brands actually in stock (keyed lowercase for easy lookup)
$dbBrands = $db->query("
    SELECT LOWER(brand) AS brand_key, brand, COUNT(*) AS product_count
    FROM products
    WHERE brand != '' AND brand IS NOT NULL AND is_active = 1
    GROUP BY LOWER(brand), brand
    ORDER BY brand ASC
")->fetchAll();

$inStock = [];
foreach ($dbBrands as $b) {
    $inStock[$b['brand_key']] = $b['product_count'];
}

// Predefined brand catalogue - description, tags, filter category, logo filename
$brandCatalogue = [
    ['name'=>'Intel',    'logo'=>'intel',    'filter'=>'components',             'desc'=>'World\'s leading processor manufacturer. Powering desktops, laptops and servers globally.',                            'tags'=>['Processors','Motherboards']],
    ['name'=>'AMD',      'logo'=>'amd',      'filter'=>'components',             'desc'=>'High-performance processors and graphics cards. AMD Ryzen CPUs and Radeon GPUs available.',                           'tags'=>['Processors','Graphics Cards']],
    ['name'=>'NVIDIA',   'logo'=>'nvidia',   'filter'=>'components',             'desc'=>'The world\'s leading GPU brand. GeForce GTX and RTX series for gaming and professional workloads.',                   'tags'=>['Graphics Cards','Gaming']],
    ['name'=>'ASUS',     'logo'=>'asus',     'filter'=>'components',             'desc'=>'Premium motherboards, graphics cards and monitors. ASUS ROG and TUF gaming series available.',                        'tags'=>['Motherboards','GPUs','Monitors']],
    ['name'=>'MSI',      'logo'=>'msi',      'filter'=>'components',             'desc'=>'High-performance gaming motherboards, GPUs and laptops. MSI Dragon series for extreme gaming.',                       'tags'=>['Motherboards','GPUs']],
    ['name'=>'Kingston', 'logo'=>'kingston', 'filter'=>'components storage',     'desc'=>'The world\'s most trusted memory brand. DDR4 and DDR5 RAM, SSD and flash storage solutions.',                         'tags'=>['RAM','SSD','Flash Storage']],
    ['name'=>'Corsair',  'logo'=>'corsair',  'filter'=>'components peripherals', 'desc'=>'Premium gaming memory, keyboards and peripherals. Corsair Vengeance RAM and K-series keyboards.',                     'tags'=>['RAM','Keyboards','Headsets']],
    ['name'=>'Samsung',  'logo'=>'samsung',  'filter'=>'storage displays',       'desc'=>'World-class SSDs, monitors and mobile displays. Samsung EVO SSDs and premium monitor panels.',                         'tags'=>['SSD','Monitors','Mobile Displays']],
    ['name'=>'WD',       'logo'=>'wd',       'filter'=>'storage',                'desc'=>'Reliable HDD and SSD storage solutions. WD Blue, Green, Red and Black series for every need.',                         'tags'=>['HDD','SSD']],
    ['name'=>'Seagate',  'logo'=>'seagate',  'filter'=>'storage',                'desc'=>'Leading HDD manufacturer with decades of reliability. Barracuda and IronWolf series available.',                       'tags'=>['HDD','NAS Storage']],
    ['name'=>'LG',       'logo'=>'lg',       'filter'=>'displays',               'desc'=>'Premium IPS and OLED monitors. LG UltraGear gaming monitors and UltraWide productivity displays.',                     'tags'=>['Monitors','Gaming Displays']],
    ['name'=>'Logitech', 'logo'=>'logitech', 'filter'=>'peripherals',            'desc'=>'The world\'s top peripherals brand. G-series gaming mice, keyboards, webcams and headsets.',                          'tags'=>['Mouse','Keyboards','Webcams']],
    ['name'=>'Redragon', 'logo'=>'redragon', 'filter'=>'peripherals',            'desc'=>'Affordable gaming peripherals without compromise. Mechanical keyboards, gaming mice and headsets.',                    'tags'=>['Keyboards','Mouse','Headsets']],
    ['name'=>'JBL',      'logo'=>'jbl',      'filter'=>'audio',                  'desc'=>'Premium audio by Harman. JBL earphones, wireless headphones and portable Bluetooth speakers.',                         'tags'=>['Earphones','Headphones','Speakers']],
    ['name'=>'Sony',     'logo'=>'sony',     'filter'=>'audio',                  'desc'=>'World-class audio technology. Sony WH and WF series wireless headphones and noise cancelling earphones.',              'tags'=>['Headphones','Earphones','Audio']],
];

// Append any DB brands not in the predefined list as generic cards
$catalogueKeys = array_map(fn($b) => strtolower($b['name']), $brandCatalogue);
foreach ($dbBrands as $b) {
    if (!in_array($b['brand_key'], $catalogueKeys)) {
        $brandCatalogue[] = [
            'name'   => $b['brand'],
            'logo'   => $b['brand_key'],
            'filter' => 'other',
            'desc'   => 'Genuine ' . $b['brand'] . ' products available in store.',
            'tags'   => [],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Genex carries the world's leading computer and electronics brands - Intel, AMD, NVIDIA, Samsung, Kingston, Logitech and more. All genuine products with warranty.">
  <title>Brands | Genex - Global Xperience</title>
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
      <span class="section-tag">Our Partners</span>
      <h1>Trusted <em>Brands</em></h1>
      <p>We carry only the world's most trusted brands - every product 100% genuine with full manufacturer warranty.</p>
    </div>
    <nav class="breadcrumb" aria-label="Breadcrumb" data-anim="up" data-delay="2">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
      <span>Brands</span>
    </nav>
  </div>
</section>

<!-- BRAND FILTER + GRID -->
<section class="section">
  <div class="container">

    <!-- Filter tabs -->
    <div class="brand-filter-tabs" data-anim="up">
      <button class="brand-tab active" data-brand-filter="all">All Brands</button>
      <button class="brand-tab" data-brand-filter="components">PC Components</button>
      <button class="brand-tab" data-brand-filter="storage">Storage</button>
      <button class="brand-tab" data-brand-filter="peripherals">Peripherals</button>
      <button class="brand-tab" data-brand-filter="displays">Displays</button>
      <button class="brand-tab" data-brand-filter="audio">Audio</button>
    </div>

    <!-- Brand grid -->
    <div class="brands-page-grid" id="brandsGrid">
      <?php foreach ($brandCatalogue as $i => $b):
        $key      = strtolower($b['name']);
        $count    = $inStock[$key] ?? 0;
        $logoPath = 'images/brands/' . $b['logo'] . '.png';
        $hasLogo  = file_exists(__DIR__ . '/' . $logoPath);
        $shopLink = 'shop.php?q=' . urlencode($b['name']);
        $delay    = ($i % 4) + 1;
      ?>
      <div class="brand-pg-card" data-brand-cat="<?= htmlspecialchars($b['filter']) ?>" data-anim="up" data-delay="<?= $delay ?>">
        <div class="bpg-top">
          <div class="bpg-logo">
            <?php if ($hasLogo): ?>
              <img src="<?= htmlspecialchars($logoPath) ?>" alt="<?= htmlspecialchars($b['name']) ?>">
            <?php else: ?>
              <span style="font-size:18px;font-weight:800;color:var(--text)"><?= htmlspecialchars($b['name']) ?></span>
            <?php endif ?>
          </div>
          <?php if ($count > 0): ?>
            <span class="bpg-genuine"><i class="fas fa-shield-alt"></i> Genuine &middot; <?= $count ?> item<?= $count !== 1 ? 's' : '' ?></span>
          <?php else: ?>
            <span class="bpg-genuine"><i class="fas fa-shield-alt"></i> Genuine</span>
          <?php endif ?>
        </div>
        <p class="bpg-desc"><?= htmlspecialchars($b['desc']) ?></p>
        <?php if ($b['tags']): ?>
        <div class="bpg-tags">
          <?php foreach ($b['tags'] as $tag): ?>
            <span><?= htmlspecialchars($tag) ?></span>
          <?php endforeach ?>
        </div>
        <?php endif ?>
        <a href="<?= $shopLink ?>" class="bpg-btn"><i class="fas fa-shopping-bag"></i> Shop <?= htmlspecialchars($b['name']) ?></a>
      </div>
      <?php endforeach ?>
    </div>

  </div>
</section>

<!-- MARQUEE -->
<div class="brands-section">
  <div class="container" style="margin-bottom:28px">
    <div class="section-header" data-anim="up" style="margin-bottom:0">
      <span class="section-tag">All Partners</span>
      <h2 class="section-title" style="margin-bottom:0">Brands We <em>Carry</em></h2>
    </div>
  </div>
  <div class="brands-marquee-wrap">
    <div class="brands-marquee-track">
      <?php
      $marqueeLogos = ['intel','amd','nvidia','asus','msi','kingston','corsair','samsung','wd','seagate','lg','logitech','redragon','jbl','sony'];
      // Add DB-only brands to marquee too
      foreach ($dbBrands as $b) {
          if (!in_array($b['brand_key'], $marqueeLogos)) {
              $marqueeLogos[] = $b['brand_key'];
          }
      }
      $names = array_column($brandCatalogue, 'name', 'logo');
      // Render twice for seamless loop
      for ($pass = 0; $pass < 2; $pass++):
        foreach ($marqueeLogos as $slug):
          $logoFile = 'images/brands/' . $slug . '.png';
          $altText  = htmlspecialchars($names[$slug] ?? ucfirst($slug));
          if (!file_exists(__DIR__ . '/' . $logoFile)) continue;
      ?>
      <div class="bm-item"><div class="bm-card"><img src="<?= $logoFile ?>" alt="<?= $altText ?>"></div></div>
      <?php endforeach; endfor; ?>
    </div>
  </div>
</div>

<!-- CTA -->
<section class="section section-dark">
  <div class="container">
    <div class="brands-cta" data-anim="up">
      <div class="brands-cta-text">
        <span class="section-tag">Wholesale Inquiries</span>
        <h2 class="section-title" style="margin-bottom:8px">Looking for <em>Bulk Orders?</em></h2>
        <p style="color:var(--text-muted);font-size:14px;max-width:480px">We offer special wholesale pricing for all the brands above. Contact us for bulk order quotes and dealer pricing.</p>
      </div>
      <div class="brands-cta-btns">
        <a href="https://wa.me/<?= htmlspecialchars($phoneClean) ?>" target="_blank" rel="noopener" class="btn btn-green"><i class="fab fa-whatsapp"></i> WhatsApp for Quote</a>
        <a href="contact.php" class="btn btn-ghost"><i class="fas fa-envelope"></i> Send Inquiry</a>
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
const tabs  = document.querySelectorAll('.brand-tab');
const cards = document.querySelectorAll('.brand-pg-card');

tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    tabs.forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    const f = tab.dataset.brandFilter;
    cards.forEach(c => {
      const cats = c.dataset.brandCat || '';
      const show = f === 'all' || cats.includes(f);
      c.style.display = show ? '' : 'none';
      if (show) {
        c.style.opacity = '0';
        c.style.transform = 'translateY(16px)';
        requestAnimationFrame(() => {
          c.style.transition = 'opacity .3s ease, transform .3s ease';
          c.style.opacity = '1';
          c.style.transform = 'none';
        });
      }
    });
  });
});
</script>
</body>
</html>
