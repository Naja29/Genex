<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }

$id = (int)(json_decode(file_get_contents('php://input'), true)['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['error' => 'Invalid ID']); exit; }

$db = getDB();

$stmt = $db->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
$stmt->execute([$id]);
if ((int)$stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'error' => 'Cannot delete — this category has products. Reassign them first.']);
    exit;
}

$db->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
echo json_encode(['success' => true, 'message' => 'Category deleted']);
