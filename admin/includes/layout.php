<?php

require_once dirname(__DIR__, 2) . '/includes/auth.php';
requireLogin();

$flash   = getFlash();
$title   = $pageTitle ?? 'Admin';
$active  = $activePage ?? '';
$name    = adminName();
$uname   = adminUsername();

$_db = getDB();
$pendingOrders  = (int)$_db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$latestOrderId  = (int)$_db->query("SELECT COALESCE(MAX(id),0) FROM orders")->fetchColumn();
$unreadMessages = (int)$_db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?> | Genex Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>admin/assets/css/admin.css">
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <img src="<?= BASE_URL ?>images/logo.jpg" alt="Genex" class="sl-logo-img">
    <span class="sl-sub">Admin Panel</span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Main</div>
    <a href="<?= BASE_URL ?>admin/dashboard.php"  class="nav-item <?= $active==='dashboard'  ?'active':'' ?>">
      <i class="fas fa-th-large"></i><span>Dashboard</span>
    </a>
    <a href="<?= BASE_URL ?>admin/products.php"   class="nav-item <?= $active==='products'   ?'active':'' ?>">
      <i class="fas fa-box-open"></i><span>Products</span>
    </a>
    <a href="<?= BASE_URL ?>admin/categories.php" class="nav-item <?= $active==='categories' ?'active':'' ?>">
      <i class="fas fa-tags"></i><span>Categories</span>
    </a>
    <a href="<?= BASE_URL ?>admin/orders.php"     class="nav-item <?= $active==='orders' ? 'active' : ($pendingOrders > 0 ? 'has-pending' : '') ?>">
      <i class="fas fa-receipt"></i><span>Orders</span>
      <?php if ($pendingOrders > 0): ?>
      <span class="nav-badge" id="sidebarPendingBadge"><?= $pendingOrders > 99 ? '99+' : $pendingOrders ?></span>
      <?php else: ?>
      <span class="nav-badge" id="sidebarPendingBadge" style="display:none">0</span>
      <?php endif ?>
    </a>

    <div class="nav-label" style="margin-top:16px">Store</div>
    <a href="<?= BASE_URL ?>admin/customers.php"  class="nav-item <?= $active==='customers'  ?'active':'' ?>">
      <i class="fas fa-users"></i><span>Customers</span>
    </a>
    <a href="<?= BASE_URL ?>admin/messages.php" class="nav-item <?= $active==='messages' ? 'active' : ($unreadMessages > 0 ? 'has-pending' : '') ?>">
      <i class="fas fa-envelope"></i><span>Messages</span>
      <?php if ($unreadMessages > 0): ?>
      <span class="nav-badge"><?= $unreadMessages > 99 ? '99+' : $unreadMessages ?></span>
      <?php endif ?>
    </a>
    <a href="<?= BASE_URL ?>admin/settings.php"   class="nav-item <?= $active==='settings'   ?'active':'' ?>">
      <i class="fas fa-cog"></i><span>Settings</span>
    </a>

    <div class="nav-label" style="margin-top:16px">Links</div>
    <a href="<?= BASE_URL ?>index.php" target="_blank" class="nav-item">
      <i class="fas fa-store"></i><span>View Store</span>
    </a>
  </nav>

  <div class="sidebar-user">
    <div class="su-avatar"><?= strtoupper(substr($name,0,1)) ?></div>
    <div class="su-info">
      <strong><?= htmlspecialchars($name) ?></strong>
      <span>@<?= htmlspecialchars($uname) ?></span>
    </div>
    <a href="<?= BASE_URL ?>admin/logout.php" class="su-logout" title="Logout">
      <i class="fas fa-sign-out-alt"></i>
    </a>
  </div>
</aside>

<!-- Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Main Wrapper -->
<div class="main-wrap">

  <!-- Top bar -->
  <header class="topbar">
    <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
      <i class="fas fa-bars"></i>
    </button>
    <div class="topbar-title"><?= htmlspecialchars($title) ?></div>
    <div class="topbar-right">
      <a href="<?= BASE_URL ?>index.php" target="_blank" class="topbar-btn" title="View Store">
        <i class="fas fa-external-link-alt"></i>
      </a>
      <a href="<?= BASE_URL ?>admin/logout.php" class="topbar-btn" title="Logout">
        <i class="fas fa-sign-out-alt"></i>
      </a>
    </div>
  </header>

  <!-- Flash message -->
  <?php if ($flash): ?>
  <div class="flash flash-<?= $flash['type'] ?>" id="flashMsg">
    <i class="fas <?= $flash['type']==='success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
    <button onclick="this.parentElement.remove()" class="flash-close"><i class="fas fa-times"></i></button>
  </div>
  <?php endif ?>

  <!-- Page content starts here -->
  <main class="page-content">

