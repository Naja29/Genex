<?php
$_isAjax = $_SERVER['REQUEST_METHOD'] === 'POST' && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
if ($_isAjax) {
    require_once dirname(__DIR__) . '/includes/functions.php';
    require_once dirname(__DIR__) . '/includes/auth.php';
    if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Session expired.']); exit; }
} else {
    $pageTitle  = 'Messages';
    $activePage = 'messages';
    require_once __DIR__ . '/includes/layout.php';
    require_once dirname(__DIR__) . '/includes/functions.php';
}

$db = getDB();

// Mark a single message as read
if (isPost() && post('action') === 'mark_read') {
    $db->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([(int)post('id')]);
    redirect(BASE_URL . 'admin/messages.php');
}

// Mark all as read
if (isPost() && post('action') === 'mark_all_read') {
    $db->query('UPDATE contact_messages SET is_read = 1');
    if (isAjax()) jsonOk('All messages marked as read.');
    flash('success', 'All messages marked as read.');
    redirect(BASE_URL . 'admin/messages.php');
}

// Delete
if (isPost() && post('action') === 'delete') {
    $db->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([(int)post('id')]);
    flash('success', 'Message deleted.');
    redirect(BASE_URL . 'admin/messages.php');
}

$filter  = get('filter', 'all'); // all | unread
$page    = max(1, (int)get('page', '1'));
$perPage = 20;

$where  = $filter === 'unread' ? 'WHERE is_read = 0' : '';
$total  = (int)$db->query("SELECT COUNT(*) FROM contact_messages $where")->fetchColumn();
$pg     = paginate($total, $perPage, $page);

$messages = $db->query("
    SELECT * FROM contact_messages $where
    ORDER BY created_at DESC
    LIMIT {$pg['per_page']} OFFSET {$pg['offset']}
")->fetchAll();

$totalUnread = (int)$db->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
$totalAll    = (int)$db->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();

// Viewing a single message - mark it read on open
$viewing = null;
if (isset($_GET['view'])) {
    $stmt = $db->prepare('SELECT * FROM contact_messages WHERE id = ?');
    $stmt->execute([(int)$_GET['view']]);
    $viewing = $stmt->fetch();
    if ($viewing && !$viewing['is_read']) {
        $db->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$viewing['id']]);
        $viewing['is_read'] = 1;
    }
}
?>

<div class="page-header">
  <div>
    <h1>Messages</h1>
    <p>Contact form submissions from your website.</p>
  </div>
  <?php if ($totalUnread > 0): ?>
  <div class="ph-actions">
    <button type="button" id="markAllReadBtn" class="btn btn-ghost"><i class="fas fa-check-double"></i> Mark All Read</button>
  </div>
  <?php endif ?>
</div>

<!-- Stats strip -->
<div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
  <a href="messages.php" class="stat-strip <?= $filter==='all'?'active':'' ?>">
    <i class="fas fa-envelope" style="color:var(--primary)"></i> All <strong><?= $totalAll ?></strong>
  </a>
  <a href="messages.php?filter=unread" class="stat-strip <?= $filter==='unread'?'active':'' ?>">
    <i class="fas fa-envelope-open" style="color:var(--blue)"></i> Unread <strong><?= $totalUnread ?></strong>
  </a>
</div>

<?php if ($viewing): ?>
<!-- Message Detail View -->
<div style="display:flex;gap:6px;margin-bottom:20px">
  <a href="messages.php" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">
  <div class="card">
    <div class="card-title" style="margin-bottom:20px">
      <i class="fas fa-envelope-open" style="color:var(--primary)"></i>
      <?= htmlspecialchars($viewing['subject'] ?: 'No Subject') ?>
    </div>
    <div style="font-size:14px;color:var(--text);line-height:1.8;white-space:pre-wrap"><?= htmlspecialchars($viewing['message']) ?></div>
  </div>
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="card">
      <div class="card-title"><i class="fas fa-user" style="color:var(--primary)"></i> Sender</div>
      <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
        <div><div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px">Name</div>
          <strong><?= htmlspecialchars($viewing['name']) ?></strong></div>
        <div><div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px">Email</div>
          <a href="mailto:<?= htmlspecialchars($viewing['email']) ?>" style="color:var(--primary)"><?= htmlspecialchars($viewing['email']) ?></a></div>
        <?php if ($viewing['phone']): ?>
        <div><div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px">Phone</div>
          <a href="tel:<?= htmlspecialchars($viewing['phone']) ?>" style="color:var(--primary)"><?= htmlspecialchars($viewing['phone']) ?></a></div>
        <?php endif ?>
        <div><div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px">Received</div>
          <span style="color:var(--text-muted)"><?= date('d M Y, h:i A', strtotime($viewing['created_at'])) ?></span></div>
      </div>
    </div>
    <div class="card" style="display:flex;flex-direction:column;gap:10px">
      <a href="mailto:<?= htmlspecialchars($viewing['email']) ?>" class="btn btn-gold" style="justify-content:center">
        <i class="fas fa-reply"></i> Reply by Email
      </a>
      <?php if ($viewing['phone']): ?>
      <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$viewing['phone']) ?>" target="_blank" rel="noopener"
         class="btn btn-ghost" style="justify-content:center;color:#25d366">
        <i class="fab fa-whatsapp"></i> Reply on WhatsApp
      </a>
      <?php endif ?>
      <button type="button" class="btn btn-danger btn-sm js-delete-msg-detail" style="width:100%;justify-content:center;margin-top:4px"
        data-id="<?= $viewing['id'] ?>">
        <i class="fas fa-trash"></i> Delete Message
      </button>
    </div>
  </div>
