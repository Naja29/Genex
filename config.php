<?php

if (ob_get_level() === 0) ob_start();


date_default_timezone_set('Asia/Colombo'); // UTC+5:30 - matches MySQL SET time_zone='+05:30'

define('BASE_URL',   'http://localhost:8080/genex/');
define('ROOT_PATH',  __DIR__ . DIRECTORY_SEPARATOR);
define('UPLOAD_DIR', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('UPLOAD_URL', BASE_URL . 'uploads/');

// DB credentials
define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    '3307');
define('DB_NAME',    'genex_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// Admin session
define('SESSION_NAME',     'genex_admin');
define('SESSION_LIFETIME', 7200); // 2 hours
