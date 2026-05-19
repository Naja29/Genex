<?php
$_isAjax = $_SERVER['REQUEST_METHOD'] === 'POST' && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
if ($_isAjax) {
    require_once dirname(__DIR__) . '/includes/functions.php';
    require_once dirname(__DIR__) . '/includes/auth.php';
    if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Session expired.']); exit; }
} else {
    $pageTitle  = 'Settings';
    $activePage = 'settings';
    require_once __DIR__ . '/includes/layout.php';
    require_once dirname(__DIR__) . '/includes/functions.php';
}

$db = getDB();

// Handle saves 
if (isPost()) {
    $section = post('section');

    // Store info
    if ($section === 'store') {
        $fields = ['store_name','store_tagline','store_phone','store_whatsapp','store_email','store_address','store_hours','store_map_embed','store_map_link'];
        foreach ($fields as $f) setSetting($f, post($f));
        if (isAjax()) jsonOk('Store information saved.');
        flash('success', 'Store information saved.');
        redirect(BASE_URL . 'admin/settings.php#store');
    }

    // Social media
    if ($section === 'social') {
        $fields = ['facebook_url','instagram_url','youtube_url','tiktok_url'];
        foreach ($fields as $f) setSetting($f, post($f));
        if (isAjax()) jsonOk('Social media links saved.');
        flash('success', 'Social media links saved.');
        redirect(BASE_URL . 'admin/settings.php#social');
    }

    // Delivery
    if ($section === 'delivery') {
        setSetting('free_delivery_min', post('free_delivery_min'));
        setSetting('currency_symbol',   post('currency_symbol'));
        setSetting('currency_code',     post('currency_code'));
        if (isAjax()) jsonOk('Delivery settings saved.');
        flash('success', 'Delivery settings saved.');
        redirect(BASE_URL . 'admin/settings.php#delivery');
    }

    // Email / SMTP
    if ($section === 'email') {
        $fields = ['smtp_host','smtp_port','smtp_encryption','smtp_username',
                   'smtp_from_name','smtp_from_email','admin_notify_email'];
        foreach ($fields as $f) setSetting($f, post($f));
        if (post('smtp_password')) setSetting('smtp_password', post('smtp_password'));
        if (isAjax()) jsonOk('Email settings saved.');
        flash('success', 'Email settings saved.');
        redirect(BASE_URL . 'admin/settings.php#email');
    }

    // Test email
    if ($section === 'test_email') {
        require_once dirname(__DIR__) . '/includes/mailer.php';
        $testTo = getSetting('admin_notify_email') ?: getSetting('store_email');
        if ($testTo) {
            $ok = sendMail($testTo, 'Admin', 'Test Email - Genex',
                '<h2 style="color:#d4920a">✅ Email is working!</h2><p>Your SMTP settings are configured correctly on Genex Admin.</p>');
            if (isAjax()) {
                if ($ok) jsonOk('Test email sent to ' . $testTo . '.');
                else     jsonErr('Failed to send. Check your SMTP settings.');
            }
            flash($ok ? 'success' : 'error',
                  $ok ? 'Test email sent to ' . $testTo . '.' : 'Failed to send. Check your SMTP settings.');
        } else {
            if (isAjax()) jsonErr('Set an Admin Notification Email first.');
            flash('error', 'Set an Admin Notification Email first.');
        }
        redirect(BASE_URL . 'admin/settings.php#email');
    }

    // Change username
    if ($section === 'username') {
        $newUsername = post('username');
        $password    = post('current_password');

        if (!$newUsername || !$password) {
            if (isAjax()) jsonErr('All fields are required.');
            flash('error', 'All fields are required.');
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $newUsername)) {
            if (isAjax()) jsonErr('Username must be 3–30 characters (letters, numbers, underscore).');
            flash('error', 'Username must be 3–30 characters (letters, numbers, underscore).');
        } else {
            $stmt = $db->prepare('SELECT password FROM admin_users WHERE id = ?');
            $stmt->execute([adminId()]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($password, $hash)) {
                if (isAjax()) jsonErr('Current password is incorrect.');
                flash('error', 'Current password is incorrect.');
            } else {
                $taken = $db->prepare('SELECT id FROM admin_users WHERE username = ? AND id != ?');
                $taken->execute([$newUsername, adminId()]);
                if ($taken->fetch()) {
                    if (isAjax()) jsonErr('That username is already taken.');
                    flash('error', 'That username is already taken.');
                } else {
                    $db->prepare('UPDATE admin_users SET username = ? WHERE id = ?')->execute([$newUsername, adminId()]);
                    $_SESSION['admin_username'] = $newUsername;
                    if (isAjax()) jsonOk('Username updated successfully.');
                    flash('success', 'Username updated successfully.');
                }
            }
        }
        redirect(BASE_URL . 'admin/settings.php#account');
    }

    // Change password
    if ($section === 'password') {
        $current = post('current_password');
        $new     = post('new_password');
        $confirm = post('confirm_password');

        if (!$current || !$new || !$confirm) {
            if (isAjax()) jsonErr('All fields are required.');
            flash('error', 'All fields are required.');
        } elseif (strlen($new) < 8) {
            if (isAjax()) jsonErr('New password must be at least 8 characters.');
            flash('error', 'New password must be at least 8 characters.');
        } elseif ($new !== $confirm) {
            if (isAjax()) jsonErr('New passwords do not match.');
            flash('error', 'New passwords do not match.');
        } else {
            $stmt = $db->prepare('SELECT password FROM admin_users WHERE id = ?');
            $stmt->execute([adminId()]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($current, $hash)) {
                if (isAjax()) jsonErr('Current password is incorrect.');
                flash('error', 'Current password is incorrect.');
            } else {
                $db->prepare('UPDATE admin_users SET password = ? WHERE id = ?')
                   ->execute([password_hash($new, PASSWORD_DEFAULT), adminId()]);
                if (isAjax()) jsonOk('Password changed successfully.');
                flash('success', 'Password changed successfully.');
            }
        }
        redirect(BASE_URL . 'admin/settings.php#account');
    }
}

