<?php
$pageTitle  = 'Orders';
$activePage = 'orders';
require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$db = getDB();


// Filters 
$search    = get('search');
$statusF   = get('status');
$page      = max(1, (int)get('page', '1'));
$perPage   = 15;

$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]  = '(order_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)';
    $s = "%$search%";
    $params = [$s, $s, $s];
}
if ($statusF) {
    $where[]  = 'status = ?';
    $params[] = $statusF;
}
$whereStr = implode(' AND ', $where);

$total = $db->prepare("SELECT COUNT(*) FROM orders WHERE $whereStr");
$total->execute($params);
$pg = paginate((int)$total->fetchColumn(), $perPage, $page);

$orders = $db->prepare("SELECT * FROM orders WHERE $whereStr ORDER BY created_at DESC LIMIT {$pg['per_page']} OFFSET {$pg['offset']}");
$orders->execute($params);
$orders = $orders->fetchAll();

// Status counts
$statusCounts = [];
$rows = $db->query("SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status")->fetchAll();
foreach ($rows as $r) $statusCounts[$r['status']] = $r['cnt'];
$totalOrders = array_sum($statusCounts);

$statuses = [
    ''           => ['All',        $totalOrders,                    'badge-gray'],
    'pending'    => ['Pending',    $statusCounts['pending']    ?? 0, 'badge-yellow'],
    'confirmed'  => ['Confirmed',  $statusCounts['confirmed']  ?? 0, 'badge-blue'],
    'processing' => ['Processing', $statusCounts['processing'] ?? 0, 'badge-purple'],
    'dispatched' => ['Dispatched', $statusCounts['dispatched'] ?? 0, 'badge-cyan'],
    'delivered'  => ['Delivered',  $statusCounts['delivered']  ?? 0, 'badge-green'],
    'cancelled'  => ['Cancelled',  $statusCounts['cancelled']  ?? 0, 'badge-red'],
];

$sourceBadge = ['whatsapp'=>'badge-green','website'=>'badge-blue','instore'=>'badge-yellow'];
?>

<div class="page-header">
  <div>
    <h1>Orders</h1>
    <p><?= number_format($pg['total']) ?> order<?= $pg['total']!=1?'s':'' ?> found</p>
  </div>
</div>

<!-- Status tabs -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
  <?php foreach ($statuses as $key => [$label, $count, $badgeClass]): ?>
    <a href="orders.php?status=<?= $key ?><?= $search?'&search='.urlencode($search):'' ?>"
       style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:var(--r);font-size:13px;font-weight:500;
              background:<?= $statusF===$key?'rgba(212,146,10,.1)':'var(--bg-2)' ?>;
              border:1px solid <?= $statusF===$key?'rgba(212,146,10,.35)':'var(--border)' ?>;
              color:<?= $statusF===$key?'var(--primary)':'var(--text-muted)' ?>">
      <?= $label ?>
      <span class="badge <?= $badgeClass ?>" style="font-size:10.5px;padding:2px 7px"><?= $count ?></span>
    </a>
  <?php endforeach ?>
</div>

<!-- Search -->
<div class="card" style="padding:14px 20px;margin-bottom:20px">
  <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
    <?php if ($statusF): ?><input type="hidden" name="status" value="<?= htmlspecialchars($statusF) ?>"><?php endif ?>
    <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:200px;background:var(--bg-3);border:1px solid var(--border);border-radius:var(--r);padding:0 14px">
      <i class="fas fa-search" style="color:var(--text-muted);font-size:13px"></i>
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
        placeholder="Search by order number, customer name or phone..."
        style="background:none;border:none;outline:none;color:var(--text);font-family:'Poppins',sans-serif;font-size:13px;padding:10px 0;width:100%">
    </div>
    <button type="submit" class="btn btn-ghost"><i class="fas fa-search"></i> Search</button>
    <?php if ($search): ?>
      <a href="orders.php<?= $statusF?'?status='.$statusF:'' ?>" class="btn btn-ghost"><i class="fas fa-times"></i> Clear</a>
    <?php endif ?>
  </form>
