<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$id     = (int)($body['id'] ?? 0);
$status = $body['status'] ?? '';
$valid  = ['pending', 'confirmed', 'processing', 'dispatched', 'delivered', 'cancelled'];

if (!$id || !in_array($status, $valid, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

$db = getDB();
$db->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);

echo json_encode(['success' => true, 'status' => $status]);
