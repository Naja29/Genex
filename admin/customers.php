<?php
$pageTitle  = 'Customers';
$activePage = 'customers';
require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$db = getDB();

// Handle DELETE 
if (isPost() && post('action') === 'delete') {
    $id = (int)post('id');
    if ($id) {
        $db->prepare('DELETE FROM customers WHERE id = ?')->execute([$id]);
        flash('success', 'Customer deleted.');
    }
    redirect(BASE_URL . 'admin/customers.php?' . http_build_query(array_filter([
        'search' => get('search'), 'page' => get('page')
    ])));
}

// Filters 
$search  = get('search');
$page    = max(1, (int)get('page', '1'));
$perPage = 20;

$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]  = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
    $s = "%$search%";
    $params = [$s, $s, $s];
}
$whereStr = implode(' AND ', $where);

$total = $db->prepare("SELECT COUNT(*) FROM customers WHERE $whereStr");
$total->execute($params);
$pg = paginate((int)$total->fetchColumn(), $perPage, $page);

$stmt = $db->prepare("
    SELECT c.*,
           COUNT(o.id) AS order_count,
           COALESCE(SUM(o.total),0) AS total_spent,
           MAX(o.created_at) AS last_order_at
    FROM customers c
    LEFT JOIN orders o ON o.customer_phone = c.phone
    WHERE $whereStr
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT {$pg['per_page']} OFFSET {$pg['offset']}
");
$stmt->execute($params);
$customers = $stmt->fetchAll();

// Summary
$totalCustomers  = $db->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$newThisMonth    = $db->query("SELECT COUNT(*) FROM customers WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();
?>

<div class="page-header">
  <div>
    <h1>Customers</h1>
    <p><?= number_format($pg['total']) ?> customer<?= $pg['total']!=1?'s':'' ?> registered</p>
  </div>
</div>

<!-- Summary strip -->
<div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
  <span class="stat-strip">
    <i class="fas fa-users" style="color:var(--primary)"></i> Total <strong><?= number_format($totalCustomers) ?></strong>
  </span>
  <span class="stat-strip">
    <i class="fas fa-user-plus" style="color:var(--green)"></i> This Month <strong><?= number_format($newThisMonth) ?></strong>
  </span>
</div>

<!-- Search -->
<div class="card" style="padding:14px 20px;margin-bottom:20px">
  <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:200px;background:var(--bg-3);border:1px solid var(--border);border-radius:var(--r);padding:0 14px">
      <i class="fas fa-search" style="color:var(--text-muted);font-size:13px"></i>
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
        placeholder="Search by name, email or phone..."
        style="background:none;border:none;outline:none;color:var(--text);font-family:'Poppins',sans-serif;font-size:13px;padding:10px 0;width:100%">
    </div>
    <button type="submit" class="btn btn-ghost"><i class="fas fa-search"></i> Search</button>
    <?php if ($search): ?>
      <a href="customers.php" class="btn btn-ghost"><i class="fas fa-times"></i> Clear</a>
    <?php endif ?>
  </form>
</div>

<!-- Table -->
<div class="card" style="padding:0;overflow:hidden">
  <?php if ($customers): ?>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th>Customer</th>
          <th>Phone</th>
          <th style="text-align:center">Orders</th>
          <th style="text-align:right">Total Spent</th>
          <th>Last Order</th>
          <th>Registered</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($customers as $c): ?>
        <tr>
          <td style="color:var(--text-dim);font-size:12px"><?= $c['id'] ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:34px;height:34px;border-radius:50%;background:rgba(212,146,10,.1);display:flex;align-items:center;justify-content:center;color:var(--primary);font-weight:700;font-size:13px;flex-shrink:0">
                <?= strtoupper(substr($c['name'],0,1)) ?>
              </div>
              <div>
                <div style="font-weight:600;color:var(--text);font-size:13px"><?= htmlspecialchars($c['name']) ?></div>
                <?php if ($c['email']): ?>
                  <div style="font-size:11.5px;color:var(--text-muted);margin-top:1px">
                    <a href="mailto:<?= htmlspecialchars($c['email']) ?>" style="color:var(--text-muted)"><?= htmlspecialchars($c['email']) ?></a>
                  </div>
                <?php endif ?>
              </div>
            </div>
          </td>
          <td>
            <a href="tel:<?= htmlspecialchars($c['phone']) ?>" style="color:var(--primary);font-size:13px">
              <?= htmlspecialchars($c['phone']) ?>
            </a>
          </td>
          <td style="text-align:center">
            <a href="orders.php?search=<?= urlencode($c['phone']) ?>"
               class="badge <?= $c['order_count'] > 0 ? 'badge-blue' : 'badge-gray' ?>">
              <?= $c['order_count'] ?>
            </a>
          </td>
          <td style="text-align:right;font-weight:700;color:var(--primary)">
            <?= fmtPrice((float)$c['total_spent']) ?>
          </td>
          <td style="font-size:12px;color:var(--text-muted)">
            <?= $c['last_order_at'] ? timeAgo($c['last_order_at']) : '<span style="color:var(--text-dim)">-</span>' ?>
          </td>
          <td style="font-size:12px;color:var(--text-muted)"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
          <td style="text-align:right">
            <div style="display:flex;gap:6px;justify-content:flex-end">
              <a href="orders.php?search=<?= urlencode($c['phone']) ?>"
                 class="btn btn-ghost btn-icon btn-sm" title="View Orders">
                <i class="fas fa-receipt"></i>
              </a>
              <?php if ($c['customer_phone'] ?? $c['phone']): ?>
              <a href="https://wa.me/<?= preg_replace('/[^0-9]/','', $c['phone']) ?>"
                 target="_blank" rel="noopener"
                 class="btn btn-ghost btn-icon btn-sm" title="WhatsApp"
                 style="color:#25d366">
                <i class="fab fa-whatsapp"></i>
              </a>
              <?php endif ?>
              <button type="button" class="btn btn-danger btn-icon btn-sm js-delete-customer" title="Delete"
                data-id="<?= $c['id'] ?>">
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
      $qs = http_build_query(array_filter(['search' => $search]));
      for ($i = 1; $i <= $pg['pages']; $i++): ?>
        <a href="customers.php?<?= $qs ?>&page=<?= $i ?>"
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
      <i class="fas fa-users"></i>
      <h3>No customers found</h3>
      <p><?= $search ? 'Try adjusting your search.' : 'Customers will appear here once orders are placed.' ?></p>
    </div>
  <?php endif ?>
</div>

<style>
.stat-strip{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:var(--r);background:var(--bg-2);border:1px solid var(--border);font-size:13px;color:var(--text-muted)}
.stat-strip strong{color:var(--text);margin-left:2px}
</style>

<script>
document.querySelectorAll('.js-delete-customer').forEach(function (btn) {
  btn.addEventListener('click', function () {
    adminDeleteRow(btn, '<?= BASE_URL ?>admin/api/delete-customer.php');
  });
});
</script>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
