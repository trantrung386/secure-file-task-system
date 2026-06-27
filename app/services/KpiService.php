<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Service tính điểm KPI cho nhân viên.
 */
class KpiService
{
    /**
     * Tạo báo cáo KPI cho một nhân viên hoặc toàn bộ nhân viên.
     */
    public function report(?int $userId = null, array $filters = []): array
    {
        $db = Database::connection();
        $db->exec(
            'CREATE TABLE IF NOT EXISTS cong_viec_phu_trach (
                id INT(11) NOT NULL AUTO_INCREMENT,
                task_id INT(11) NOT NULL,
                user_id INT(11) NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_task_user (task_id, user_id),
                KEY idx_task_id (task_id),
                KEY idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci'
        );
        $db->exec(
            'INSERT IGNORE INTO cong_viec_phu_trach(task_id, user_id, created_at)
             SELECT id, assignee_id, NOW() FROM cong_viec WHERE assignee_id IS NOT NULL'
        );

        $sql = "SELECT u.id, u.name, u.email,
                       t.id AS task_id, t.status, t.deadline, t.approved_at, t.review_score
                FROM tai_khoan u
                LEFT JOIN cong_viec_phu_trach cpt ON cpt.user_id = u.id
                LEFT JOIN cong_viec t ON t.id = cpt.task_id
                WHERE u.role = 'employee'";

        $params = [];
        if ($userId !== null) {
            $sql .= ' AND u.id = :user_id';
            $params['user_id'] = $userId;
        }

        if (!empty($filters['employee_id'])) {
            $sql .= ' AND u.id = :employee_id';
            $params['employee_id'] = (int) $filters['employee_id'];
        }

        if (!empty($filters['project_id'])) {
            $sql .= ' AND t.project_id = :project_id';
            $params['project_id'] = (int) $filters['project_id'];
        }

        $fromDate = $this->normalizeDate($filters['from_date'] ?? '');
        $toDate = $this->normalizeDate($filters['to_date'] ?? '');
        if ($fromDate !== null) {
            $sql .= ' AND t.deadline >= :from_date';
            $params['from_date'] = $fromDate;
        }
        if ($toDate !== null) {
            $sql .= ' AND t.deadline <= :to_date';
            $params['to_date'] = $toDate;
        }

        $sql .= ' ORDER BY u.name';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
        $today = date('Y-m-d');
        foreach ($rows as $row) {
            $uid = (int) $row['id'];
            if (!isset($grouped[$uid])) {
                $grouped[$uid] = [
                    'id' => $uid,
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'total_tasks' => 0,
                    'approved_tasks' => 0,
                    'redo_tasks' => 0,
                    'overdue_tasks' => 0,
                    'on_time_tasks' => 0,
                    'approved_late_tasks' => 0,
                    'review_points' => 0,
                    '_task_ids' => [],
                ];
            }

            if (empty($row['task_id'])) {
                continue;
            }

            $taskId = (int) $row['task_id'];
            if (isset($grouped[$uid]['_task_ids'][$taskId])) {
                continue;
            }
            $grouped[$uid]['_task_ids'][$taskId] = true;
            $grouped[$uid]['total_tasks']++;

            if ($row['status'] === 'approved') {
                $grouped[$uid]['approved_tasks']++;
            }
            if ($row['status'] === 'redo') {
                $grouped[$uid]['redo_tasks']++;
            }

            $deadline = $row['deadline'] ?? null;
            $approvedAt = !empty($row['approved_at']) ? date('Y-m-d', strtotime($row['approved_at'])) : null;

            if ($row['status'] === 'approved' && $approvedAt !== null) {
                if ($approvedAt <= $deadline) {
                    $grouped[$uid]['on_time_tasks']++;
                } else {
                    $grouped[$uid]['approved_late_tasks']++;
                }
            } elseif ($deadline !== null && $deadline < $today) {
                $grouped[$uid]['overdue_tasks']++;
            }

            $grouped[$uid]['review_points'] += (int) ($row['review_score'] ?? 0);
        }

        $result = array_values($grouped);
        foreach ($result as &$row) {
            unset($row['_task_ids']);

            $completionRate = $row['total_tasks'] > 0
                ? $row['approved_tasks'] / $row['total_tasks']
                : 0.0;
            $onTimeRate = $row['total_tasks'] > 0
                ? $row['on_time_tasks'] / $row['total_tasks']
                : 0.0;
            $reviewRate = $row['approved_tasks'] > 0
                ? min(1.0, (float) $row['review_points'] / (20 * $row['approved_tasks']))
                : 0.0;
            $issueRate = $row['total_tasks'] > 0
                ? min(1.0, ($row['approved_late_tasks'] + $row['overdue_tasks'] + $row['redo_tasks']) / $row['total_tasks'])
                : 0.0;

            $score = round(100 * (
                0.35 * $completionRate
              + 0.30 * $onTimeRate
              + 0.25 * $reviewRate
              + 0.10 * (1.0 - $issueRate)
            ));

            $row['kpi_score'] = max(0, min(100, (int) $score));
        }

        return $result;
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
}

