<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Model xử lý nghiệp vụ công việc, phân công, duyệt và lịch sử trạng thái.
 */
class Task extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureMultiAssigneeStorage();
        $this->syncLegacyAssignments();
    }

    /**
     * Lấy danh sách công việc theo quyền và bộ lọc.
     */
    public function forUser(array $user, array $filters = []): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = 't.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['project_id'])) {
            $conditions[] = 't.project_id = :project_id';
            $params['project_id'] = (int) $filters['project_id'];
        }

        if ($user['role'] === 'employee') {
            $conditions[] = '(t.assignee_id = :uid OR EXISTS (
                SELECT 1 FROM cong_viec_phu_trach cpt WHERE cpt.task_id = t.id AND cpt.user_id = :uid
            ))';
            $params['uid'] = (int) $user['id'];
        } elseif ($user['role'] === 'manager') {
            $conditions[] = '(t.created_by = :uid OR p.created_by = :uid OR EXISTS (
                SELECT 1 FROM thanh_vien_du_an pm WHERE pm.project_id = t.project_id AND pm.user_id = :uid
            ))';
            $params['uid'] = (int) $user['id'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql = "SELECT t.*, p.name AS project_name, p.code AS project_code,
                       assignee.name AS assignee_name,
                       COALESCE(NULLIF((
                           SELECT GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ', ')
                           FROM cong_viec_phu_trach cpt
                           INNER JOIN tai_khoan u ON u.id = cpt.user_id
                           WHERE cpt.task_id = t.id
                       ), ''), assignee.name) AS assignee_names,
                       creator.name AS creator_name
                FROM cong_viec t
                INNER JOIN du_an p ON p.id = t.project_id
                LEFT JOIN tai_khoan assignee ON assignee.id = t.assignee_id
                LEFT JOIN tai_khoan creator ON creator.id = t.created_by
                {$where}
                ORDER BY t.deadline ASC, t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Tìm một công việc theo ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, p.name AS project_name, p.code AS project_code,
                    assignee.name AS assignee_name,
                    COALESCE(NULLIF((
                        SELECT GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ", ")
                        FROM cong_viec_phu_trach cpt
                        INNER JOIN tai_khoan u ON u.id = cpt.user_id
                        WHERE cpt.task_id = t.id
                    ), ""), assignee.name) AS assignee_names,
                    creator.name AS creator_name
             FROM cong_viec t
             INNER JOIN du_an p ON p.id = t.project_id
             LEFT JOIN tai_khoan assignee ON assignee.id = t.assignee_id
             LEFT JOIN tai_khoan creator ON creator.id = t.created_by
             WHERE t.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Tạo công việc mới.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO cong_viec(project_id, title, description, priority, status, deadline, start_date, assignee_id, created_by, expected_score, review_score, review_comment, approved_at, created_at, updated_at)
             VALUES(:project_id, :title, :description, :priority, 'new', :deadline, NULL, NULL, :created_by, :expected_score, NULL, NULL, NULL, NOW(), NOW())"
        );
        $stmt->execute([
            'project_id' => $data['project_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'deadline' => $data['deadline'],
            'created_by' => $data['created_by'],
            'expected_score' => $data['expected_score'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Gán một hoặc nhiều người phụ trách cho công việc.
     */
    public function assign(int $id, array $data): bool
    {
        $assigneeIds = $this->normalizeAssigneeIds($data['assignee_ids'] ?? []);
        if (!$assigneeIds) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE cong_viec
                 SET start_date = :start_date,
                     deadline = :deadline,
                     status = 'assigned',
                     updated_at = NOW()
                 WHERE id = :id"
            );
            $ok = $stmt->execute([
                'id' => $id,
                'start_date' => $data['start_date'],
                'deadline' => $data['deadline'],
            ]);

            if (!$ok || !$this->syncAssignees($id, $assigneeIds)) {
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Cập nhật trạng thái công việc và ghi lại lịch sử thay đổi.
     */
    public function updateStatus(int $id, string $status, ?string $note, int $userId): bool
    {
        $old = $this->find($id);
        if (!$old) {
            return false;
        }

        $ok = $this->db->prepare('UPDATE cong_viec SET status = :status, updated_at = NOW() WHERE id = :id')
            ->execute(['status' => $status, 'id' => $id]);

        if ($ok) {
            $logStmt = $this->db->prepare(
                'INSERT INTO lich_su_trang_thai_cong_viec(task_id, user_id, old_status, new_status, note, created_at)
                 VALUES(:task_id, :user_id, :old_status, :new_status, :note, NOW())'
            );
            $logStmt->execute([
                'task_id' => $id,
                'user_id' => $userId,
                'old_status' => $old['status'],
                'new_status' => $status,
                'note' => $note,
            ]);
        }

        return $ok;
    }

    /**
     * Lưu kết quả duyệt công việc và thêm log trạng thái.
     */
    public function review(int $id, string $status, ?string $comment, ?int $score, int $userId): bool
    {
        $old = $this->find($id);
        if (!$old) {
            return false;
        }

        $approvedAt = $status === 'approved' ? date('Y-m-d H:i:s') : null;
        $stmt = $this->db->prepare(
            'UPDATE cong_viec
             SET status = :status,
                 review_comment = :review_comment,
                 review_score = :review_score,
                 approved_at = :approved_at,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $ok = $stmt->execute([
            'id' => $id,
            'status' => $status,
            'review_comment' => $comment,
            'review_score' => $score,
            'approved_at' => $approvedAt,
        ]);

        if ($ok) {
            $logStmt = $this->db->prepare(
                'INSERT INTO lich_su_trang_thai_cong_viec(task_id, user_id, old_status, new_status, note, created_at)
                 VALUES(:task_id, :user_id, :old_status, :new_status, :note, NOW())'
            );
            $logStmt->execute([
                'task_id' => $id,
                'user_id' => $userId,
                'old_status' => $old['status'],
                'new_status' => $status,
                'note' => $comment,
            ]);
        }

        return $ok;
    }

    /**
     * Lấy lịch sử thay đổi trạng thái của công việc.
     */
    public function logs(int $taskId): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.name
             FROM lich_su_trang_thai_cong_viec l
             LEFT JOIN tai_khoan u ON u.id = l.user_id
             WHERE l.task_id = :task_id
             ORDER BY l.created_at DESC'
        );
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    /**
     * Danh sách người phụ trách hiện tại của công việc.
     */
    public function assignedUsers(int $taskId): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.name, u.email, u.role
             FROM cong_viec_phu_trach cpt
             INNER JOIN tai_khoan u ON u.id = cpt.user_id
             WHERE cpt.task_id = :task_id
             ORDER BY u.name'
        );
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    /**
     * Kiểm tra người dùng có đang là người phụ trách công việc hay không.
     */
    public function isUserAssigned(int $taskId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM cong_viec_phu_trach
             WHERE task_id = :task_id AND user_id = :user_id'
        );
        $stmt->execute([
            'task_id' => $taskId,
            'user_id' => $userId,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Kiểm tra người dùng có quyền xem/tải công việc và file liên quan hay không.
     */
    public function canAccess(array $user, int $taskId): bool
    {
        $task = $this->find($taskId);
        if (!$task) {
            return false;
        }

        if ($user['role'] === 'admin') {
            return true;
        }

        if ($this->isUserAssigned($taskId, (int) $user['id']) || (int) $task['created_by'] === (int) $user['id']) {
            return true;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM thanh_vien_du_an WHERE project_id = :project_id AND user_id = :user_id');
        $stmt->execute([
            'project_id' => $task['project_id'],
            'user_id' => $user['id'],
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Kiểm tra người dùng hiện tại có được xóa công việc hay không.
     */
    public function canDelete(array $user, int $taskId): bool
    {
        $task = $this->find($taskId);
        if (!$task || !$this->canAccess($user, $taskId)) {
            return false;
        }

        if (!in_array($user['role'] ?? '', ['manager', 'admin'], true)) {
            return false;
        }

        return in_array($task['status'], ['new', 'assigned', 'blocked', 'redo'], true);
    }

    /**
     * Xóa công việc và dọn các file đã lưu trong storage.
     */
    public function deleteTask(int $taskId): bool
    {
        $stmt = $this->db->prepare('SELECT encrypted_path FROM tep_dinh_kem WHERE task_id = :task_id');
        $stmt->execute(['task_id' => $taskId]);
        $paths = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];

        $this->db->beginTransaction();
        try {
            $deleteTask = $this->db->prepare('DELETE FROM cong_viec WHERE id = :id');
            $ok = $deleteTask->execute(['id' => $taskId]);
            if (!$ok) {
                $this->db->rollBack();
                return false;
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }

        foreach ($paths as $filePath) {
            if ($filePath && is_file($filePath)) {
                @unlink($filePath);
            }
        }

        return true;
    }

    /**
     * Tính nhanh các chỉ số tổng quan cho Dashboard.
     */
    public function statsForDashboard(array $user): array
    {
        $cong_viec = $this->forUser($user);
        $stats = [
            'total' => count($cong_viec),
            'approved' => 0,
            'submitted' => 0,
            'overdue' => 0,
        ];

        $today = date('Y-m-d');
        foreach ($cong_viec as $task) {
            if ($task['status'] === 'approved') {
                $stats['approved']++;
            }
            if ($task['status'] === 'submitted') {
                $stats['submitted']++;
            }
            if ($task['deadline'] < $today && !in_array($task['status'], ['approved'], true)) {
                $stats['overdue']++;
            }
        }

        return $stats;
    }

    private function normalizeAssigneeIds(array $userIds): array
    {
        $normalized = [];
        foreach ($userIds as $userId) {
            $value = (int) $userId;
            if ($value > 0) {
                $normalized[$value] = $value;
            }
        }

        return array_values($normalized);
    }

    private function syncAssignees(int $taskId, array $userIds): bool
    {
        $primaryAssigneeId = $userIds[0] ?? null;

        $deleteStmt = $this->db->prepare('DELETE FROM cong_viec_phu_trach WHERE task_id = :task_id');
        if (!$deleteStmt->execute(['task_id' => $taskId])) {
            return false;
        }

        $insertStmt = $this->db->prepare(
            'INSERT INTO cong_viec_phu_trach(task_id, user_id, created_at)
             VALUES(:task_id, :user_id, NOW())'
        );
        foreach ($userIds as $userId) {
            if (!$insertStmt->execute(['task_id' => $taskId, 'user_id' => $userId])) {
                return false;
            }
        }

        $updatePrimaryStmt = $this->db->prepare(
            'UPDATE cong_viec SET assignee_id = :assignee_id WHERE id = :id'
        );

        return $updatePrimaryStmt->execute([
            'id' => $taskId,
            'assignee_id' => $primaryAssigneeId,
        ]);
    }

    private function ensureMultiAssigneeStorage(): void
    {
        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS cong_viec_phu_trach (
                id INT(11) NOT NULL AUTO_INCREMENT,
                task_id INT(11) NOT NULL,
                user_id INT(11) NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_task_user (task_id, user_id),
                KEY idx_task_id (task_id),
                KEY idx_user_id (user_id),
                CONSTRAINT fk_cpt_task FOREIGN KEY (task_id) REFERENCES cong_viec (id) ON DELETE CASCADE,
                CONSTRAINT fk_cpt_user FOREIGN KEY (user_id) REFERENCES tai_khoan (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci'
        );
    }

    private function syncLegacyAssignments(): void
    {
        $this->db->exec(
            'INSERT IGNORE INTO cong_viec_phu_trach(task_id, user_id, created_at)
             SELECT id, assignee_id, NOW()
             FROM cong_viec
             WHERE assignee_id IS NOT NULL'
        );
    }
}
