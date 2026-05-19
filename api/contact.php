<?php
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/mailer.php';

$data    = json_decode(file_get_contents('php://input'), true) ?? [];
$name    = trim($data['name']    ?? '');
$phone   = trim($data['phone']   ?? '');
$email   = trim($data['email']   ?? '');
$subject = trim($data['subject'] ?? '');
$message = trim($data['message'] ?? '');

// Validate
$errors = [];
if (!$name)    $errors[] = 'Full name is required.';
if (!$email)   $errors[] = 'Email address is required.';
if (!$message) $errors[] = 'Message is required.';
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
    exit;
}

$db = getDB();
$db->prepare('INSERT INTO contact_messages (name, phone, email, subject, message) VALUES (?,?,?,?,?)')
   ->execute([
       htmlspecialchars($name),
       htmlspecialchars($phone) ?: null,
       htmlspecialchars($email),
       htmlspecialchars($subject) ?: null,
       htmlspecialchars($message),
   ]);

// Notify admin by email
$adminEmail = getSetting('admin_notify_email') ?: getSetting('store_email');
if ($adminEmail) {
    $subjectLine = $subject ? "[$subject]" : '[Contact Form]';
    $html = '
    <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto">
      <div style="background:#d4920a;padding:20px 28px;border-radius:8px 8px 0 0">
        <h2 style="color:#fff;margin:0;font-size:18px">New Contact Message</h2>
      </div>
      <div style="background:#f9f9f9;padding:28px;border:1px solid #e5e5e5;border-top:none;border-radius:0 0 8px 8px">
        <table style="width:100%;border-collapse:collapse;font-size:14px">
          <tr><td style="padding:6px 0;color:#888;width:90px">Name</td><td style="padding:6px 0;font-weight:600">' . htmlspecialchars($name) . '</td></tr>
          <tr><td style="padding:6px 0;color:#888">Email</td><td style="padding:6px 0"><a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a></td></tr>
          ' . ($phone ? '<tr><td style="padding:6px 0;color:#888">Phone</td><td style="padding:6px 0">' . htmlspecialchars($phone) . '</td></tr>' : '') . '
          ' . ($subject ? '<tr><td style="padding:6px 0;color:#888">Subject</td><td style="padding:6px 0">' . htmlspecialchars($subject) . '</td></tr>' : '') . '
        </table>
        <hr style="border:none;border-top:1px solid #e5e5e5;margin:16px 0">
        <p style="color:#444;line-height:1.7;margin:0">' . nl2br(htmlspecialchars($message)) . '</p>
      </div>
    </div>';
    sendMail($adminEmail, 'Genex Admin', 'New Contact Message ' . $subjectLine . ' from ' . $name, $html);
}

echo json_encode(['success' => true]);
