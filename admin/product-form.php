<?php
$isEdit  = isset($_GET['id']) && (int)$_GET['id'] > 0;
$_isAjax = $_SERVER['REQUEST_METHOD'] === 'POST' && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
if ($_isAjax) {
    require_once dirname(__DIR__) . '/includes/functions.php';
    require_once dirname(__DIR__) . '/includes/auth.php';
    if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Session expired.']); exit; }
} else {
    $pageTitle  = $isEdit ? 'Edit Product' : 'Add Product';
    $activePage = 'products';
    require_once __DIR__ . '/includes/layout.php';
    require_once dirname(__DIR__) . '/includes/functions.php';
}

$db = getDB();

// Load existing product for edit
$product = [];
$specs   = [];
if ($isEdit) {
    $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $product = $stmt->fetch();
    if (!$product) { flash('error','Product not found.'); redirect(BASE_URL.'admin/products.php'); }

    $specStmt = $db->prepare('SELECT * FROM product_specs WHERE product_id = ? ORDER BY sort_order');
    $specStmt->execute([$product['id']]);
    $specs = $specStmt->fetchAll();
}

$galleryImages = [];
if ($isEdit) {
    $gStmt = $db->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC');
    $gStmt->execute([$product['id']]);
    $galleryImages = $gStmt->fetchAll();
}

