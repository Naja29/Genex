<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
header('Cache-Control: public, max-age=60');

echo json_encode([
    'whatsapp'  => getSetting('store_whatsapp', '94777237962'),
    'phone'     => getSetting('store_phone',    '+94 77 723 7962'),
    'facebook'  => getSetting('facebook_url',   'https://web.facebook.com/genecoretech'),
    'instagram' => getSetting('instagram_url',  ''),
    'youtube'   => getSetting('youtube_url',    ''),
    'tiktok'    => getSetting('tiktok_url',     ''),
]);
