<?php
header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }

$id = (int)(json_decode(file_get_contents('php://input'), true)['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['error' => 'Invalid ID']); exit; }

getDB()->prepare('DELETE FROM customers WHERE id = ?')->execute([$id]);
echo json_encode(['success' => true, 'message' => 'Customer deleted']);
