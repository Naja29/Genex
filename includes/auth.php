<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function startAdminSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
        session_start();
    }
}

function isAdminLoggedIn(): bool {
    startAdminSession();
    if (empty($_SESSION['admin_id'])) return false;

    // Expire idle sessions
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
        logoutAdmin(false);
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function requireLogin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . BASE_URL . 'admin/login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function loginAdmin(string $username, string $password): bool {
    $db   = getDB();
    $stmt = $db->prepare('SELECT id, name, username, password, role FROM admin_users WHERE username = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([trim($username)]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        startAdminSession();
        session_regenerate_id(true);
        $_SESSION['admin_id']       = $user['id'];
        $_SESSION['admin_name']     = $user['name'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role']     = $user['role'];
        $_SESSION['last_activity']  = time();

        $db->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);
        return true;
    }
    return false;
}

function logoutAdmin(bool $redirect = true): void {
    startAdminSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    if ($redirect) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
}

function adminName(): string     { return $_SESSION['admin_name']     ?? 'Admin'; }
function adminUsername(): string { return $_SESSION['admin_username'] ?? 'admin'; }
function adminRole(): string     { return $_SESSION['admin_role']     ?? 'admin'; }
function adminId(): int          { return (int)($_SESSION['admin_id'] ?? 0); }

function isSuperAdmin(): bool { return adminRole() === 'superadmin'; }

// Flash messages
function flash(string $type, string $msg): void {
    startAdminSession();
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    startAdminSession();
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
