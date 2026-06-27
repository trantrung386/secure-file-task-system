<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Log;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\KpiService;

/**
 * Controller hiển thị màn hình tổng quan theo vai trò.
 */
class DashboardController extends BaseController
{
    /**
     * Nạp dữ liệu tổng quan để hiển thị Dashboard.
     */
    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();

        $taskModel = new Task();
        $projectModel = new Project();

        $data = [
            'user' => $user,
            'stats' => $taskModel->statsForDashboard($user),
            'projects' => $projectModel->forUser($user),
            'tasks' => array_slice($taskModel->forUser($user), 0, 5),
            'logs' => $user['role'] === 'admin' ? (new Log())->recent(10) : [],
            'userCount' => $user['role'] === 'admin' ? count((new User())->all()) : null,
            'kpiRows' => $user['role'] === 'employee' ? (new KpiService())->report((int) $user['id']) : [],
        ];

        $this->render('dashboard/index', $data, 'Tổng quan');
    }
}
