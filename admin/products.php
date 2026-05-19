<?php
$pageTitle  = 'Products';
$activePage = 'products';
require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$db = getDB();

// Delete via POST 
if (isPost()) {
    $action = post('action');
    $id     = (int)post('id');

    if ($action === 'delete') {
        // Delete specs and images too (cascades via FK, but let's also remove image files)
        $img = $db->prepare('SELECT image_path FROM product_images WHERE product_id = ?');
        $img->execute([$id]);
        foreach ($img->fetchAll() as $row) {
            $path = ROOT_PATH . $row['image_path'];
            if (file_exists($path)) unlink($path);
        }
        // Delete thumbnail
        $thumb = $db->prepare('SELECT thumbnail FROM products WHERE id = ?');
        $thumb->execute([$id]);
        $t = $thumb->fetchColumn();
        if ($t && file_exists(ROOT_PATH . $t)) unlink(ROOT_PATH . $t);

        $db->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        flash('success', 'Product deleted successfully.');
    }
    redirect(BASE_URL . 'admin/products.php?' . http_build_query(array_filter([
        'category' => get('category'),
        'search'   => get('search'),
        'page'     => get('page'),
    ])));
}

// Filters 
$search   = get('search');
$catId    = (int)get('category');
$filterLow= get('filter') === 'low_stock';
$page     = max(1, (int)get('page', '1'));
$perPage  = 15;

$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]  = '(p.name LIKE ? OR p.sku LIKE ? OR p.brand LIKE ?)';
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s]);
}
if ($catId) {
    $where[]  = 'p.category_id = ?';
    $params[] = $catId;
}
if ($filterLow) {
    $where[] = 'p.stock_qty <= 5 AND p.is_active = 1';
}

$whereStr = implode(' AND ', $where);

$total = $db->prepare("SELECT COUNT(*) FROM products p WHERE $whereStr");
$total->execute($params);
$pg = paginate((int)$total->fetchColumn(), $perPage, $page);

