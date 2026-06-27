<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Controller gốc dùng chung cho toàn bộ module.
 */
abstract class BaseController
{
    /**
     * Render view và bọc vào layout chính.
     */
    protected function render(string $view, array $data = [], string $title = APP_NAME): void
    {
        extract($data);
        $viewFile = VIEW_PATH . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            exit('View not found: ' . $view);
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require VIEW_PATH . '/layouts/main.php';
    }

    /**
     * Rút gọn lệnh kiểm tra đăng nhập.
     */
    protected function requireLogin(): void
    {
        Auth::requireLogin();
    }

    /**
     * Rút gọn lệnh kiểm tra quyền truy cập.
     */
    protected function requireRole(array $roles): void
    {
        Auth::requireRole($roles);
    }
}