</div>

<!-- Orders table -->
<div class="card" style="padding:0;overflow:hidden">
  <?php if ($orders): ?>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Order #</th>
          <th>Customer</th>
          <th>Items</th>
          <th>Total</th>
          <th>Source</th>
          <th>Date</th>
          <th>Status</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($orders as $o):
        $items = json_decode($o['items_json'], true) ?? [];
        $itemCount = is_array($items) ? count($items) : 0;
      ?>
        <tr>
          <td>
            <a href="order-detail.php?id=<?= $o['id'] ?>" style="font-weight:700;color:var(--primary);font-size:13px">
              <?= htmlspecialchars($o['order_number']) ?>
            </a>
          </td>
          <td>
            <div style="font-weight:500;font-size:13px"><?= htmlspecialchars($o['customer_name']) ?></div>
            <div style="font-size:11.5px;color:var(--text-muted);margin-top:1px">
              <a href="tel:<?= htmlspecialchars($o['customer_phone']) ?>" style="color:var(--text-muted)"><?= htmlspecialchars($o['customer_phone']) ?></a>
            </div>
          </td>
          <td>
            <span style="font-size:13px"><?= $itemCount ?> item<?= $itemCount!=1?'s':'' ?></span>
            <?php if ($items && isset($items[0]['name'])): ?>
              <div style="font-size:11.5px;color:var(--text-muted);margin-top:1px;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                <?= htmlspecialchars($items[0]['name']) ?><?= $itemCount>1?' +'.($itemCount-1).' more':'' ?>
              </div>
            <?php endif ?>
          </td>
          <td><strong style="color:var(--primary)"><?= fmtPrice((float)$o['total']) ?></strong></td>
          <td><span class="badge <?= $sourceBadge[$o['source']] ?? 'badge-gray' ?>"><?= ucfirst($o['source']) ?></span></td>
          <td style="font-size:12px;color:var(--text-muted)"><?= timeAgo($o['created_at']) ?><br><span style="font-size:11px"><?= date('d M Y', strtotime($o['created_at'])) ?></span></td>
          <td>
            <select class="form-select js-status-select" style="font-size:12px;padding:5px 10px;width:130px"
              data-id="<?= $o['id'] ?>" data-num="<?= htmlspecialchars($o['order_number']) ?>">
              <?php foreach (['pending','confirmed','processing','dispatched','delivered','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach ?>
            </select>
          </td>
          <td style="text-align:right">
            <a href="order-detail.php?id=<?= $o['id'] ?>" class="btn btn-ghost btn-icon btn-sm" title="View Details">
              <i class="fas fa-eye"></i>
            </a>
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
      $qs = http_build_query(array_filter(['search'=>$search,'status'=>$statusF]));
      for ($i=1; $i<=$pg['pages']; $i++): ?>
        <a href="orders.php?<?= $qs ?>&page=<?= $i ?>"
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
      <i class="fas fa-receipt"></i>
      <h3>No orders found</h3>
      <p><?= $search||$statusF ? 'Try adjusting your search or filter.' : 'Orders will appear here once customers place them.' ?></p>
    </div>
  <?php endif ?>
</div>

<script>
document.querySelectorAll('.js-status-select').forEach(function (sel) {
  sel.addEventListener('change', function () {
    var id     = sel.dataset.id;
    var num    = sel.dataset.num;
    var status = sel.value;
    var prev   = sel.querySelector('[data-prev]') ? sel.querySelector('[data-prev]').value : null;

    fetch('<?= BASE_URL ?>admin/api/update-order-status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: parseInt(id), status: status })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (res.success) {
        adminSnackbar('Order ' + num + ' → ' + status.charAt(0).toUpperCase() + status.slice(1), 'success');
      } else {
        adminSnackbar(res.error || 'Update failed.', 'error');
      }
    })
    .catch(function () { adminSnackbar('Network error. Try again.', 'error'); });
  });
});
</script>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