$stmt = $db->prepare("
    SELECT p.*, c.name AS cat_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE $whereStr
    ORDER BY p.created_at DESC
    LIMIT {$pg['per_page']} OFFSET {$pg['offset']}
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Categories for filter dropdown
$cats = $db->query('SELECT id, name FROM categories WHERE is_active=1 ORDER BY sort_order, name')->fetchAll();

// Summary counts
$totalActive   = $db->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn();
$totalFeatured = $db->query('SELECT COUNT(*) FROM products WHERE is_featured=1')->fetchColumn();
$totalLowStock = $db->query('SELECT COUNT(*) FROM products WHERE stock_qty<=5 AND is_active=1')->fetchColumn();
?>

<div class="page-header">
  <div>
    <h1>Products</h1>
    <p><?= number_format($pg['total']) ?> product<?= $pg['total'] != 1 ? 's' : '' ?> found</p>
  </div>
  <div class="ph-actions">
    <a href="product-form.php" class="btn btn-gold"><i class="fas fa-plus"></i> Add Product</a>
  </div>
</div>

<!-- Summary strip -->
<div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
  <a href="products.php" class="<?= !$catId&&!$search&&!$filterLow?'stat-strip active':'stat-strip' ?>">
    <i class="fas fa-box-open"></i> All <strong><?= $db->query('SELECT COUNT(*) FROM products')->fetchColumn() ?></strong>
  </a>
  <a href="products.php?filter=low_stock" class="<?= $filterLow?'stat-strip active':'stat-strip' ?>">
    <i class="fas fa-exclamation-triangle" style="color:var(--yellow)"></i> Low Stock <strong><?= $totalLowStock ?></strong>
  </a>
  <span class="stat-strip">
    <i class="fas fa-star" style="color:var(--primary)"></i> Featured <strong><?= $totalFeatured ?></strong>
  </span>
  <span class="stat-strip">
    <i class="fas fa-check-circle" style="color:var(--green)"></i> Active <strong><?= $totalActive ?></strong>
  </span>
</div>

<!-- Filters bar -->
<div class="card" style="padding:14px 20px;margin-bottom:20px">
  <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:200px;background:var(--bg-3);border:1px solid var(--border);border-radius:var(--r);padding:0 14px">
      <i class="fas fa-search" style="color:var(--text-muted);font-size:13px"></i>
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
        placeholder="Search by name, SKU, brand..."
        style="background:none;border:none;outline:none;color:var(--text);font-family:'Poppins',sans-serif;font-size:13px;padding:10px 0;width:100%">
    </div>
    <select name="category" class="form-select" style="width:180px">
      <option value="">All Categories</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $catId==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach ?>
    </select>
    <button type="submit" class="btn btn-ghost"><i class="fas fa-filter"></i> Filter</button>
    <?php if ($search || $catId || $filterLow): ?>
      <a href="products.php" class="btn btn-ghost"><i class="fas fa-times"></i> Clear</a>
    <?php endif ?>
  </form>
</div>

<!-- Products table -->
<div class="card" style="padding:0;overflow:hidden">
  <?php if ($products): ?>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width:46px"></th>
          <th>Product</th>
          <th>Category</th>
          <th>Price</th>
          <th style="text-align:center">Stock</th>
          <th style="text-align:center">Badge</th>
          <th style="text-align:center">Featured</th>
          <th style="text-align:center">Status</th>
          <th style="text-align:right;width:100px">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($products as $p):
        $stockClass = $p['stock_qty'] <= 0 ? 'badge-red' : ($p['stock_qty'] <= 5 ? 'badge-yellow' : 'badge-green');
      ?>
        <tr>
          <!-- Thumbnail -->
          <td style="padding:10px 8px 10px 16px">
            <?php if ($p['thumbnail'] && file_exists(ROOT_PATH . $p['thumbnail'])): ?>
              <img src="<?= BASE_URL . htmlspecialchars($p['thumbnail']) ?>" alt=""
                style="width:40px;height:40px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
            <?php else: ?>
              <div style="width:40px;height:40px;border-radius:8px;background:var(--bg-3);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-dim);font-size:16px">
                <i class="fas fa-image"></i>
              </div>
            <?php endif ?>
          </td>

          <!-- Name -->
          <td>
            <div style="font-weight:600;color:var(--text);font-size:13px;max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              <?= htmlspecialchars($p['name']) ?>
            </div>
            <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px">
              <?= $p['sku'] ? 'SKU: ' . htmlspecialchars($p['sku']) : '' ?>
              <?= $p['brand'] ? ' &bull; ' . htmlspecialchars($p['brand']) : '' ?>
            </div>
          </td>

          <!-- Category -->
          <td style="font-size:12.5px;color:var(--text-muted)"><?= htmlspecialchars($p['cat_name']) ?></td>

          <!-- Price -->
          <td>
            <div style="font-weight:700;color:var(--primary);font-size:13.5px"><?= fmtPrice((float)$p['price']) ?></div>
            <?php if ($p['old_price']): ?>
              <div style="font-size:11.5px;color:var(--text-dim);text-decoration:line-through"><?= fmtPrice((float)$p['old_price']) ?></div>
            <?php endif ?>
          </td>

          <!-- Stock -->
          <td style="text-align:center">
            <span class="badge <?= $stockClass ?>"><?= $p['stock_qty'] <= 0 ? 'Out' : $p['stock_qty'] ?></span>
          </td>

          <!-- Badge -->
          <td style="text-align:center">
            <?php if ($p['badge']): ?>
              <span class="badge <?= $p['badge']==='HOT'?'badge-red':($p['badge']==='NEW'?'badge-blue':'badge-yellow') ?>"><?= $p['badge'] ?></span>
            <?php else: ?>
              <span style="color:var(--text-dim);font-size:12px">-</span>
            <?php endif ?>
          </td>

          <!-- Featured toggle -->
          <td style="text-align:center">
            <button type="button" class="js-toggle-product" style="background:none;border:none;cursor:pointer;font-size:18px;line-height:1"
              data-id="<?= $p['id'] ?>" data-field="is_featured" data-value="<?= $p['is_featured'] ?>"
              title="<?= $p['is_featured']?'Remove from featured':'Mark as featured' ?>">
              <i class="<?= $p['is_featured']?'fas':'far' ?> fa-star" style="color:<?= $p['is_featured']?'var(--primary)':'var(--text-dim)' ?>"></i>
            </button>
          </td>

          <!-- Active toggle -->
          <td style="text-align:center">
            <button type="button" class="badge <?= $p['is_active']?'badge-green':'badge-red' ?> js-toggle-product"
              style="border:none;cursor:pointer;padding:3px 10px"
              data-id="<?= $p['id'] ?>" data-field="is_active" data-value="<?= $p['is_active'] ?>">
              <?= $p['is_active']?'Active':'Inactive' ?>
            </button>
          </td>

          <!-- Actions -->
          <td style="text-align:right">
            <div style="display:flex;gap:6px;justify-content:flex-end">
              <a href="product-form.php?id=<?= $p['id'] ?>" class="btn btn-ghost btn-icon btn-sm" title="Edit">
                <i class="fas fa-pen"></i>
              </a>
              <button type="button" class="btn btn-danger btn-icon btn-sm js-delete-product" title="Delete"
                data-id="<?= $p['id'] ?>"
                data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      <?php endforeach ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($pg['pages'] > 1): ?>
  <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid var(--border)">
    <span style="font-size:12.5px;color:var(--text-muted)">
      Showing <?= $pg['offset']+1 ?>–<?= min($pg['offset']+$pg['per_page'], $pg['total']) ?> of <?= $pg['total'] ?>
    </span>
    <div style="display:flex;gap:6px">
      <?php
      $qs = http_build_query(array_filter(['search'=>$search,'category'=>$catId]));
      for ($i = 1; $i <= $pg['pages']; $i++):
      ?>
        <a href="products.php?<?= $qs ?>&page=<?= $i ?>"
          style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;font-size:13px;font-weight:600;
                 background:<?= $i==$page?'var(--gold)':'var(--bg-3)' ?>;
                 color:<?= $i==$page?'#fff':'var(--text-muted)' ?>;
                 border:1px solid <?= $i==$page?'transparent':'var(--border)' ?>">
          <?= $i ?>
        </a>
      <?php endfor ?>
    </div>
  </div>
  <?php endif ?>

  <?php else: ?>
    <div class="empty-state">
      <i class="fas fa-box-open"></i>
      <h3>No products found</h3>
      <p><?= $search||$catId ? 'Try adjusting your filters.' : 'Add your first product to get started.' ?></p>
      <a href="product-form.php" class="btn btn-gold"><i class="fas fa-plus"></i> Add Product</a>
    </div>
  <?php endif ?>
</div>

<style>
.stat-strip{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:var(--r);background:var(--bg-2);border:1px solid var(--border);font-size:13px;color:var(--text-muted);transition:var(--t)}
.stat-strip strong{color:var(--text);margin-left:2px}
.stat-strip:hover,.stat-strip.active{border-color:rgba(212,146,10,.35);color:var(--primary);background:rgba(212,146,10,.07)}
.stat-strip.active strong{color:var(--primary)}
</style>

<script>
document.querySelectorAll('.js-delete-product').forEach(function (btn) {
  btn.addEventListener('click', function () {
    adminDeleteRow(btn, '<?= BASE_URL ?>admin/api/delete-product.php');
  });
});

document.querySelectorAll('.js-toggle-product').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var field = btn.dataset.field;
    fetch('<?= BASE_URL ?>admin/api/toggle-product.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: parseInt(btn.dataset.id), field: field })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (!res.success) { adminSnackbar('Update failed.', 'error'); return; }
      var val = res.value === 1;
      if (field === 'is_active') {
        btn.className = 'badge ' + (val ? 'badge-green' : 'badge-red') + ' js-toggle-product';
        btn.textContent = val ? 'Active' : 'Inactive';
        adminSnackbar('Product ' + (val ? 'activated' : 'deactivated') + '.', 'success');
      } else {
        var icon = btn.querySelector('i');
        icon.className = (val ? 'fas' : 'far') + ' fa-star';
        icon.style.color = val ? 'var(--primary)' : 'var(--text-dim)';
        btn.title = val ? 'Remove from featured' : 'Mark as featured';
        adminSnackbar(val ? 'Marked as featured.' : 'Removed from featured.', 'success');
      }
      btn.dataset.value = res.value;
    })
    .catch(function () { adminSnackbar('Network error. Try again.', 'error'); });
  });
});
</script>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
