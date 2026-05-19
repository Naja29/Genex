<?php
require_once dirname(__DIR__) . '/includes/auth.php';

// Already logged in → go to dashboard
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if (isPost()) {
    $username = post('username');
    $password = post('password');

    if (!$username || !$password) {
        $error = 'Please enter your username and password.';
    } elseif (loginAdmin($username, $password)) {
        $next = get('next');
        redirect($next ?: BASE_URL . 'admin/dashboard.php');
    } else {
        $error = 'Invalid username or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Genex</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --gold: linear-gradient(135deg, #d4920a 0%, #ff6b00 100%);
      --primary: #d4920a;
      --bg: #0d0d0d;
      --bg-2: #161616;
      --bg-3: #1e1e1e;
      --border: #272727;
      --text: #ffffff;
      --text-muted: #888;
    }
    html, body { height: 100%; }
    body {
      font-family: 'Poppins', sans-serif;
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 24px;
      overflow: hidden;
    }

    /* Background orbs */
    .bg-orb {
      position: fixed;
      border-radius: 50%;
      pointer-events: none;
      filter: blur(80px);
    }
    .bg-orb-1 {
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(212,146,10,.15) 0%, transparent 70%);
      top: -150px; left: -150px;
    }
    .bg-orb-2 {
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(59,130,246,.07) 0%, transparent 70%);
      bottom: -100px; right: -100px;
    }

    /* Card */
    .login-card {
      position: relative;
      background: var(--bg-2);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 44px 40px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 24px 64px rgba(0,0,0,.6);
    }

    /* Logo */
    .login-logo {
      text-align: center;
      margin-bottom: 28px;
    }
    .login-logo-img {
      height: 72px;
      width: auto;
      max-width: 180px;
      object-fit: contain;
      border-radius: 14px;
      margin-bottom: 14px;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }
    .admin-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 14px;
      background: rgba(212,146,10,.12);
      border: 1px solid rgba(212,146,10,.25);
      border-radius: 50px;
      font-size: 11.5px;
      font-weight: 600;
      color: var(--primary);
      letter-spacing: .6px;
      text-transform: uppercase;
      margin-top: 2px;
    }
    .admin-pill i { font-size: 10px; }

    /* Divider */
    .divider {
      height: 1px;
      background: var(--border);
      margin-bottom: 28px;
    }

    .login-title { font-size: 17px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
    .login-sub   { font-size: 12.5px; color: var(--text-muted); margin-bottom: 28px; }

    /* Error */
    .error-msg {
      display: flex;
      align-items: center;
      gap: 10px;
      background: rgba(239,68,68,.1);
      border: 1px solid rgba(239,68,68,.3);
      color: #f87171;
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 13px;
      margin-bottom: 22px;
      animation: shake .3s ease;
    }
    @keyframes shake {
      0%,100%{transform:translateX(0)}
      25%{transform:translateX(-6px)}
      75%{transform:translateX(6px)}
    }

    /* Form */
    .form-group { margin-bottom: 18px; }
    .form-label {
      display: block;
      font-size: 11.5px;
      font-weight: 600;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: .6px;
      margin-bottom: 8px;
    }
    .input-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }
    .input-wrap > i {
      position: absolute;
      left: 14px;
      color: var(--text-muted);
      font-size: 14px;
      pointer-events: none;
      transition: color .2s;
    }
    .input-wrap input {
      width: 100%;
      padding: 13px 44px 13px 42px;
      background: var(--bg-3);
      border: 1px solid var(--border);
      border-radius: 10px;
      color: var(--text);
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .input-wrap input::placeholder { color: #444; }
    .input-wrap input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(212,146,10,.12);
    }
    .input-wrap input:focus + i,
    .input-wrap:focus-within > i { color: var(--primary); }

    /* Password toggle */
    .pw-toggle {
      position: absolute;
      right: 14px;
      background: none;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      font-size: 14px;
      padding: 4px;
      transition: color .2s;
    }
    .pw-toggle:hover { color: var(--primary); }

    /* Remember row */
    .remember-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
    }
    .remember-label {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: var(--text-muted);
      cursor: pointer;
    }
    .remember-label input[type="checkbox"] {
      width: 15px; height: 15px;
      accent-color: var(--primary);
      cursor: pointer;
    }
    .forgot-link {
      font-size: 12.5px;
      color: var(--primary);
      text-decoration: none;
    }
    .forgot-link:hover { text-decoration: underline; }

    /* Submit button */
    .btn-login {
      width: 100%;
      padding: 14px;
      background: var(--gold);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 4px 20px rgba(212,146,10,.3);
      transition: opacity .2s, transform .2s;
    }
    .btn-login:hover  { opacity: .92; transform: translateY(-1px); }
    .btn-login:active { transform: translateY(0); }

    /* Footer */
    .login-footer {
      text-align: center;
      margin-top: 28px;
      font-size: 12px;
      color: #333;
    }
    .login-footer a { color: #555; text-decoration: none; }
    .login-footer a:hover { color: var(--primary); }

    /* Loading state */
    .btn-login.loading .btn-text  { display: none; }
    .btn-login .spinner            { display: none; }
    .btn-login.loading .spinner    { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media(max-width:480px) {
      .login-card { padding: 32px 24px; }
    }
  </style>
</head>
<body>

<div class="bg-orb bg-orb-1"></div>
<div class="bg-orb bg-orb-2"></div>

<div class="login-card">

  <!-- Logo -->
  <div class="login-logo">
    <img src="<?= BASE_URL ?>images/logo.jpg" alt="Genex" class="login-logo-img">
    <div><span class="admin-pill"><i class="fas fa-shield-alt"></i> Admin Panel</span></div>
  </div>

  <div class="divider"></div>

  <p class="login-title">Welcome back</p>
  <p class="login-sub">Sign in to manage your store</p>

  <?php if ($error): ?>
    <div class="error-msg">
      <i class="fas fa-exclamation-circle"></i>
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif ?>

  <form method="POST" id="loginForm" autocomplete="off">

    <div class="form-group">
      <label class="form-label">Username</label>
      <div class="input-wrap">
        <i class="fas fa-user"></i>
        <input
          type="text"
          name="username"
          placeholder="Enter your username"
          value="<?= htmlspecialchars(post('username')) ?>"
          required
          autofocus
          autocomplete="username">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Password</label>
      <div class="input-wrap">
        <i class="fas fa-lock"></i>
        <input
          type="password"
          name="password"
          id="pwInput"
          placeholder="Enter your password"
          required>
        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Toggle password">
          <i class="fas fa-eye" id="pwIcon"></i>
        </button>
      </div>
    </div>

    <div class="remember-row">
      <label class="remember-label">
        <input type="checkbox" name="remember"> Remember me
      </label>
      <a href="#" class="forgot-link">Forgot password?</a>
    </div>

    <button type="submit" class="btn-login" id="loginBtn">
      <span class="btn-text"><i class="fas fa-sign-in-alt"></i> Sign In</span>
      <span class="spinner"></span>
    </button>

  </form>

  <div class="login-footer">
    &larr; <a href="<?= BASE_URL ?>index.php">Back to store</a>
    &nbsp;&bull;&nbsp;
    Genex &copy; <?= date('Y') ?>
  </div>

</div>

<script>
// Password show/hide
const pwInput  = document.getElementById('pwInput');
const pwToggle = document.getElementById('pwToggle');
const pwIcon   = document.getElementById('pwIcon');
pwToggle.addEventListener('click', () => {
  const show = pwInput.type === 'password';
  pwInput.type  = show ? 'text' : 'password';
  pwIcon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
});

// Loading state on submit
document.getElementById('loginForm').addEventListener('submit', function() {
  document.getElementById('loginBtn').classList.add('loading');
});
</script>
</body>
</html>
