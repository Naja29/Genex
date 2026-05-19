<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$body  = json_decode(file_get_contents('php://input'), true) ?? [];
$id    = (int)($body['id']    ?? 0);
$field = $body['field'] ?? '';

if (!$id || !in_array($field, ['is_active', 'is_featured'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$db = getDB();
$db->prepare("UPDATE products SET $field = 1 - $field WHERE id = ?")->execute([$id]);

$stmt = $db->prepare("SELECT $field FROM products WHERE id = ?");
$stmt->execute([$id]);
$newVal = (int)$stmt->fetchColumn();

echo json_encode(['success' => true, 'field' => $field, 'value' => $newVal]);
