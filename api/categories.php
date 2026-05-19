<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
header('Cache-Control: public, max-age=300');

$db   = getDB();
$rows = $db->query('
    SELECT id, name, slug, icon
    FROM categories
    WHERE is_active = 1
    ORDER BY sort_order ASC, name ASC
')->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows);
