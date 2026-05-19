<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$id = (int)(json_decode(file_get_contents('php://input'), true)['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$db = getDB();
$db->prepare('UPDATE categories SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);

$stmt = $db->prepare('SELECT is_active FROM categories WHERE id = ?');
$stmt->execute([$id]);
$newState = (int)$stmt->fetchColumn();

echo json_encode(['success' => true, 'is_active' => $newState]);