// Load current settings
$s = fn($k, $d='') => getSetting($k, $d);

// Load admin user
$adminUser = $db->prepare('SELECT name, username, email, role, last_login, created_at FROM admin_users WHERE id = ?');
$adminUser->execute([adminId()]);
$adminUser = $adminUser->fetch();
?>

<div class="page-header">
  <div>
    <h1>Settings</h1>
    <p>Manage your store configuration and admin account.</p>
  </div>
</div>

<!-- Tab nav -->
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:24px;border-bottom:1px solid var(--border);padding-bottom:0">
  <?php
  $tabs = [
    'store'    => ['fas fa-store',       'Store Info'],
    'social'   => ['fab fa-instagram',   'Social Media'],
    'delivery' => ['fas fa-truck',       'Delivery'],
    'email'    => ['fas fa-envelope',    'Email / SMTP'],
    'account'  => ['fas fa-user-shield', 'Admin Account'],
  ];
  foreach ($tabs as $id => [$icon, $label]): ?>
    <a href="#<?= $id ?>" class="settings-tab" data-tab="<?= $id ?>">
      <i class="<?= $icon ?>"></i> <?= $label ?>
    </a>
  <?php endforeach ?>
</div>

<div style="max-width:760px">

  <!-- Store Info -->
  <div class="settings-panel" id="panel-store">
    <form method="POST">
      <input type="hidden" name="section" value="store">
      <div class="card">
        <div class="card-title"><i class="fas fa-store" style="color:var(--primary)"></i> Store Information</div>
        <div class="form-grid" style="gap:16px">

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">Store Name</label>
              <input type="text" name="store_name" class="form-input"
                value="<?= htmlspecialchars($s('store_name','Genex - Global Xperience')) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Tagline</label>
              <input type="text" name="store_tagline" class="form-input"
                value="<?= htmlspecialchars($s('store_tagline')) ?>" placeholder="Short store tagline">
            </div>
          </div>

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">Phone / Call</label>
              <div style="position:relative">
                <i class="fas fa-phone" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px"></i>
                <input type="text" name="store_phone" class="form-input" style="padding-left:36px"
                  value="<?= htmlspecialchars($s('store_phone','+94 77 723 7962')) ?>" placeholder="+94 77 000 0000">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">WhatsApp Number <span class="form-hint" style="display:inline">(digits only)</span></label>
              <div style="position:relative">
                <i class="fab fa-whatsapp" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#25d366;font-size:14px"></i>
                <input type="text" name="store_whatsapp" class="form-input" style="padding-left:36px"
                  value="<?= htmlspecialchars($s('store_whatsapp','94777237962')) ?>" placeholder="94777237962">
              </div>
              <span class="form-hint">Used for WhatsApp order links. No + or spaces.</span>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Email Address</label>
            <div style="position:relative">
              <i class="fas fa-envelope" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px"></i>
              <input type="email" name="store_email" class="form-input" style="padding-left:36px"
                value="<?= htmlspecialchars($s('store_email','genecoretech@gmail.com')) ?>" placeholder="store@email.com">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Address</label>
            <textarea name="store_address" class="form-textarea" rows="2"
              placeholder="Full store address"><?= htmlspecialchars($s('store_address')) ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Opening Hours</label>
            <input type="text" name="store_hours" class="form-input"
              value="<?= htmlspecialchars($s('store_hours','Mon-Sat: 8:00 AM - 7:00 PM | Sunday: Closed')) ?>"
              placeholder="Mon-Sat: 8:00 AM - 7:00 PM | Sunday: Closed">
          </div>

          <!-- Google Maps -->
          <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:4px">
            <div style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:14px">
              <i class="fas fa-map-marked-alt" style="color:var(--primary);margin-right:6px"></i> Google Maps
            </div>

            <div class="form-group">
              <label class="form-label">Map Embed URL <span style="color:var(--text-dim);font-weight:400;font-size:11px;text-transform:none">(shown on Contact page)</span></label>
              <div style="position:relative">
                <i class="fas fa-map" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#4285f4;font-size:13px"></i>
                <input type="url" name="store_map_embed" class="form-input" style="padding-left:36px"
                  value="<?= htmlspecialchars($s('store_map_embed')) ?>"
                  placeholder="https://www.google.com/maps/embed?pb=...">
              </div>
              <span class="form-hint">Google Maps → Share → <strong>Embed a map</strong> → copy only the <code style="background:var(--bg-3);padding:1px 5px;border-radius:3px">src</code> value from the iframe code</span>
            </div>

            <div class="form-group">
              <label class="form-label">Directions Link <span style="color:var(--text-dim);font-weight:400;font-size:11px;text-transform:none">("Open in Maps" button)</span></label>
              <div style="position:relative">
                <i class="fas fa-directions" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#34a853;font-size:13px"></i>
                <input type="url" name="store_map_link" class="form-input" style="padding-left:36px"
                  value="<?= htmlspecialchars($s('store_map_link')) ?>"
                  placeholder="https://maps.google.com/?q=...">
              </div>
              <span class="form-hint">Google Maps → Share → <strong>Copy link</strong> - used for the "Get Directions" button</span>
            </div>

            <?php if ($s('store_map_embed')): ?>
            <div style="margin-top:8px;border-radius:var(--r);overflow:hidden;height:160px;border:1px solid var(--border)">
              <iframe src="<?= htmlspecialchars($s('store_map_embed')) ?>"
                width="100%" height="100%" style="border:0;display:block" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade" title="Map preview"></iframe>
            </div>
            <?php endif ?>
          </div>

          <div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Store Info</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Social Media -->
  <div class="settings-panel" id="panel-social">
    <form method="POST">
      <input type="hidden" name="section" value="social">
      <div class="card">
        <div class="card-title"><i class="fas fa-share-alt" style="color:var(--primary)"></i> Social Media Links</div>
        <div class="form-grid" style="gap:16px">

          <?php
          $socials = [
            'facebook_url'  => ['fab fa-facebook-f',  '#1877f2', 'Facebook URL',  'https://web.facebook.com/genecoretech'],
            'instagram_url' => ['fab fa-instagram',   '#e1306c', 'Instagram URL', 'https://instagram.com/yourpage'],
            'youtube_url'   => ['fab fa-youtube',     '#ff0000', 'YouTube URL',   'https://youtube.com/yourchannel'],
            'tiktok_url'    => ['fab fa-tiktok',      '#010101', 'TikTok URL',    'https://tiktok.com/@yourpage'],
          ];
          foreach ($socials as $key => [$icon, $color, $label, $placeholder]): ?>
          <div class="form-group">
            <label class="form-label"><?= $label ?></label>
            <div style="position:relative">
              <i class="<?= $icon ?>" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:<?= $color ?>;font-size:14px"></i>
              <input type="url" name="<?= $key ?>" class="form-input" style="padding-left:36px"
                value="<?= htmlspecialchars($s($key)) ?>" placeholder="<?= $placeholder ?>">
            </div>
          </div>
          <?php endforeach ?>

          <div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Social Links</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Delivery -->
  <div class="settings-panel" id="panel-delivery">
    <form method="POST">
      <input type="hidden" name="section" value="delivery">
      <div class="card">
        <div class="card-title"><i class="fas fa-truck" style="color:var(--primary)"></i> Delivery &amp; Currency</div>
        <div class="form-grid" style="gap:16px">

          <div class="form-group">
            <label class="form-label">Free Delivery Minimum (Rs.)</label>
            <div style="position:relative">
              <i class="fas fa-truck" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--green);font-size:13px"></i>
              <input type="number" name="free_delivery_min" class="form-input" style="padding-left:36px" min="0"
                value="<?= htmlspecialchars($s('free_delivery_min','50000')) ?>">
            </div>
            <span class="form-hint">Orders above this amount get free delivery. Set to 0 to disable.</span>
          </div>

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">Currency Symbol</label>
              <input type="text" name="currency_symbol" class="form-input" maxlength="10"
                value="<?= htmlspecialchars($s('currency_symbol','Rs.')) ?>" placeholder="Rs.">
            </div>
            <div class="form-group">
              <label class="form-label">Currency Code</label>
              <input type="text" name="currency_code" class="form-input" maxlength="5"
                value="<?= htmlspecialchars($s('currency_code','LKR')) ?>" placeholder="LKR">
            </div>
          </div>

          <div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Delivery Settings</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Email / SMTP -->
  <div class="settings-panel" id="panel-email">
    <form method="POST">
      <input type="hidden" name="section" value="email">
      <div class="card" style="margin-bottom:20px">
        <div class="card-title"><i class="fas fa-server" style="color:var(--primary)"></i> SMTP Server</div>
        <div class="form-grid" style="gap:16px">

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">SMTP Host</label>
              <input type="text" name="smtp_host" class="form-input"
                value="<?= htmlspecialchars($s('smtp_host')) ?>"
                placeholder="e.g. mail.yourdomain.com or smtp.gmail.com">
              <span class="form-hint">Your mail server hostname (from cPanel or Gmail)</span>
            </div>
            <div class="form-group">
              <label class="form-label">Port</label>
              <select name="smtp_port" class="form-select">
                <?php foreach (['587'=>'587 - TLS (recommended)','465'=>'465 - SSL','25'=>'25 - Plain'] as $p=>$l): ?>
                  <option value="<?= $p ?>" <?= $s('smtp_port','587')===$p?'selected':'' ?>><?= $l ?></option>
                <?php endforeach ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Encryption</label>
            <select name="smtp_encryption" class="form-select">
              <option value="tls"  <?= $s('smtp_encryption','tls')==='tls' ?'selected':'' ?>>TLS (STARTTLS) - port 587</option>
              <option value="ssl"  <?= $s('smtp_encryption','tls')==='ssl' ?'selected':'' ?>>SSL - port 465</option>
              <option value="none" <?= $s('smtp_encryption','tls')==='none'?'selected':'' ?>>None - port 25</option>
            </select>
          </div>

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">SMTP Username</label>
              <input type="text" name="smtp_username" class="form-input" autocomplete="off"
                value="<?= htmlspecialchars($s('smtp_username')) ?>"
                placeholder="your@email.com">
            </div>
            <div class="form-group">
              <label class="form-label">SMTP Password</label>
              <input type="password" name="smtp_password" class="form-input" autocomplete="new-password"
                placeholder="Leave blank to keep current">
              <span class="form-hint">Leave blank to keep the saved password</span>
            </div>
          </div>

        </div>
      </div>

      <div class="card" style="margin-bottom:20px">
        <div class="card-title"><i class="fas fa-paper-plane" style="color:var(--primary)"></i> Sender &amp; Notifications</div>
        <div class="form-grid" style="gap:16px">

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">From Name</label>
              <input type="text" name="smtp_from_name" class="form-input"
                value="<?= htmlspecialchars($s('smtp_from_name', getSetting('store_name','Genex Store'))) ?>"
                placeholder="Genex Store">
              <span class="form-hint">Shown as the sender in email clients</span>
            </div>
            <div class="form-group">
              <label class="form-label">From Email Address</label>
              <input type="email" name="smtp_from_email" class="form-input"
                value="<?= htmlspecialchars($s('smtp_from_email')) ?>"
                placeholder="noreply@yourdomain.com">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Admin Notification Email</label>
            <div style="position:relative">
              <i class="fas fa-bell" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--primary);font-size:13px"></i>
              <input type="email" name="admin_notify_email" class="form-input" style="padding-left:36px"
                value="<?= htmlspecialchars($s('admin_notify_email', getSetting('store_email'))) ?>"
                placeholder="admin@yourdomain.com">
            </div>
            <span class="form-hint">New order alerts are sent to this address</span>
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap">
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Email Settings</button>
          </div>
        </div>
      </div>
    </form>

    <!-- Test email -->
    <div class="card">
      <div class="card-title"><i class="fas fa-vial" style="color:var(--primary)"></i> Test Email</div>
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">
        Send a test email to your admin notification address to verify the SMTP settings are working.
      </p>
      <form method="POST">
        <input type="hidden" name="section" value="test_email">
        <button type="submit" class="btn btn-ghost"><i class="fas fa-paper-plane"></i> Send Test Email</button>
      </form>

      <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
        <div style="font-size:12.5px;color:var(--text-muted);line-height:1.8">
          <strong style="color:var(--text)">cPanel setup tip:</strong><br>
          Host: <code style="background:var(--bg-3);padding:1px 6px;border-radius:4px">mail.yourdomain.com</code> &nbsp;|&nbsp;
          Port: <code style="background:var(--bg-3);padding:1px 6px;border-radius:4px">587</code> &nbsp;|&nbsp;
          Encryption: <code style="background:var(--bg-3);padding:1px 6px;border-radius:4px">TLS</code><br>
          Username and password are the same as your cPanel email account.
        </div>
      </div>
    </div>
  </div>

  <!-- Admin Account -->
  <div class="settings-panel" id="panel-account">

    <!-- Admin info card -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-title"><i class="fas fa-user-shield" style="color:var(--primary)"></i> Admin Account</div>
      <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
        <div style="width:60px;height:60px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#fff;flex-shrink:0">
          <?= strtoupper(substr(adminName(), 0, 1)) ?>
        </div>
        <div>
          <div style="font-size:16px;font-weight:700;color:var(--text)"><?= htmlspecialchars($adminUser['name'] ?? '') ?></div>
          <div style="font-size:13px;color:var(--text-muted);margin-top:2px">@<?= htmlspecialchars($adminUser['username'] ?? '') ?></div>
          <div style="display:flex;gap:12px;margin-top:6px;flex-wrap:wrap">
            <span class="badge badge-purple"><?= ucfirst($adminUser['role'] ?? 'admin') ?></span>
            <?php if ($adminUser['last_login']):
              $ll     = strtotime($adminUser['last_login']);
              $diff   = time() - $ll;
              $rel    = $diff < 3600   ? 'Just now'
                      : ($diff < 86400  ? floor($diff/3600) . 'h ago'
                      : ($diff < 604800 ? floor($diff/86400) . 'd ago'
                      : ''));
              $full   = date('d M Y, h:i A', $ll);
            ?>
              <span style="font-size:12px;color:var(--text-muted)" title="<?= $full ?>">
                <i class="fas fa-clock"></i>
                Last login: <?= $full ?>
                <?= $rel ? "<span style='color:var(--text-dim)'> ($rel)</span>" : '' ?>
              </span>
            <?php endif ?>

          </div>
        </div>
      </div>
    </div>

    <!-- Change username -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-title"><i class="fas fa-user-edit" style="color:var(--primary)"></i> Change Username</div>
      <form method="POST" style="max-width:420px">
        <input type="hidden" name="section" value="username">
        <div class="form-grid" style="gap:14px">
          <div class="form-group">
            <label class="form-label">New Username</label>
            <input type="text" name="username" class="form-input"
              value="<?= htmlspecialchars(adminUsername()) ?>"
              placeholder="Enter new username" required>
            <span class="form-hint">3-30 characters. Letters, numbers and underscore only.</span>
          </div>
          <div class="form-group">
            <label class="form-label">Current Password <span style="color:var(--text-muted);font-weight:400;text-transform:none;font-size:11px">to confirm</span></label>
            <input type="password" name="current_password" class="form-input" placeholder="Enter current password" required>
          </div>
          <div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Update Username</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Change password -->
    <div class="card">
      <div class="card-title"><i class="fas fa-lock" style="color:var(--primary)"></i> Change Password</div>
      <form method="POST" style="max-width:420px" id="pwForm">
        <input type="hidden" name="section" value="password">
        <div class="form-grid" style="gap:14px">
          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-input" placeholder="Enter current password" required>
          </div>
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" id="newPw" class="form-input" placeholder="Min. 8 characters" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirmPw" class="form-input" placeholder="Repeat new password" required>
            <span class="form-hint" id="pwMatchHint" style="display:none"></span>
          </div>
          <div>
            <button type="submit" class="btn btn-gold" id="pwSubmit"><i class="fas fa-key"></i> Change Password</button>
          </div>
        </div>
      </form>
    </div>

  </div>

