<?php
$_isAjax = $_SERVER['REQUEST_METHOD'] === 'POST' && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
if ($_isAjax) {
    require_once dirname(__DIR__) . '/includes/functions.php';
    require_once dirname(__DIR__) . '/includes/auth.php';
    if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Session expired.']); exit; }
} else {
    $pageTitle  = 'Categories';
    $activePage = 'categories';
    require_once __DIR__ . '/includes/layout.php';
    require_once dirname(__DIR__) . '/includes/functions.php';
}

$db = getDB();

// Handle POST actions 
if (isPost()) {
    $action = post('action');

    // ADD
    if ($action === 'add') {
        $name  = post('name');
        $slug  = post('slug') ?: slugify($name);
        $icon  = post('icon', 'fas fa-box');
        $desc  = post('description');
        $sort  = (int)post('sort_order', '0');
        $active= post('is_active', '1') === '1' ? 1 : 0;

        if ($name) {
            // Ensure unique slug
            $base = $slug; $i = 1;
            while ($db->prepare('SELECT id FROM categories WHERE slug = ?')->execute([$slug]) && $db->query("SELECT id FROM categories WHERE slug = '$slug'")->fetch()) {
                $slug = $base . '-' . $i++;
            }
            try {
                $db->prepare('INSERT INTO categories (name,slug,icon,description,sort_order,is_active) VALUES (?,?,?,?,?,?)')
                   ->execute([$name, $slug, $icon, $desc, $sort, $active]);
                if (isAjax()) jsonOk("Category \"$name\" added successfully.");
                flash('success', "Category \"$name\" added successfully.");
            } catch (PDOException $e) {
                if (isAjax()) jsonErr('Slug already exists. Please use a different name.');
                flash('error', 'Slug already exists. Please use a different name.');
            }
        } else {
            if (isAjax()) jsonErr('Category name is required.');
            flash('error', 'Category name is required.');
        }
        redirect(BASE_URL . 'admin/categories.php');
    }

    // EDIT
    if ($action === 'edit') {
        $id    = (int)post('id');
        $name  = post('name');
        $slug  = post('slug') ?: slugify($name);
        $icon  = post('icon', 'fas fa-box');
        $desc  = post('description');
        $sort  = (int)post('sort_order', '0');
        $active= post('is_active', '1') === '1' ? 1 : 0;

        if ($id && $name) {
            try {
                $db->prepare('UPDATE categories SET name=?,slug=?,icon=?,description=?,sort_order=?,is_active=? WHERE id=?')
                   ->execute([$name, $slug, $icon, $desc, $sort, $active, $id]);
                if (isAjax()) jsonOk('Category updated successfully.');
                flash('success', "Category updated successfully.");
            } catch (PDOException $e) {
                if (isAjax()) jsonErr('Slug already in use by another category.');
                flash('error', 'Slug already in use by another category.');
            }
        } else {
            if (isAjax()) jsonErr('Name is required.');
        }
        redirect(BASE_URL . 'admin/categories.php');
    }

    // DELETE
    if ($action === 'delete') {
        $id = (int)post('id');
        $count = $db->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $count->execute([$id]);
        if ($count->fetchColumn() > 0) {
            flash('error', 'Cannot delete - this category has products. Reassign them first.');
        } else {
            $db->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            flash('success', 'Category deleted.');
        }
        redirect(BASE_URL . 'admin/categories.php');
    }

}

// Fetch data 
$categories = $db->query('
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    GROUP BY c.id
    ORDER BY c.sort_order ASC, c.name ASC
')->fetchAll();

// Edit mode - load existing row
$editing = [];
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch();
}

// Common icon suggestions
$iconSuggestions = [
    'fas fa-microchip'   => 'Processors',
    'fas fa-server'      => 'Motherboards',
    'fas fa-memory'      => 'RAM',
    'fas fa-hdd'         => 'Storage',
    'fas fa-tv'          => 'GPU / Monitors',
    'fas fa-desktop'     => 'Desktop / Monitor',
    'fas fa-keyboard'    => 'Keyboards',
    'fas fa-mouse'       => 'Mouse',
    'fas fa-wifi'        => 'Networking',
    'fas fa-mobile-alt'  => 'Mobile',
    'fas fa-bolt'        => 'Power Supply',
    'fas fa-cube'        => 'PC Cases',
    'fas fa-wind'        => 'Cooling',
    'fas fa-laptop'      => 'Laptops',
    'fas fa-headphones'  => 'Audio',
    'fas fa-camera'      => 'Cameras',
    'fas fa-print'       => 'Printers',
    'fas fa-tags'        => 'General',
    'fas fa-box'         => 'Other',
];
?>

