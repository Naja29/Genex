<?php
require_once __DIR__ . '/db.php';

// Formatting 
function fmtPrice(float $n): string {
    return 'Rs. ' . number_format($n, 2);
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 0)      return date('d M Y', strtotime($datetime));
    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('d M Y', strtotime($datetime));
}

function slugify(string $text): string {
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s\-]/', '', $text);
    $text = preg_replace('/[\s\-]+/', '-', $text);
    return trim($text, '-');
}

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Request helpers 
function isPost(): bool  { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
function isAjax(): bool  { return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'; }
function jsonOk(string $msg, array $extra = []): never  { while (ob_get_level() > 0) ob_end_clean(); header('Content-Type: application/json'); echo json_encode(['success' => true,  'message' => $msg] + $extra); exit; }
function jsonErr(string $msg): never                    { while (ob_get_level() > 0) ob_end_clean(); header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => $msg]); exit; }
function post(string $k, string $d = ''): string { return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
function get(string  $k, string $d = ''): string { return isset($_GET[$k])  ? trim($_GET[$k])  : $d; }

function redirect(string $url): never {
    while (ob_get_level() > 0) ob_end_clean();
    header('Location: ' . $url);
    exit;
}

// Settings 
function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $stmt = getDB()->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $cache[$key] = $row !== false ? (string)($row['setting_value'] ?? '') : $default;
    } catch (Throwable) {
        $cache[$key] = $default;
    }
    return $cache[$key];
}

function setSetting(string $key, string $value): void {
    getDB()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?,?)
                      ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
           ->execute([$key, $value]);
}

// Order number 
function generateOrderNumber(): string {
    $year = date('Y');
    $stmt = getDB()->prepare(
        "SELECT MAX(CAST(SUBSTRING_INDEX(order_number, '-', -1) AS UNSIGNED))
         FROM orders WHERE order_number LIKE ?"
    );
    $stmt->execute(["GNX-{$year}-%"]);
    $next = (int)$stmt->fetchColumn() + 1;

    do {
        $num    = 'GNX-' . $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
        $exists = getDB()->prepare('SELECT id FROM orders WHERE order_number = ?');
        $exists->execute([$num]);
        if (!$exists->fetch()) break;
        $next++;
    } while (true);

    return $num;
}

// Image upload 
function uploadImage(array $file, string $subdir = 'products'): ?string {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if ($file['error'] !== UPLOAD_ERR_OK)          return null;
    if (!in_array($file['type'], $allowed, true))  return null;
    if ($file['size'] > 5 * 1024 * 1024)           return null;

    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $name = uniqid('img_', true) . '.' . $ext;
    $dir  = UPLOAD_DIR . $subdir . DIRECTORY_SEPARATOR;

    if (!is_dir($dir)) mkdir($dir, 0755, true);

    if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
        return 'uploads/' . $subdir . '/' . $name;
    }
    return null;
}

// Status badge HTML 
function orderStatusBadge(string $status): string {
    $map = [
        'pending'    => ['#f59e0b', 'Pending'],
        'confirmed'  => ['#3b82f6', 'Confirmed'],
        'processing' => ['#8b5cf6', 'Processing'],
        'dispatched' => ['#06b6d4', 'Dispatched'],
        'delivered'  => ['#10b981', 'Delivered'],
        'cancelled'  => ['#ef4444', 'Cancelled'],
    ];
    [$color, $label] = $map[$status] ?? ['#6b7280', ucfirst($status)];
    return "<span style='background:{$color}22;color:{$color};padding:3px 10px;border-radius:50px;font-size:12px;font-weight:600'>{$label}</span>";
}

// Pagination 
function paginate(int $total, int $perPage, int $current): array {
    $pages = (int)ceil($total / $perPage);
    return [
        'total'    => $total,
        'per_page' => $perPage,
        'current'  => $current,
        'pages'    => $pages,
        'offset'   => ($current - 1) * $perPage,
        'has_prev' => $current > 1,
        'has_next' => $current < $pages,
    ];
}
