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

        $filters = $this->sanitizeKpiFilters($_GET, $user);

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

        $filters = $this->sanitizeKpiFilters($_GET, $user);

        $service = new KpiService();
        $rows = $user['role'] === 'employee'
            ? $service->report((int) $user['id'], $filters)
            : $service->report(null, $filters);

        header('Content-Type: application/json');
        echo json_encode(['rows' => $rows]);
        exit;
    }

    private function sanitizeKpiFilters(array $get, array $user): array
    {
        $projectId = \App\Core\Validator::validateId($get['project_id'] ?? null);
        $fromDate = trim((string) ($get['from_date'] ?? ''));
        $toDate = trim((string) ($get['to_date'] ?? ''));

        $filters = [
            'project_id' => $projectId !== null ? (string) $projectId : '',
            'from_date' => $this->isValidDate($fromDate) ? $fromDate : '',
            'to_date' => $this->isValidDate($toDate) ? $toDate : '',
        ];

        if ($user['role'] !== 'employee') {
            $employeeId = \App\Core\Validator::validateId($get['employee_id'] ?? null);
            $filters['employee_id'] = $employeeId !== null ? (string) $employeeId : '';
        }

        return $filters;
    }

    private function isValidDate(string $date): bool
    {
        if ($date === '') {
            return false;
        }
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