</div>

<?php else: ?>
<!-- Message List -->
<div class="card" style="padding:0;overflow:hidden">
  <?php if ($messages): ?>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width:16px"></th>
          <th>From</th>
          <th>Subject</th>
          <th>Message</th>
          <th>Received</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($messages as $msg): ?>
        <tr style="<?= !$msg['is_read'] ? 'background:rgba(212,146,10,.04)' : '' ?>">
          <td>
            <?php if (!$msg['is_read']): ?>
              <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--primary)" title="Unread"></span>
            <?php endif ?>
          </td>
          <td>
            <div style="font-weight:<?= !$msg['is_read']?'700':'500' ?>;font-size:13px;color:var(--text)"><?= htmlspecialchars($msg['name']) ?></div>
            <div style="font-size:11.5px;color:var(--text-muted);margin-top:1px">
              <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" style="color:var(--text-muted)"><?= htmlspecialchars($msg['email']) ?></a>
            </div>
          </td>
          <td style="font-size:13px;color:var(--text-muted)"><?= htmlspecialchars($msg['subject'] ?: '-') ?></td>
          <td style="max-width:260px">
            <div style="font-size:12.5px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px">
              <?= htmlspecialchars($msg['message']) ?>
            </div>
          </td>
          <td style="font-size:12px;color:var(--text-muted);white-space:nowrap"><?= timeAgo($msg['created_at']) ?><br><span style="font-size:11px"><?= date('d M Y', strtotime($msg['created_at'])) ?></span></td>
          <td style="text-align:right">
            <div style="display:flex;gap:6px;justify-content:flex-end">
              <a href="messages.php?view=<?= $msg['id'] ?>" class="btn btn-ghost btn-icon btn-sm" title="Read">
                <i class="fas fa-eye"></i>
              </a>
              <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="btn btn-ghost btn-icon btn-sm" title="Reply by Email">
                <i class="fas fa-reply"></i>
              </a>
              <button type="button" class="btn btn-danger btn-icon btn-sm js-delete-msg"
                data-id="<?= $msg['id'] ?>" title="Delete">
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
      <?php for ($i=1; $i<=$pg['pages']; $i++): ?>
        <a href="messages.php?filter=<?= $filter ?>&page=<?= $i ?>"
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
      <i class="fas fa-envelope-open"></i>
      <h3>No messages<?= $filter==='unread'?' unread':'' ?></h3>
      <p><?= $filter==='unread' ? 'All messages have been read.' : 'Contact form submissions will appear here.' ?></p>
    </div>
  <?php endif ?>
</div>
<?php endif ?>

<style>
.stat-strip{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:var(--r);background:var(--bg-2);border:1px solid var(--border);font-size:13px;color:var(--text-muted);transition:var(--t)}
.stat-strip strong{color:var(--text);margin-left:2px}
.stat-strip:hover,.stat-strip.active{border-color:rgba(212,146,10,.35);color:var(--primary);background:rgba(212,146,10,.07)}
</style>

<script>
document.querySelectorAll('.js-delete-msg').forEach(function (btn) {
  btn.addEventListener('click', function () {
    adminDeleteRow(btn, '<?= BASE_URL ?>admin/api/delete-message.php');
  });
});

var markAllBtn = document.getElementById('markAllReadBtn');
if (markAllBtn) {
  markAllBtn.addEventListener('click', function () {
    markAllBtn.disabled = true;
    markAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Marking…';
    fetch(location.pathname, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: 'action=mark_all_read'
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (res.success) {
        adminSnackbar(res.message, 'success');
        setTimeout(function () { location.reload(); }, 800);
      } else {
        markAllBtn.disabled = false;
        markAllBtn.innerHTML = '<i class="fas fa-check-double"></i> Mark All Read';
        adminSnackbar('Failed. Try again.', 'error');
      }
    })
    .catch(function () {
      markAllBtn.disabled = false;
      markAllBtn.innerHTML = '<i class="fas fa-check-double"></i> Mark All Read';
      adminSnackbar('Network error. Try again.', 'error');
    });
  });
}

var delDetailBtn = document.querySelector('.js-delete-msg-detail');
if (delDetailBtn) {
  delDetailBtn.addEventListener('click', function () {
    if (!confirm('Delete this message?')) return;
    delDetailBtn.disabled = true;
    fetch('<?= BASE_URL ?>admin/api/delete-message.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: parseInt(delDetailBtn.dataset.id) })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (res.success) {
        adminSnackbar('Message deleted.', 'success');
        setTimeout(function () { window.location.href = '<?= BASE_URL ?>admin/messages.php'; }, 800);
      } else {
        delDetailBtn.disabled = false;
        adminSnackbar('Delete failed.', 'error');
      }
    })
    .catch(function () {
      delDetailBtn.disabled = false;
      adminSnackbar('Network error. Try again.', 'error');
    });
  });
}
</script>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
