<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/mailer.php';
require_once dirname(__DIR__) . '/includes/email-templates.php';

// Parse body 
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request format.']);
    exit;
}

// Validate 
$name    = trim($data['name']    ?? '');
$phone   = trim($data['phone']   ?? '');
$email   = trim($data['email']   ?? '');
$address = trim($data['address'] ?? '');
$notes   = trim($data['notes']   ?? '');
$items   = $data['cart']         ?? [];

$errors = [];
if (!$name)         $errors[] = 'Full name is required.';
if (!$phone)        $errors[] = 'Phone number is required.';
if (empty($items))  $errors[] = 'Your cart is empty.';
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
    exit;
}

// Sanitise items & calculate totals 
$cleanItems = [];
$subtotal   = 0.0;

foreach ($items as $item) {
    $qty   = max(1, min(100, (int)($item['qty']   ?? 1)));
    $price = abs((float)($item['price'] ?? 0));

    $cleanItems[] = [
        'name'     => htmlspecialchars(strip_tags(trim($item['name'] ?? 'Unknown'))),
        'category' => htmlspecialchars(strip_tags(trim($item['category'] ?? ''))),
        'price'    => $price,
        'qty'      => $qty,
    ];
    $subtotal += $price * $qty;
}

$freeMin  = (float)getSetting('free_delivery_min', '50000');
$delivery = 0.0;
$total    = $subtotal + $delivery;

// Save order 
$db          = getDB();
$orderNumber = generateOrderNumber();

try {
    $db->prepare(
        'INSERT INTO orders
         (order_number, customer_name, customer_phone, customer_email, customer_address,
          items_json, subtotal, delivery_charge, total, notes, source, status)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
    )->execute([
        $orderNumber,
        htmlspecialchars($name),
        htmlspecialchars($phone),
        $email ?: null,
        htmlspecialchars($address),
        json_encode($cleanItems, JSON_UNESCAPED_UNICODE),
        $subtotal,
        $delivery,
        $total,
        htmlspecialchars($notes),
        'website',
        'pending',
    ]);

    // Upsert customer record (unique on phone)
    $db->prepare(
        'INSERT INTO customers (name, phone, email)
         VALUES (?,?,?)
         ON DUPLICATE KEY UPDATE
           name  = VALUES(name),
           email = COALESCE(VALUES(email), email)'
    )->execute([
        htmlspecialchars($name),
        htmlspecialchars($phone),
        $email ?: null,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not save your order. Please try again.']);
    exit;
}

// Send emails 
$order = [
    'order_number'     => $orderNumber,
    'customer_name'    => $name,
    'customer_phone'   => $phone,
    'customer_email'   => $email,
    'customer_address' => $address,
    'subtotal'         => $subtotal,
    'delivery_charge'  => $delivery,
    'total'            => $total,
    'notes'            => $notes,
    'created_at'       => date('Y-m-d H:i:s'),
];

// Customer confirmation
if ($email) {
    $html = emailOrderConfirmation($order, $cleanItems);
    sendMail($email, $name, 'Order Confirmed – ' . $orderNumber . ' | Genex', $html);
}

// Admin notification
$adminEmail = getSetting('admin_notify_email');
if ($adminEmail) {
    $html = emailAdminNewOrder($order, $cleanItems);
    sendMail($adminEmail, 'Genex Admin', 'New Order: ' . $orderNumber . ' from ' . $name, $html);
}

echo json_encode([
    'success'      => true,
    'order_number' => $orderNumber,
    'total'        => $total,
]);