<!-- Order notification toast -->
<div class="order-toast" id="orderToast" role="alert" aria-live="assertive">
  <div class="toast-accent"></div>
  <div class="toast-body">
    <div class="toast-header">
      <div class="toast-icon"><i class="fas fa-bell"></i></div>
      <div class="toast-title">New Order Received</div>
      <button class="toast-close" id="toastClose" aria-label="Dismiss"><i class="fas fa-times"></i></button>
    </div>
    <div class="toast-order-num" id="toastOrderNum"></div>
    <div class="toast-customer" id="toastCustomer"></div>
    <div class="toast-meta"><span class="toast-amount" id="toastTotal"></span> &nbsp;·&nbsp; <span id="toastTime"></span></div>
    <div class="toast-actions">
      <a href="<?= BASE_URL ?>admin/orders.php" class="toast-btn-view" id="toastViewBtn">
        <i class="fas fa-receipt"></i> View Orders
      </a>
      <button class="toast-btn-dismiss" id="toastDismiss">Dismiss</button>
    </div>
  </div>
</div>

<script>
(function () {
  const POLL_INTERVAL = 30000;
  const API_URL       = '<?= BASE_URL ?>admin/api/pending-orders.php';
  const ORDERS_URL    = '<?= BASE_URL ?>admin/orders.php';
  const LS_KEY        = 'gnx_last_order_id';

  // Seed with server-rendered value so we don't toast on first load
  const serverLatestId = <?= $latestOrderId ?>;
  if (!localStorage.getItem(LS_KEY)) {
    localStorage.setItem(LS_KEY, serverLatestId);
  }

  const toast      = document.getElementById('orderToast');
  const badge      = document.getElementById('sidebarPendingBadge');
  const closeBtn   = document.getElementById('toastClose');
  const dismissBtn = document.getElementById('toastDismiss');

  function formatRs(n) {
    return 'Rs. ' + Number(n).toLocaleString('en-LK', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  }

  function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
    if (diff < 0)    return 'Just now';
    if (diff < 60)   return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    return Math.floor(diff / 3600) + 'h ago';
  }

  function showToast(order) {
    toast._shownId = order.id; // track which order triggered this toast
    document.getElementById('toastOrderNum').textContent = order.order_number;
    document.getElementById('toastCustomer').textContent = order.customer_name;
    document.getElementById('toastTotal').textContent    = formatRs(order.total);
    document.getElementById('toastTime').textContent     = timeAgo(order.created_at);
    toast.classList.remove('toast-hide');
    toast.classList.add('toast-show');

    // Auto-dismiss after 12 seconds
    clearTimeout(toast._timer);
    toast._timer = setTimeout(hideToast, 12000);
  }

  function hideToast() {
    toast.classList.add('toast-hide');
    setTimeout(() => toast.classList.remove('toast-show', 'toast-hide'), 350);
  }

  function updateBadge(count) {
    if (!badge) return;
    if (count > 0) {
      badge.textContent = count > 99 ? '99+' : count;
      badge.style.display = '';
    } else {
      badge.style.display = 'none';
    }
  }

  async function poll() {
    try {
      const res  = await fetch(API_URL, { cache: 'no-store' });
      if (!res.ok) return;
      const data = await res.json();

      // Update sidebar badge
      updateBadge(data.pending_count);

      // Show toast if there's a new order we haven't seen
      const lastSeen = parseInt(localStorage.getItem(LS_KEY) || '0', 10);
      if (data.latest_id > lastSeen && data.latest) {
        localStorage.setItem(LS_KEY, data.latest_id);
        showToast(data.latest);
      }
    } catch (_) { /* network error - silently skip */ }
  }

  // X button - hide and mark seen
  closeBtn.addEventListener('click', () => {
    hideToast();
    localStorage.setItem(LS_KEY, String(toast._shownId || localStorage.getItem(LS_KEY)));
  });

  // Dismiss button - same as X
  dismissBtn.addEventListener('click', () => {
    hideToast();
    localStorage.setItem(LS_KEY, String(toast._shownId || localStorage.getItem(LS_KEY)));
  });

  // View Orders - slide toast out first, then navigate
  const viewBtn = document.getElementById('toastViewBtn');
  viewBtn.addEventListener('click', function (e) {
    e.preventDefault();
    const dest = this.href;
    localStorage.setItem(LS_KEY, String(toast._shownId || localStorage.getItem(LS_KEY)));
    hideToast();
    setTimeout(() => { window.location.href = dest; }, 320);
  });

  // Start polling
  setInterval(poll, POLL_INTERVAL);
})();
</script>