</div>

<style>
.settings-tab {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 10px 18px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
  border-radius: var(--r) var(--r) 0 0;
  border: 1px solid transparent;
  border-bottom: none;
  transition: var(--t);
  margin-bottom: -1px;
  cursor: pointer;
}
.settings-tab:hover { color: var(--text); background: var(--bg-3); }
.settings-tab.active { background: var(--bg-2); border-color: var(--border); color: var(--primary); }
.settings-panel { display: none; }
.settings-panel.active { display: block; }
</style>

<script>
const tabs   = document.querySelectorAll('.settings-tab');
const panels = document.querySelectorAll('.settings-panel');

function showTab(id) {
  tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === id));
  panels.forEach(p => p.classList.toggle('active', p.id === 'panel-' + id));
  history.replaceState(null, '', '#' + id);
}

tabs.forEach(t => t.addEventListener('click', e => { e.preventDefault(); showTab(t.dataset.tab); }));

// Show tab from URL hash
const hash = location.hash.replace('#','') || 'store';
showTab(['store','social','delivery','email','account'].includes(hash) ? hash : 'store');

// Password match validation
const newPw     = document.getElementById('newPw');
const confirmPw = document.getElementById('confirmPw');
const hint      = document.getElementById('pwMatchHint');
const pwSubmit  = document.getElementById('pwSubmit');

function checkPw() {
  if (!confirmPw.value) { hint.style.display='none'; return; }
  const match = newPw.value === confirmPw.value;
  hint.style.display = 'block';
  hint.textContent   = match ? '✓ Passwords match' : '✗ Passwords do not match';
  hint.style.color   = match ? 'var(--green)' : 'var(--red)';
  pwSubmit.disabled  = !match;
}
newPw.addEventListener('input', checkPw);
confirmPw.addEventListener('input', checkPw);
</script>

<script>
// Generic AJAX save for all settings forms
document.querySelectorAll('.settings-panel form').forEach(function (form) {
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var btn  = form.querySelector('[type="submit"]');
    var orig = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…'; }
    fetch(location.pathname, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form)
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (btn) { btn.disabled = false; btn.innerHTML = orig; }
      adminSnackbar(res.message, res.success ? 'success' : 'error');
      var section = form.querySelector('[name="section"]');
      if (res.success && section && section.value === 'username') {
        setTimeout(function () { location.reload(); }, 1200);
      }
    })
    .catch(function () {
      if (btn) { btn.disabled = false; btn.innerHTML = orig; }
      adminSnackbar('Network error. Try again.', 'error');
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