// Handle save 
if (isPost()) {
    $name     = post('name');
    $slug     = post('slug') ?: slugify($name);
    $catId    = (int)post('category_id');
    $brand    = post('brand');
    $sku      = post('sku') ?: null;
    $price    = (float)str_replace(',','',(post('price')));
    $oldPrice = post('old_price') ? (float)str_replace(',','',post('old_price')) : null;
    $shortDesc= post('short_description');
    $desc     = post('description');
    $stock    = (int)post('stock_qty');
    $inStock  = post('in_stock','1') === '1' ? 1 : 0;
    $badge    = post('badge');
    $rating   = (float)post('rating','0');
    $featured = post('is_featured','0') === '1' ? 1 : 0;
    $active   = post('is_active','1') === '1' ? 1 : 0;

    $errors = [];
    if (!$name)  $errors[] = 'Product name is required.';
    if (!$catId) $errors[] = 'Please select a category.';
    if ($price <= 0) $errors[] = 'Price must be greater than 0.';

    // Handle thumbnail upload
    $thumbnail = $product['thumbnail'] ?? null;
    if (!empty($_FILES['thumbnail']['name'])) {
        $uploaded = uploadImage($_FILES['thumbnail'], 'products');
        if ($uploaded) {
            // Remove old thumbnail
            if ($thumbnail && file_exists(ROOT_PATH . $thumbnail)) unlink(ROOT_PATH . $thumbnail);
            $thumbnail = $uploaded;
        } else {
            $errors[] = 'Image upload failed. Use JPG, PNG or WebP under 5MB.';
        }
    }

    if (!$errors) {
        try {
            if ($isEdit) {
                $db->prepare('UPDATE products SET name=?,slug=?,category_id=?,brand=?,sku=?,price=?,old_price=?,short_description=?,description=?,stock_qty=?,in_stock=?,badge=?,rating=?,is_featured=?,is_active=?,thumbnail=? WHERE id=?')
                   ->execute([$name,$slug,$catId,$brand,$sku,$price,$oldPrice,$shortDesc,$desc,$stock,$inStock,$badge?:null,$rating,$featured,$active,$thumbnail,$product['id']]);
                $productId = $product['id'];
            } else {
                $db->prepare('INSERT INTO products (name,slug,category_id,brand,sku,price,old_price,short_description,description,stock_qty,in_stock,badge,rating,is_featured,is_active,thumbnail) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
                   ->execute([$name,$slug,$catId,$brand,$sku,$price,$oldPrice,$shortDesc,$desc,$stock,$inStock,$badge?:null,$rating,$featured,$active,$thumbnail]);
                $productId = (int)$db->lastInsertId();
            }

            // Save specs
            $db->prepare('DELETE FROM product_specs WHERE product_id = ?')->execute([$productId]);
            $specKeys  = $_POST['spec_key']   ?? [];
            $specVals  = $_POST['spec_value'] ?? [];
            foreach ($specKeys as $i => $key) {
                $key = trim($key); $val = trim($specVals[$i] ?? '');
                if ($key && $val) {
                    $db->prepare('INSERT INTO product_specs (product_id,spec_key,spec_value,sort_order) VALUES (?,?,?,?)')
                       ->execute([$productId, $key, $val, $i]);
                }
            }

            // Handle gallery deletions
            foreach ((array)($_POST['delete_gallery'] ?? []) as $delId) {
                $delId = (int)$delId;
                if (!$delId) continue;
                $imgStmt = $db->prepare('SELECT image_path FROM product_images WHERE id=? AND product_id=?');
                $imgStmt->execute([$delId, $productId]);
                $imgRow = $imgStmt->fetch();
                if ($imgRow) {
                    if (file_exists(ROOT_PATH . $imgRow['image_path'])) unlink(ROOT_PATH . $imgRow['image_path']);
                    $db->prepare('DELETE FROM product_images WHERE id=?')->execute([$delId]);
                }
            }

            // Handle new gallery uploads
            if (!empty($_FILES['gallery']['name'][0])) {
                $maxStmt = $db->prepare('SELECT COALESCE(MAX(sort_order),0) FROM product_images WHERE product_id=?');
                $maxStmt->execute([$productId]);
                $nextSort = (int)$maxStmt->fetchColumn() + 1;
                for ($fi = 0; $fi < count($_FILES['gallery']['name']); $fi++) {
                    if ($_FILES['gallery']['error'][$fi] !== UPLOAD_ERR_OK) continue;
                    $gFile = [
                        'error'    => $_FILES['gallery']['error'][$fi],
                        'type'     => $_FILES['gallery']['type'][$fi],
                        'size'     => $_FILES['gallery']['size'][$fi],
                        'tmp_name' => $_FILES['gallery']['tmp_name'][$fi],
                        'name'     => $_FILES['gallery']['name'][$fi],
                    ];
                    $gPath = uploadImage($gFile, 'products');
                    if ($gPath) {
                        $db->prepare('INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?,?,?)')
                           ->execute([$productId, $gPath, $nextSort++]);
                    }
                }
            }

            if (isAjax()) jsonOk($isEdit ? 'Product updated successfully.' : 'Product added successfully.');
            flash('success', $isEdit ? 'Product updated successfully.' : 'Product added successfully.');
            redirect(BASE_URL . 'admin/products.php');

        } catch (PDOException $e) {
            $errors[] = str_contains($e->getMessage(),'slug') ? 'That slug is already in use.' : 'Database error: ' . $e->getMessage();
        }
    }

    if ($errors && isAjax()) {
        jsonErr(implode(' | ', $errors));
    }
}

$cats = $db->query('SELECT id, name FROM categories WHERE is_active=1 ORDER BY sort_order, name')->fetchAll();
function val(array $product, string $key, string $default = ''): string {
    return htmlspecialchars((string)($product[$key] ?? $default));
}
?>

<div class="page-header">
  <div>
    <h1><?= $isEdit ? 'Edit Product' : 'Add New Product' ?></h1>
    <p><?= $isEdit ? 'Update product details, specs and image.' : 'Fill in the details to add a new product.' ?></p>
  </div>
  <div class="ph-actions">
    <a href="products.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
</div>

<?php if (!empty($errors)): ?>
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:var(--r);padding:14px 20px;margin-bottom:20px">
  <div style="font-size:13px;font-weight:600;color:#f87171;margin-bottom:6px"><i class="fas fa-exclamation-circle"></i> Please fix the following:</div>
  <?php foreach ($errors as $e): ?>
    <div style="font-size:13px;color:#f87171;margin-top:4px">• <?= htmlspecialchars($e) ?></div>
  <?php endforeach ?>
</div>
<?php endif ?>

<form method="POST" enctype="multipart/form-data" id="productForm">
<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

  <!-- LEFT: Main fields -->
  <div style="display:flex;flex-direction:column;gap:20px">

    <!-- Basic Info -->
    <div class="card">
      <div class="card-title"><i class="fas fa-info-circle" style="color:var(--primary)"></i> Basic Information</div>
      <div class="form-grid form-grid-2">
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Product Name <span style="color:var(--red)">*</span></label>
          <input type="text" name="name" class="form-input" id="prodName" required
            placeholder="e.g. Intel Core i5-12400F"
            value="<?= val($product,'name') ?>">
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="form-input" id="prodSlug"
            placeholder="auto-generated"
            value="<?= val($product,'slug') ?>">
          <span class="form-hint">URL-friendly identifier. Leave blank to auto-generate.</span>
        </div>
        <div class="form-group">
          <label class="form-label">Category <span style="color:var(--red)">*</span></label>
          <select name="category_id" class="form-select" required>
            <option value="">Select category</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($product['category_id']??'')==$c['id']?'selected':'' ?>>
                <?= htmlspecialchars($c['name']) ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Brand</label>
          <input type="text" name="brand" class="form-input" placeholder="e.g. Intel, AMD, ASUS"
            value="<?= val($product,'brand') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">SKU</label>
          <input type="text" name="sku" class="form-input" placeholder="e.g. INT-I5-12400F"
            value="<?= val($product,'sku') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Badge</label>
          <select name="badge" class="form-select">
            <option value="">None</option>
            <option value="NEW"  <?= ($product['badge']??'')==='NEW' ?'selected':'' ?>>NEW</option>
            <option value="HOT"  <?= ($product['badge']??'')==='HOT' ?'selected':'' ?>>HOT</option>
            <option value="SALE" <?= ($product['badge']??'')==='SALE'?'selected':'' ?>>SALE</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Pricing & Stock -->
    <div class="card">
      <div class="card-title"><i class="fas fa-tags" style="color:var(--primary)"></i> Pricing &amp; Stock</div>
      <div class="form-grid form-grid-2">
        <div class="form-group">
          <label class="form-label">Price (Rs.) <span style="color:var(--red)">*</span></label>
          <input type="number" name="price" class="form-input" step="0.01" min="0" required
            placeholder="0.00" value="<?= val($product,'price','') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Old Price (Rs.) <span style="color:var(--text-dim);font-weight:400;text-transform:none;font-size:11px">for strikethrough</span></label>
          <input type="number" name="old_price" class="form-input" step="0.01" min="0"
            placeholder="Leave blank if no discount"
            value="<?= val($product,'old_price','') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Stock Quantity</label>
          <input type="number" name="stock_qty" class="form-input" min="0"
            value="<?= val($product,'stock_qty','0') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">In Stock</label>
          <select name="in_stock" class="form-select">
            <option value="1" <?= ($product['in_stock']??1)==1?'selected':'' ?>>In Stock</option>
            <option value="0" <?= ($product['in_stock']??1)==0?'selected':'' ?>>Out of Stock</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Rating (0–5)</label>
          <input type="number" name="rating" class="form-input" min="0" max="5" step="0.1"
            value="<?= val($product,'rating','0') ?>">
        </div>
      </div>
    </div>

    <!-- Description -->
    <div class="card">
      <div class="card-title"><i class="fas fa-align-left" style="color:var(--primary)"></i> Description</div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Short Description <span style="color:var(--text-dim);font-weight:400;text-transform:none;font-size:11px">shown on cards</span></label>
          <input type="text" name="short_description" class="form-input"
            placeholder="One-line summary of the product"
            value="<?= val($product,'short_description') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Full Description</label>
          <textarea name="description" class="form-textarea" rows="6"
            placeholder="Detailed product description..."><?= val($product,'description') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Specs -->
    <div class="card">
      <div class="card-title">
        <span><i class="fas fa-list-ul" style="color:var(--primary)"></i> Specifications</span>
        <button type="button" class="btn btn-ghost btn-sm" id="addSpecBtn"><i class="fas fa-plus"></i> Add Row</button>
      </div>
      <div id="specsContainer">
        <?php
        $defaultSpecs = $specs ?: [['spec_key'=>'','spec_value'=>'']];
        foreach ($defaultSpecs as $i => $s): ?>
        <div class="spec-row" style="display:grid;grid-template-columns:1fr 1fr 36px;gap:8px;margin-bottom:8px">
          <input type="text" name="spec_key[]" class="form-input" placeholder="e.g. Socket"
            value="<?= htmlspecialchars($s['spec_key']) ?>">
          <input type="text" name="spec_value[]" class="form-input" placeholder="e.g. LGA1700"
            value="<?= htmlspecialchars($s['spec_value']) ?>">
          <button type="button" class="btn btn-danger btn-icon remove-spec" style="width:36px;height:38px"><i class="fas fa-times"></i></button>
        </div>
        <?php endforeach ?>
      </div>
    </div>

  </div>

  <!-- RIGHT: Image + Settings -->
  <div style="display:flex;flex-direction:column;gap:20px;position:sticky;top:80px">

    <!-- Publish settings -->
    <div class="card">
      <div class="card-title"><i class="fas fa-cog" style="color:var(--primary)"></i> Settings</div>
      <div class="form-grid" style="gap:14px">
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="is_active" class="form-select">
            <option value="1" <?= ($product['is_active']??1)==1?'selected':'' ?>>Active (visible)</option>
            <option value="0" <?= ($product['is_active']??1)==0?'selected':'' ?>>Inactive (hidden)</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Featured</label>
          <select name="is_featured" class="form-select">
            <option value="0" <?= ($product['is_featured']??0)==0?'selected':'' ?>>Not Featured</option>
            <option value="1" <?= ($product['is_featured']??0)==1?'selected':'' ?>>Featured</option>
          </select>
        </div>
      </div>
      <div style="margin-top:20px;display:flex;flex-direction:column;gap:10px">
        <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center">
          <i class="fas <?= $isEdit?'fa-save':'fa-plus' ?>"></i>
          <?= $isEdit ? 'Save Changes' : 'Add Product' ?>
        </button>
        <a href="products.php" class="btn btn-ghost" style="width:100%;justify-content:center">
          <i class="fas fa-times"></i> Cancel
        </a>
      </div>
    </div>

    <!-- Thumbnail -->
    <div class="card">
      <div class="card-title"><i class="fas fa-image" style="color:var(--primary)"></i> Thumbnail</div>

      <?php $thumb = $product['thumbnail'] ?? ''; $hasThumb = $thumb && file_exists(ROOT_PATH . $thumb); ?>

      <!-- Current image -->
      <div id="imgPreviewWrap" style="<?= $hasThumb ? '' : 'display:none' ?>;margin-bottom:14px">
        <img id="imgPreview"
          src="<?= $hasThumb ? BASE_URL . htmlspecialchars($thumb) : '' ?>"
          alt="Thumbnail"
          style="width:100%;height:180px;object-fit:cover;border-radius:var(--r);border:1px solid var(--border)">
      </div>

      <!-- No image placeholder -->
      <div id="imgPlaceholder" style="<?= $hasThumb ? 'display:none' : '' ?>;width:100%;height:160px;border:2px dashed var(--border);border-radius:var(--r);display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-dim);margin-bottom:14px;cursor:pointer">
        <i class="fas fa-cloud-upload-alt" style="font-size:28px;margin-bottom:8px"></i>
        <span style="font-size:12.5px">Click to upload image</span>
        <span style="font-size:11px;margin-top:4px">JPG, PNG, WebP · Max 5MB</span>
      </div>

      <input type="file" name="thumbnail" id="thumbnailInput" accept="image/*" style="display:none">
      <button type="button" class="btn btn-ghost" style="width:100%;justify-content:center" onclick="document.getElementById('thumbnailInput').click()">
        <i class="fas fa-upload"></i> <?= $thumb ? 'Change Image' : 'Upload Image' ?>
      </button>
    </div>

    <!-- Gallery Images -->
    <div class="card">
      <div class="card-title"><i class="fas fa-images" style="color:var(--primary)"></i> Gallery Images</div>

      <!-- Existing images -->
      <?php if (!empty($galleryImages)): ?>
      <div id="existingGallery" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px">
        <?php foreach ($galleryImages as $gi): ?>
        <div class="gal-item" data-id="<?= $gi['id'] ?>" style="position:relative;border-radius:var(--r);overflow:hidden;aspect-ratio:1;background:#111">
          <img src="<?= BASE_URL . htmlspecialchars($gi['image_path']) ?>" alt="Gallery"
            style="width:100%;height:100%;object-fit:cover;display:block">
          <button type="button" class="gal-del-btn" data-id="<?= $gi['id'] ?>"
            style="position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:rgba(239,68,68,.9);border:none;color:#fff;cursor:pointer;font-size:10px;display:flex;align-items:center;justify-content:center;line-height:1">
            <i class="fas fa-times"></i>
          </button>
          <div class="gal-del-overlay" style="display:none;position:absolute;inset:0;background:rgba(239,68,68,.35)"></div>
        </div>
        <?php endforeach ?>
      </div>
      <?php else: ?>
      <div id="existingGallery" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px"></div>
      <?php endif ?>

      <!-- New image previews -->
      <div id="galleryPreview" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px"></div>

      <!-- Hidden delete inputs (populated by JS) -->
      <div id="galleryDeleteInputs"></div>

      <input type="file" name="gallery[]" id="galleryInput" accept="image/*" multiple style="display:none">
      <button type="button" class="btn btn-ghost" style="width:100%;justify-content:center" onclick="document.getElementById('galleryInput').click()">
        <i class="fas fa-plus"></i> Add Gallery Images
      </button>
      <span class="form-hint" style="margin-top:6px;display:block;text-align:center">Select multiple images at once · JPG, PNG, WebP · Max 5MB each</span>
    </div>

  </div>
</div>
</form>

<script>
// Auto slug
const nameInput = document.getElementById('prodName');
const slugInput = document.getElementById('prodSlug');
<?php if (!$isEdit): ?>
nameInput.addEventListener('input', () => {
  slugInput.value = nameInput.value.toLowerCase()
    .replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-').replace(/^-|-$/g,'');
});
<?php endif ?>

// Specs - add row
document.getElementById('addSpecBtn').addEventListener('click', () => {
  const row = document.createElement('div');
  row.className = 'spec-row';
  row.style.cssText = 'display:grid;grid-template-columns:1fr 1fr 36px;gap:8px;margin-bottom:8px';
  row.innerHTML = `
    <input type="text" name="spec_key[]" class="form-input" placeholder="e.g. Socket">
    <input type="text" name="spec_value[]" class="form-input" placeholder="e.g. LGA1700">
    <button type="button" class="btn btn-danger btn-icon remove-spec" style="width:36px;height:38px"><i class="fas fa-times"></i></button>`;
  document.getElementById('specsContainer').appendChild(row);
});

// Remove spec row
document.getElementById('specsContainer').addEventListener('click', e => {
  const btn = e.target.closest('.remove-spec');
  if (btn) {
    const rows = document.querySelectorAll('.spec-row');
    if (rows.length > 1) btn.closest('.spec-row').remove();
    else { btn.closest('.spec-row').querySelectorAll('input').forEach(i => i.value = ''); }
  }
});

// Image preview
const thumbInput      = document.getElementById('thumbnailInput');
const imgPreview      = document.getElementById('imgPreview');
const imgPreviewWrap  = document.getElementById('imgPreviewWrap');
const imgPlaceholder  = document.getElementById('imgPlaceholder');

thumbInput.addEventListener('change', () => {
  const file = thumbInput.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    imgPreview.src = e.target.result;
    imgPreviewWrap.style.display = '';
    imgPlaceholder.style.display = 'none';
  };
  reader.readAsDataURL(file);
});

