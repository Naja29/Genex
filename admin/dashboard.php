<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$db = getDB();

// Stats 
$totalProducts  = $db->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
$totalOrders    = $db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalCustomers = $db->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$totalRevenue   = $db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status NOT IN ('cancelled')")->fetchColumn();

$ordersToday    = $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$ordersThisMonth= $db->query("SELECT COUNT(*) FROM orders WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();
$revenueMonth   = $db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status NOT IN ('cancelled') AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();
$pendingOrders  = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// Recent orders 
$recentOrders = $db->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 8')->fetchAll();

// Low stock products 
$lowStock = $db->query('SELECT p.id, p.name, p.stock_qty, c.name AS cat FROM products p JOIN categories c ON p.category_id = c.id WHERE p.stock_qty <= 5 AND p.is_active = 1 ORDER BY p.stock_qty ASC LIMIT 6')->fetchAll();

// Category breakdown 
$catStats = $db->query('SELECT c.name, COUNT(p.id) AS total FROM categories c LEFT JOIN products p ON p.category_id = c.id AND p.is_active=1 GROUP BY c.id ORDER BY total DESC LIMIT 6')->fetchAll();

// Status map 
$statusBadge = [
  'pending'    => 'badge-yellow',
  'confirmed'  => 'badge-blue',
  'processing' => 'badge-purple',
  'dispatched' => 'badge-cyan',
  'delivered'  => 'badge-green',
  'cancelled'  => 'badge-red',
];
?>

<!-- Page header -->
<div class="page-header">
  <div>
    <h1>Dashboard</h1>
    <p>Welcome back, <?= htmlspecialchars(adminName()) ?>. Here's what's happening today.</p>
  </div>
  <div class="ph-actions">
    <a href="<?= BASE_URL ?>admin/products.php?action=add" class="btn btn-gold">
      <i class="fas fa-plus"></i> Add Product
    </a>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-ico" style="background:rgba(212,146,10,.12);color:#d4920a"><i class="fas fa-box-open"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= number_format($totalProducts) ?></div>
      <div class="stat-label">Total Products</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-ico" style="background:rgba(59,130,246,.12);color:#3b82f6"><i class="fas fa-receipt"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= number_format($totalOrders) ?></div>
      <div class="stat-label">Total Orders</div>
      <div class="stat-change up"><i class="fas fa-arrow-up"></i> <?= $ordersToday ?> today</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-ico" style="background:rgba(16,185,129,.12);color:#10b981"><i class="fas fa-users"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= number_format($totalCustomers) ?></div>
      <div class="stat-label">Customers</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-ico" style="background:rgba(139,92,246,.12);color:#8b5cf6"><i class="fas fa-chart-line"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= fmtPrice((float)$revenueMonth) ?></div>
      <div class="stat-label">Revenue This Month</div>
      <div class="stat-change up"><i class="fas fa-arrow-up"></i> <?= $ordersThisMonth ?> orders</div>
    </div>
  </div>
</div>

<!-- Main grid -->
<div class="dash-grid">

  <!-- Recent orders -->
  <div class="card">
    <div class="card-title">
      Recent Orders
      <a href="<?= BASE_URL ?>admin/orders.php">View all &rarr;</a>
    </div>
    <?php if ($recentOrders): ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($recentOrders as $o): ?>
          <tr>
            <td><strong style="color:var(--primary)"><?= htmlspecialchars($o['order_number']) ?></strong></td>
            <td>
              <?= htmlspecialchars($o['customer_name']) ?>
              <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($o['customer_phone']) ?></div>
            </td>
            <td><strong><?= fmtPrice((float)$o['total']) ?></strong></td>
            <td><span class="badge <?= $statusBadge[$o['status']] ?? 'badge-gray' ?>"><?= ucfirst($o['status']) ?></span></td>
            <td style="color:var(--text-muted);font-size:12px"><?= timeAgo($o['created_at']) ?></td>
          </tr>
        <?php endforeach ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <h3>No orders yet</h3>
        <p>Orders will appear here when customers place them.</p>
      </div>
    <?php endif ?>
  </div>

  <!-- Right column -->
  <div style="display:flex;flex-direction:column;gap:20px">

    <!-- Pending orders alert -->
    <?php if ($pendingOrders > 0): ?>
    <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:var(--r-lg);padding:18px 20px;display:flex;align-items:center;gap:14px">
      <div style="width:40px;height:40px;border-radius:50%;background:rgba(245,158,11,.15);display:flex;align-items:center;justify-content:center;color:#f59e0b;font-size:18px;flex-shrink:0">
        <i class="fas fa-clock"></i>
      </div>
      <div style="flex:1">
        <strong style="color:#f59e0b;font-size:14px"><?= $pendingOrders ?> Pending Order<?= $pendingOrders>1?'s':'' ?></strong>
        <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Require your attention</p>
      </div>
      <a href="<?= BASE_URL ?>admin/orders.php?status=pending" class="btn btn-sm" style="background:rgba(245,158,11,.15);color:#f59e0b;border:1px solid rgba(245,158,11,.3)">Review</a>
    </div>
    <?php endif ?>

    <!-- Quick actions -->
    <div class="card">
      <div class="card-title">Quick Actions</div>
      <div class="quick-actions">
        <a href="<?= BASE_URL ?>admin/products.php?action=add" class="qa-btn">
          <i class="fas fa-plus-circle"></i> Add Product
        </a>
        <a href="<?= BASE_URL ?>admin/orders.php" class="qa-btn">
          <i class="fas fa-receipt"></i> View Orders
        </a>
        <a href="<?= BASE_URL ?>admin/categories.php" class="qa-btn">
          <i class="fas fa-tags"></i> Categories
        </a>
        <a href="<?= BASE_URL ?>admin/settings.php" class="qa-btn">
          <i class="fas fa-cog"></i> Settings
        </a>
      </div>
    </div>

    <!-- Low stock -->
    <?php if ($lowStock): ?>
    <div class="card">
      <div class="card-title">
        Low Stock
        <a href="<?= BASE_URL ?>admin/products.php?filter=low_stock">View all &rarr;</a>
      </div>
      <?php foreach ($lowStock as $p):
        $pct = min(100, ($p['stock_qty'] / 10) * 100);
        $cls = $p['stock_qty'] <= 2 ? 'critical' : ($p['stock_qty'] <= 4 ? 'low' : '');
      ?>
        <div style="margin-bottom:14px">
          <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:4px">
            <span style="font-size:12.5px;color:var(--text-2)"><?= htmlspecialchars($p['name']) ?></span>
            <span style="font-size:12px;font-weight:700;color:<?= $p['stock_qty']<=2?'var(--red)':'var(--yellow)' ?>"><?= $p['stock_qty'] ?> left</span>
          </div>
          <div class="stock-bar">
            <div class="stock-bar-fill <?= $cls ?>" style="width:<?= max(4, $pct) ?>%"></div>
          </div>
        </div>
      <?php endforeach ?>
    </div>
    <?php endif ?>

    <!-- Categories breakdown -->
    <div class="card">
      <div class="card-title">Products by Category</div>
      <?php foreach ($catStats as $c): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;font-size:13px">
          <span style="color:var(--text-2)"><?= htmlspecialchars($c['name']) ?></span>
          <span class="badge badge-gray"><?= $c['total'] ?></span>
        </div>
      <?php endforeach ?>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
