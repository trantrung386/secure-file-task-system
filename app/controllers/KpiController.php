<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Project;
use App\Models\User;
use App\Services\KpiService;

/**
 * Controller hiển thị báo cáo KPI cá nhân hoặc toàn hệ thống.
 */
class KpiController extends BaseController
{
    /**
     * Nạp báo cáo KPI theo vai trò người dùng.
     */
    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();

        $filters = [
            'project_id' => $_GET['project_id'] ?? '',
            'from_date' => $_GET['from_date'] ?? '',
            'to_date' => $_GET['to_date'] ?? '',
        ];

        if ($user['role'] !== 'employee') {
            $filters['employee_id'] = $_GET['employee_id'] ?? '';
        }

        $service = new KpiService();
        $rows = $user['role'] === 'employee'
            ? $service->report((int) $user['id'], $filters)
            : $service->report(null, $filters);

        $projects = (new Project())->forUser($user);
        $employees = $user['role'] !== 'employee' ? (new User())->allActiveEmployees() : [];

        $this->render('kpi/index', compact('rows', 'projects', 'employees', 'filters'), 'Báo cáo KPI');
    }

    /**
     * Trả về dữ liệu KPI dạng JSON để vẽ biểu đồ phía client.
     */
    public function data(): void
    {
        $this->requireLogin();
        $user = Auth::user();

        $filters = [
            'project_id' => $_GET['project_id'] ?? '',
            'from_date' => $_GET['from_date'] ?? '',
            'to_date' => $_GET['to_date'] ?? '',
        ];

        if ($user['role'] !== 'employee') {
            $filters['employee_id'] = $_GET['employee_id'] ?? '';
        }

        $service = new KpiService();
        $rows = $user['role'] === 'employee'
            ? $service->report((int) $user['id'], $filters)
            : $service->report(null, $filters);

        header('Content-Type: application/json');
        echo json_encode(['rows' => $rows]);
        exit;
    }
}
