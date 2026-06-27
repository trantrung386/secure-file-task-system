<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Log;

/**
 * Controller quản lý màn hình nhật ký truy cập cho Admin.
 */
class LogController extends BaseController
{
    /**
     * Hiển thị danh sách nhật ký gần đây để phục vụ báo cáo và kiểm tra truy vết.
     */
    public function index(): void
    {
        $this->requireRole(['admin']);
        $logs = (new Log())->recent(100);
        $this->render('nhat_ky/index', ['logs' => $logs], 'Nhật ký truy cập');
    }
}