// Click placeholder to upload
imgPlaceholder.addEventListener('click', () => thumbInput.click());

// Gallery - preview new uploads
document.getElementById('galleryInput').addEventListener('change', function () {
  const preview = document.getElementById('galleryPreview');
  preview.innerHTML = '';
  Array.from(this.files).forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      const div = document.createElement('div');
      div.style.cssText = 'position:relative;border-radius:var(--r);overflow:hidden;aspect-ratio:1;background:#111;border:1px solid var(--border)';
      div.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;display:block">
        <div style="position:absolute;inset:0;background:rgba(212,146,10,.12);display:flex;align-items:flex-end;padding:4px">
          <span style="font-size:9px;color:#fff;background:rgba(0,0,0,.6);border-radius:3px;padding:2px 4px;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${file.name}</span>
        </div>`;
      preview.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
});

// AJAX form submit (supports file uploads via FormData)
document.getElementById('productForm').addEventListener('submit', function (e) {
  e.preventDefault();
  var form = this;
  var btn  = form.querySelector('[type="submit"]');
  var orig = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

  fetch(location.href, {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: new FormData(form)
  })
  .then(function (r) { return r.json(); })
  .then(function (res) {
    btn.disabled = false;
    btn.innerHTML = orig;
    if (res.success) {
      adminSnackbar(res.message, 'success');
      setTimeout(function () { window.location.href = '<?= BASE_URL ?>admin/products.php'; }, 1000);
    } else {
      adminSnackbar(res.message, 'error');
      var errDiv = document.getElementById('ajaxErrors');
      if (!errDiv) {
        errDiv = document.createElement('div');
        errDiv.id = 'ajaxErrors';
        errDiv.style.cssText = 'background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:14px 20px;margin-bottom:20px';
        form.parentNode.insertBefore(errDiv, form);
      }
      errDiv.innerHTML = '<div style="font-size:13px;font-weight:600;color:#f87171;margin-bottom:4px"><i class="fas fa-exclamation-circle"></i> Please fix the following:</div>' +
        res.message.split(' | ').map(function (m) { return '<div style="font-size:13px;color:#f87171;margin-top:4px">• ' + m + '</div>'; }).join('');
    }
  })
  .catch(function () {
    btn.disabled = false;
    btn.innerHTML = orig;
    adminSnackbar('Network error. Try again.', 'error');
  });
});

// Gallery - delete existing images
document.getElementById('existingGallery').addEventListener('click', function (e) {
  const btn = e.target.closest('.gal-del-btn');
  if (!btn) return;
  const id = btn.dataset.id;
  const item = btn.closest('.gal-item');
  const deleteInputs = document.getElementById('galleryDeleteInputs');
  const existing = deleteInputs.querySelector(`input[value="${id}"]`);
  if (existing) {
    // Toggle off
    existing.remove();
    item.querySelector('.gal-del-overlay').style.display = 'none';
    btn.style.background = 'rgba(239,68,68,.9)';
  } else {
    // Mark for deletion
    const inp = document.createElement('input');
    inp.type = 'hidden';
    inp.name = 'delete_gallery[]';
    inp.value = id;
    deleteInputs.appendChild(inp);
    item.querySelector('.gal-del-overlay').style.display = '';
    btn.style.background = 'rgba(239,68,68,1)';
  }
});
</script>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
