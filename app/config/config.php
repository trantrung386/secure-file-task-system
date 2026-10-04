<?php
declare(strict_types=1);

use App\Core\Env;

/**
 * Cấu hình hệ thống chung
 * Đọc linh hoạt từ biến môi trường (.env) và có giá trị dự phòng an toàn.
 */

define('DB_HOST', Env::get('DB_HOST', '127.0.0.1'));
define('DB_PORT', Env::get('DB_PORT', '3306'));
define('DB_NAME', Env::get('DB_NAME', 'data'));
define('DB_USER', Env::get('DB_USER', 'root'));
define('DB_PASS', Env::get('DB_PASS', ''));

define('APP_NAME', Env::get('APP_NAME', 'TruTo Work'));
define('APP_URL', Env::get('APP_URL', ''));
define('APP_ENV', Env::get('APP_ENV', 'local'));
define('APP_DEBUG', Env::get('APP_DEBUG', true));

define('MAX_UPLOAD_BYTES', 20 * 1024 * 1024); // 20MB

date_default_timezone_set('Asia/Ho_Chi_Minh');
