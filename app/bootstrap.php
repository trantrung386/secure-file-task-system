<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);
define('STORAGE_PATH', BASE_PATH . '/storage');
define('VIEW_PATH', APP_PATH . '/views');

require APP_PATH . '/config/config.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', '/', $relative);
    $file = APP_PATH . '/' . strtolower(dirname($relativePath)) . '/' . basename($relativePath) . '.php';

    if (!file_exists($file)) {
        $file = APP_PATH . '/' . $relativePath . '.php';
    }

    if (file_exists($file)) {
        require $file;
    }
});

require APP_PATH . '/core/helpers.php';

/**
 * Khởi tạo RSA keypair cho hybrid encryption nếu chưa tồn tại.
 */
try {
    $cryptoService = new \App\Services\FileCryptoService();
    $cryptoService->ensureRsaKeysExist();
} catch (\Throwable $e) {
    error_log('Failed to initialize RSA keys: ' . $e->getMessage());
}
