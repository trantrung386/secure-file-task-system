<?php
declare(strict_types=1);

/**
 * Public Web Entry Point (Front Controller)
 * Đóng vai trò là cổng tiếp nhận duy nhất cho tất cả các HTTP Request.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

header('Content-Type: text/html; charset=UTF-8');

$route = $_GET['route'] ?? (current_user() ? 'dashboard/index' : 'home/index');
$route = trim($route, '/');
$parts = array_values(array_filter(explode('/', $route)));

$controllerKey = $parts[0] ?? (current_user() ? 'dashboard' : 'home');
$action = $parts[1] ?? 'index';
$id = $parts[2] ?? ($_GET['id'] ?? null);

if ($id !== null) {
    $validatedId = \App\Core\Validator::validateId($id);
    if ($validatedId === null) {
        http_response_code(400);
        exit('Tham so ID khong hop le.');
    }
    $id = $validatedId;
}

$controllerMap = [
    'home' => \App\Controllers\HomeController::class,
    'auth' => \App\Controllers\AuthController::class,
    'dashboard' => \App\Controllers\DashboardController::class,
    'tai_khoan' => \App\Controllers\UserController::class,
    'du_an' => \App\Controllers\ProjectController::class,
    'cong_viec' => \App\Controllers\TaskController::class,
    'files' => \App\Controllers\FileController::class,
    'crypto' => \App\Controllers\CryptoController::class,
    'kpi' => \App\Controllers\KpiController::class,
    'nhat_ky' => \App\Controllers\LogController::class,
];

if (!isset($controllerMap[$controllerKey])) {
    http_response_code(404);
    exit('Controller not found');
}

$controller = new $controllerMap[$controllerKey]();

if (!method_exists($controller, $action)) {
    http_response_code(404);
    exit('Action not found');
}

if ($id !== null) {
    $controller->$action($id);
} else {
    $controller->$action();
}
