<?php
require_once __DIR__ . '/functions.php';

// Shared wrapper 
function emailWrap(string $body): string
{
    $store   = getSetting('store_name', 'Genex - Global Xperience');
    $phone   = getSetting('store_phone', '+94 77 723 7962');
    $email   = getSetting('store_email', 'genecoretech@gmail.com');
    $address = getSetting('store_address', 'Kamburupitiya, Sri Lanka');
    $year    = date('Y');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>$store</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{background:#f0f0f0;font-family:Arial,Helvetica,sans-serif;color:#222}
  a{color:#d4920a}
  .wrap{max-width:600px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08)}
  .hdr{background:#111;padding:22px 32px;display:flex;align-items:center;gap:12px}
  .hdr-logo{font-size:22px;font-weight:800;color:#d4920a;letter-spacing:-0.5px}
  .hdr-sub{font-size:12px;color:#999;margin-top:2px}
  .hero{background:linear-gradient(135deg,#d4920a,#b8780a);padding:32px;color:#fff;text-align:center}
  .hero h1{font-size:22px;font-weight:700;margin-bottom:6px}
  .hero p{font-size:14px;opacity:.9}
  .body{padding:28px 32px}
  .order-num{background:#f9f9f9;border:1px solid #e8e8e8;border-left:4px solid #d4920a;border-radius:4px;padding:14px 18px;margin-bottom:24px}
  .order-num .label{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#888;margin-bottom:4px}
  .order-num .num{font-size:20px;font-weight:800;color:#111}
  table.items{width:100%;border-collapse:collapse;margin-bottom:20px;font-size:13px}
  table.items th{background:#f5f5f5;padding:10px 12px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:#666;border-bottom:2px solid #e8e8e8}
  table.items td{padding:10px 12px;border-bottom:1px solid #f0f0f0;vertical-align:top}
  table.items tr:last-child td{border-bottom:none}
  .totals{background:#f9f9f9;border-radius:6px;padding:16px 18px;margin-bottom:24px}
  .totals-row{display:flex;justify-content:space-between;font-size:13px;color:#666;margin-bottom:8px}
  .totals-row.total{font-size:16px;font-weight:700;color:#111;border-top:1px solid #e8e8e8;padding-top:10px;margin-top:4px}
  .totals-row.total span:last-child{color:#d4920a}
  .info-box{background:#f9f9f9;border-radius:6px;padding:16px 18px;margin-bottom:20px}
  .info-row{display:flex;gap:10px;font-size:13px;margin-bottom:8px}
  .info-row:last-child{margin-bottom:0}
  .info-label{color:#888;min-width:80px;flex-shrink:0}
  .info-val{color:#222;font-weight:500}
  .cta-box{background:linear-gradient(135deg,#111,#1a1a1a);border-radius:6px;padding:20px;text-align:center;margin-bottom:24px}
  .cta-box p{color:#ccc;font-size:13px;margin-bottom:14px;line-height:1.6}
  .btn-wa{display:inline-block;background:#25d366;color:#fff;text-decoration:none;padding:11px 24px;border-radius:50px;font-weight:700;font-size:13px}
  .notice{background:#fff8e8;border:1px solid #f0d070;border-radius:6px;padding:14px 16px;font-size:12.5px;color:#7a5f00;line-height:1.6;margin-bottom:24px}
  .ftr{background:#f5f5f5;border-top:1px solid #e8e8e8;padding:20px 32px;text-align:center}
  .ftr p{font-size:12px;color:#888;line-height:1.8}
  .ftr a{color:#d4920a;text-decoration:none}
  @media(max-width:600px){.body{padding:20px 18px}.hdr{padding:16px 18px}.hero{padding:24px 18px}}
</style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <div>
      <div class="hdr-logo">GENEX</div>
      <div class="hdr-sub">Global Xperience - Computer Parts &amp; Electronics</div>
    </div>
  </div>
  $body
  <div class="ftr">
    <p>
      <strong>$store</strong><br>
      $address<br>
      <a href="tel:$phone">$phone</a> &nbsp;|&nbsp; <a href="mailto:$email">$email</a>
    </p>
    <p style="margin-top:10px;font-size:11px;color:#aaa">&copy; $year $store. All rights reserved.</p>
  </div>
</div>
</body>
</html>
HTML;
}

// Item rows helper 
function emailItemRows(array $items): string
{
    $rows = '';
    foreach ($items as $item) {
        $qty      = (int)($item['qty'] ?? 1);
        $price    = (float)($item['price'] ?? 0);
        $subtotal = $qty * $price;
        $name     = htmlspecialchars($item['name'] ?? '');
        $cat      = htmlspecialchars($item['category'] ?? '');
        $rows .= "<tr>
            <td><strong>$name</strong>" . ($cat ? "<br><span style='font-size:11px;color:#888'>$cat</span>" : '') . "</td>
            <td style='text-align:center'>$qty</td>
            <td style='text-align:right;color:#666'>Rs. " . number_format($price, 2) . "</td>
            <td style='text-align:right;font-weight:700;color:#d4920a'>Rs. " . number_format($subtotal, 2) . "</td>
        </tr>";
    }
    return $rows;
}

// Customer order confirmation 
function emailOrderConfirmation(array $order, array $items): string
{
    $wa       = 'https://wa.me/' . getSetting('store_whatsapp', '94777237962');
    $rows     = emailItemRows($items);
    $sub      = number_format((float)$order['subtotal'], 2);
    $del      = (float)$order['delivery_charge'];
    $total    = number_format((float)$order['total'], 2);
    $delLine  = $del > 0
        ? "<div class='totals-row'><span>Delivery</span><span>Rs. " . number_format($del, 2) . "</span></div>"
        : "<div class='totals-row'><span>Delivery</span><span style='color:#10b981'>Free</span></div>";
    $addr  = $order['customer_address'] ? '<div class="info-row"><span class="info-label">Address</span><span class="info-val">' . nl2br(htmlspecialchars($order['customer_address'])) . '</span></div>' : '';
    $notes = $order['notes'] ? '<div class="info-row"><span class="info-label">Notes</span><span class="info-val">' . htmlspecialchars($order['notes']) . '</span></div>' : '';
    $date  = date('d M Y, h:i A', strtotime($order['created_at']));
    $on    = htmlspecialchars($order['order_number']);
    $cn    = htmlspecialchars($order['customer_name']);

    $body = <<<HTML
<div class="hero">
  <h1>Thank You, $cn!</h1>
  <p>We've received your order and will contact you shortly to confirm.</p>
</div>
<div class="body">
  <div class="order-num">
    <div class="label">Your Order Number</div>
    <div class="num">$on</div>
    <div style="font-size:11.5px;color:#888;margin-top:4px">Placed on $date</div>
  </div>

  <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;color:#111">Order Summary</h3>
  <table class="items">
    <thead>
      <tr><th>Product</th><th style="text-align:center">Qty</th><th style="text-align:right">Unit Price</th><th style="text-align:right">Subtotal</th></tr>
    </thead>
    <tbody>$rows</tbody>
  </table>

  <div class="totals">
    <div class="totals-row"><span>Subtotal</span><span>Rs. $sub</span></div>
    $delLine
    <div class="totals-row total"><span>Total</span><span>Rs. $total</span></div>
  </div>

  <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;color:#111">Your Details</h3>
  <div class="info-box">
    <div class="info-row"><span class="info-label">Name</span><span class="info-val">{$cn}</span></div>
    <div class="info-row"><span class="info-label">Phone</span><span class="info-val">{$order['customer_phone']}</span></div>
    $addr
    $notes
  </div>

  <div class="cta-box">
    <p>We'll reach out via WhatsApp to confirm stock availability, delivery timeline and final payment details. No online payment needed!</p>
    <a href="$wa" class="btn-wa"><i>💬</i> Chat with us on WhatsApp</a>
  </div>

  <div class="notice">
    <strong>📦 What happens next?</strong><br>
    1. Our team reviews your order<br>
    2. We contact you via WhatsApp within a few hours<br>
    3. We confirm stock, price and delivery<br>
    4. You pay on delivery or as agreed
  </div>
</div>
HTML;

    return emailWrap($body);
}

// Admin new order notification 
function emailAdminNewOrder(array $order, array $items): string
{
    $rows    = emailItemRows($items);
    $sub     = number_format((float)$order['subtotal'], 2);
    $del     = (float)$order['delivery_charge'];
    $total   = number_format((float)$order['total'], 2);
    $delLine = $del > 0
        ? "<div class='totals-row'><span>Delivery</span><span>Rs. " . number_format($del, 2) . "</span></div>"
        : "<div class='totals-row'><span>Delivery</span><span style='color:#10b981'>Free</span></div>";
    $addr  = $order['customer_address'] ? '<div class="info-row"><span class="info-label">Address</span><span class="info-val">' . nl2br(htmlspecialchars($order['customer_address'])) . '</span></div>' : '';
    $notes = $order['notes'] ? '<div class="info-row"><span class="info-label">Notes</span><span class="info-val">' . htmlspecialchars($order['notes']) . '</span></div>' : '';
    $date  = date('d M Y, h:i A', strtotime($order['created_at']));
    $on    = htmlspecialchars($order['order_number']);
    $cn    = htmlspecialchars($order['customer_name']);
    $cp    = htmlspecialchars($order['customer_phone']);
    $wa    = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $order['customer_phone']);
    $adminUrl = getSetting('admin_url', BASE_URL . 'admin/orders.php');

    $body = <<<HTML
<div class="hero">
  <h1>🛍️ New Order Received!</h1>
  <p>A customer just placed an order on your website.</p>
</div>
<div class="body">
  <div class="order-num">
    <div class="label">Order Number</div>
    <div class="num">$on</div>
    <div style="font-size:11.5px;color:#888;margin-top:4px">Placed on $date</div>
  </div>

  <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;color:#111">Customer Details</h3>
  <div class="info-box">
    <div class="info-row"><span class="info-label">Name</span><span class="info-val">$cn</span></div>
    <div class="info-row"><span class="info-label">Phone</span><span class="info-val"><a href="tel:$cp">$cp</a></span></div>
    $addr
    $notes
  </div>

  <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;color:#111">Order Items</h3>
  <table class="items">
    <thead>
      <tr><th>Product</th><th style="text-align:center">Qty</th><th style="text-align:right">Unit Price</th><th style="text-align:right">Subtotal</th></tr>
    </thead>
    <tbody>$rows</tbody>
  </table>

  <div class="totals">
    <div class="totals-row"><span>Subtotal</span><span>Rs. $sub</span></div>
    $delLine
    <div class="totals-row total"><span>Total</span><span>Rs. $total</span></div>
  </div>

  <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px">
    <a href="$wa" style="flex:1;display:block;background:#25d366;color:#fff;text-decoration:none;padding:12px 20px;border-radius:6px;font-weight:700;font-size:13px;text-align:center">
      💬 WhatsApp Customer
    </a>
    <a href="{$adminUrl}" style="flex:1;display:block;background:#d4920a;color:#fff;text-decoration:none;padding:12px 20px;border-radius:6px;font-weight:700;font-size:13px;text-align:center">
      📋 View in Admin Panel
    </a>
  </div>
</div>
HTML;

    return emailWrap($body);
}