<div class="page-header">
  <div>
    <h1>Categories</h1>
    <p>Manage product categories for your store.</p>
  </div>
  <?php if ($editing): ?>
  <div class="ph-actions">
    <a href="categories.php" class="btn btn-ghost"><i class="fas fa-times"></i> Cancel Edit</a>
  </div>
  <?php endif ?>
</div>

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start">

  <!-- Category Table -->
  <div class="card" style="padding:0;overflow:hidden">
    <div style="padding:18px 24px 0;display:flex;align-items:center;justify-content:space-between">
      <div class="card-title" style="margin:0">All Categories <span class="badge badge-gray" style="margin-left:8px" id="catCountBadge"><?= count($categories) ?></span></div>
    </div>
    <div style="padding:12px 0 0">
    <?php if ($categories): ?>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width:40px">#</th>
              <th>Category</th>
              <th>Slug</th>
              <th style="text-align:center">Products</th>
              <th style="text-align:center">Sort</th>
              <th style="text-align:center">Status</th>
              <th style="text-align:right">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($categories as $i => $cat): ?>
            <tr>
              <td style="color:var(--text-dim);font-size:12px"><?= $i + 1 ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:12px">
                  <div style="width:36px;height:36px;border-radius:8px;background:rgba(212,146,10,.1);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:15px;flex-shrink:0">
                    <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
                  </div>
                  <div>
                    <div style="font-weight:600;color:var(--text);font-size:13.5px"><?= htmlspecialchars($cat['name']) ?></div>
                    <?php if ($cat['description']): ?>
                      <div style="font-size:11.5px;color:var(--text-muted);margin-top:1px"><?= htmlspecialchars($cat['description']) ?></div>
                    <?php endif ?>
                  </div>
                </div>
              </td>
              <td><code style="font-size:12px;color:var(--text-muted);background:var(--bg-3);padding:2px 8px;border-radius:4px"><?= htmlspecialchars($cat['slug']) ?></code></td>
              <td style="text-align:center">
                <a href="products.php?category=<?= $cat['id'] ?>" class="badge <?= $cat['product_count'] > 0 ? 'badge-blue' : 'badge-gray' ?>">
                  <?= $cat['product_count'] ?>
                </a>
              </td>
              <td style="text-align:center;color:var(--text-muted);font-size:13px"><?= $cat['sort_order'] ?></td>
              <td style="text-align:center">
                <button type="button"
                  class="badge <?= $cat['is_active'] ? 'badge-green' : 'badge-red' ?> js-toggle-cat"
                  style="border:none;cursor:pointer;padding:3px 10px"
                  data-id="<?= $cat['id'] ?>"
                  data-active="<?= $cat['is_active'] ?>">
                  <?= $cat['is_active'] ? 'Active' : 'Inactive' ?>
                </button>
              </td>
              <td style="text-align:right">
                <div style="display:flex;gap:6px;justify-content:flex-end">
                  <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-ghost btn-icon btn-sm" title="Edit">
                    <i class="fas fa-pen"></i>
                  </a>
                  <button type="button" class="btn btn-danger btn-icon btn-sm js-delete-category" title="Delete"
                    data-id="<?= $cat['id'] ?>">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-tags"></i>
        <h3>No categories yet</h3>
        <p>Add your first category using the form.</p>
      </div>
    <?php endif ?>
    </div>
  </div>

  <!-- Add / Edit Form -->
  <div class="card" style="position:sticky;top:80px">
    <div class="card-title">
      <?= $editing ? '<i class="fas fa-pen" style="color:var(--primary)"></i> Edit Category' : '<i class="fas fa-plus" style="color:var(--primary)"></i> Add Category' ?>
    </div>

    <form method="POST" id="catForm">
      <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
      <?php if ($editing): ?>
        <input type="hidden" name="id" value="<?= $editing['id'] ?>">
      <?php endif ?>

      <div class="form-grid" style="gap:14px">

        <div class="form-group">
          <label class="form-label">Name <span style="color:var(--red)">*</span></label>
          <input type="text" name="name" class="form-input" placeholder="e.g. Processors" required
            value="<?= htmlspecialchars($editing['name'] ?? '') ?>" id="catName">
        </div>

        <div class="form-group">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="form-input" placeholder="auto-generated"
            value="<?= htmlspecialchars($editing['slug'] ?? '') ?>" id="catSlug">
          <span class="form-hint">Leave blank to auto-generate from name</span>
        </div>

        <!-- Icon picker -->
        <div class="form-group">
          <label class="form-label">Icon</label>
          <div style="display:flex;gap:8px;align-items:center">
            <div id="iconPreview" style="width:40px;height:40px;border-radius:8px;background:rgba(212,146,10,.1);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:18px;flex-shrink:0">
              <i class="<?= htmlspecialchars($editing['icon'] ?? 'fas fa-box') ?>" id="iconPreviewI"></i>
            </div>
            <input type="text" name="icon" class="form-input" id="iconInput"
              placeholder="fas fa-box"
              value="<?= htmlspecialchars($editing['icon'] ?? 'fas fa-box') ?>">
          </div>
          <!-- Quick picks -->
          <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px">
            <?php foreach ($iconSuggestions as $cls => $label): ?>
              <button type="button" class="icon-pick" data-icon="<?= $cls ?>" title="<?= $label ?>"
                style="width:32px;height:32px;border-radius:6px;background:var(--bg-3);border:1px solid var(--border);color:var(--text-muted);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;transition:all .2s">
                <i class="<?= $cls ?>"></i>
              </button>
            <?php endforeach ?>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <input type="text" name="description" class="form-input" placeholder="Short description (optional)"
            value="<?= htmlspecialchars($editing['description'] ?? '') ?>">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-input" min="0" value="<?= $editing['sort_order'] ?? '0' ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-select">
              <option value="1" <?= ($editing['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
              <option value="0" <?= ($editing['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:4px">
          <button type="submit" class="btn btn-gold" style="flex:1">
            <i class="fas <?= $editing ? 'fa-save' : 'fa-plus' ?>"></i>
            <?= $editing ? 'Save Changes' : 'Add Category' ?>
          </button>
          <?php if ($editing): ?>
            <a href="categories.php" class="btn btn-ghost"><i class="fas fa-times"></i></a>
          <?php endif ?>
        </div>

      </div>
    </form>
  </div>

</div><!-- /grid -->

<script>
// Auto-generate slug from name
const nameInput = document.getElementById('catName');
const slugInput = document.getElementById('catSlug');
<?php if (!$editing): ?>
nameInput.addEventListener('input', () => {
  slugInput.value = nameInput.value.toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/[\s]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
});
<?php endif ?>

// Icon picker
const iconInput   = document.getElementById('iconInput');
const iconPreviewI = document.getElementById('iconPreviewI');

iconInput.addEventListener('input', () => {
  iconPreviewI.className = iconInput.value.trim() || 'fas fa-box';
});

document.querySelectorAll('.icon-pick').forEach(btn => {
  btn.addEventListener('click', () => {
    const cls = btn.dataset.icon;
    iconInput.value = cls;
    iconPreviewI.className = cls;
    document.querySelectorAll('.icon-pick').forEach(b => b.style.background = '');
    btn.style.background = 'rgba(212,146,10,.15)';
    btn.style.color = 'var(--primary)';
    btn.style.borderColor = 'rgba(212,146,10,.3)';
  });
});

// Highlight active icon pick on load
document.querySelectorAll('.icon-pick').forEach(btn => {
  if (btn.dataset.icon === iconInput.value.trim()) {
    btn.style.background = 'rgba(212,146,10,.15)';
    btn.style.color = 'var(--primary)';
    btn.style.borderColor = 'rgba(212,146,10,.3)';
  }
});
</script>

<script>
// AJAX submit for add/edit category form
document.getElementById('catForm').addEventListener('submit', function (e) {
  e.preventDefault();
  var form = this;
  var btn  = form.querySelector('[type="submit"]');
  var orig = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
  fetch(location.pathname, {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: new FormData(form)
  })
  .then(function (r) { return r.json(); })
  .then(function (res) {
    btn.disabled = false;
    btn.innerHTML = orig;
    adminSnackbar(res.message, res.success ? 'success' : 'error');
    if (res.success) setTimeout(function () { window.location.href = location.pathname; }, 800);
  })
  .catch(function () {
    btn.disabled = false;
    btn.innerHTML = orig;
    adminSnackbar('Network error. Try again.', 'error');
  });
});

document.querySelectorAll('.js-delete-category').forEach(function (btn) {
  btn.addEventListener('click', function () {
    adminDeleteRow(btn, '<?= BASE_URL ?>admin/api/delete-category.php', function () {
      var badge = document.getElementById('catCountBadge');
      if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent, 10) - 1);
    });
  });
});

document.querySelectorAll('.js-toggle-cat').forEach(function (btn) {
  btn.addEventListener('click', function () {
    fetch('<?= BASE_URL ?>admin/api/toggle-category.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: parseInt(btn.dataset.id) })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (!res.success) { adminSnackbar('Update failed.', 'error'); return; }
      var active = res.is_active === 1;
      btn.className = 'badge ' + (active ? 'badge-green' : 'badge-red') + ' js-toggle-cat';
      btn.textContent = active ? 'Active' : 'Inactive';
      btn.dataset.active = res.is_active;
      adminSnackbar('Category ' + (active ? 'activated' : 'deactivated') + '.', 'success');
    })
    .catch(function () { adminSnackbar('Network error. Try again.', 'error'); });
  });
});
</script>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
