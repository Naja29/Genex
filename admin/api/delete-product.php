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

// Remove gallery image files
$img = $db->prepare('SELECT image_path FROM product_images WHERE product_id = ?');
$img->execute([$id]);
foreach ($img->fetchAll() as $row) {
    $p = ROOT_PATH . $row['image_path'];
    if (file_exists($p)) unlink($p);
}

// Remove thumbnail file
$thumbRow = $db->prepare('SELECT thumbnail FROM products WHERE id = ?');
$thumbRow->execute([$id]);
$t = $thumbRow->fetchColumn();
if ($t && file_exists(ROOT_PATH . $t)) unlink(ROOT_PATH . $t);

$db->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);

echo json_encode(['success' => true]);
