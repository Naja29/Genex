<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db     = getDB();
$count  = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$latest = $db->query(
    "SELECT id, order_number, customer_name, total, created_at
     FROM orders ORDER BY id DESC LIMIT 1"
)->fetch();

echo json_encode([
    'pending_count' => $count,
    'latest_id'     => $latest ? (int)$latest['id'] : 0,
    'latest'        => $latest ? [
        'id'            => (int)$latest['id'],
        'order_number'  => $latest['order_number'],
        'customer_name' => $latest['customer_name'],
        'total'         => (float)$latest['total'],
        'created_at'    => $latest['created_at'],
    ] : null,
]);
