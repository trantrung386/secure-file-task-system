<?php
declare(strict_types=1);

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'data');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('APP_NAME', 'TruTo Work');
define('APP_URL', getenv('APP_URL') ?: '');
define('APP_KEY', getenv('APP_KEY') ?: 'taskflow-demo-secret-key-2026');
define('MAX_UPLOAD_BYTES', 20 * 1024 * 1024); // 20MB

date_default_timezone_set('Asia/Ho_Chi_Minh');
