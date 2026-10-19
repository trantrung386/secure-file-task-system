<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model quản lý thông báo hệ thống và thông báo từ Quản lý/Trưởng phòng.
 */
class Notification extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTables();
    }

    /**
     * Đếm số lượng thông báo chưa đọc của người dùng.
     */
    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM thong_bao_nguoi_nhan r
             INNER JOIN thong_bao n ON n.id = r.notification_id
             WHERE r.user_id = :user_id AND r.is_read = 0'
        );
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Lấy danh sách thông báo mà người dùng nhận được (dành cho Nhân viên xem thông báo & hộp thư cá nhân).
     */
    public function forRecipient(int $userId, array $filters = [], ?int $limit = null): array
    {
        $conditions = ['r.user_id = :user_id'];
        $params = ['user_id' => $userId];

        if (isset($filters['read_status']) && $filters['read_status'] !== '') {
            if ($filters['read_status'] === 'unread') {
                $conditions[] = 'r.is_read = 0';
            } elseif ($filters['read_status'] === 'read') {
                $conditions[] = 'r.is_read = 1';
            }
        }

        if (!empty($filters['type'])) {
            $conditions[] = 'n.type = :type';
            $params['type'] = (string) $filters['type'];
        }

        if (!empty($filters['project_id'])) {
            $conditions[] = 'n.project_id = :project_id';
            $params['project_id'] = (int) $filters['project_id'];
        }

        if (!empty($filters['keyword'])) {
            $kw = '%' . trim((string) $filters['keyword']) . '%';
            $conditions[] = '(n.title LIKE :kw_title OR n.content LIKE :kw_content)';
            $params['kw_title'] = $kw;
            $params['kw_content'] = $kw;
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);
        $limitSql = $limit !== null && $limit > 0 ? ' LIMIT ' . (int) $limit : '';

        $sql = "SELECT n.*,
                       r.is_read,
                       r.read_at,
                       u.name AS sender_name,
                       u.role AS sender_role,
                       p.name AS project_name,
                       p.code AS project_code,
                       t.title AS task_title
                FROM thong_bao_nguoi_nhan r
                INNER JOIN thong_bao n ON n.id = r.notification_id
                LEFT JOIN tai_khoan u ON u.id = n.created_by
                LEFT JOIN du_an p ON p.id = n.project_id
                LEFT JOIN cong_viec t ON t.id = n.task_id
                {$where}
                ORDER BY r.is_read ASC, n.created_at DESC, n.id DESC{$limitSql}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách thông báo để Quản lý/Trưởng phòng hoặc Admin quản lý.
     * Manager quản lý thông báo do mình tạo hoặc thuộc dự án mình tham gia; Admin quản lý toàn bộ.
     */
    public function managedByUser(array $user, array $filters = []): array
    {
        $conditions = [];
        $params = [];

        if (($user['role'] ?? '') !== 'admin') {
            $conditions[] = '(n.created_by = :uid1 OR (n.project_id IS NOT NULL AND EXISTS (
                SELECT 1
                FROM du_an p_acc
                LEFT JOIN thanh_vien_du_an pm_acc ON pm_acc.project_id = p_acc.id
                WHERE p_acc.id = n.project_id
                  AND (p_acc.created_by = :uid2 OR pm_acc.user_id = :uid3)
            )))';
            $params['uid1'] = (int) $user['id'];
            $params['uid2'] = (int) $user['id'];
            $params['uid3'] = (int) $user['id'];
        }

        if (!empty($filters['project_id'])) {
            $conditions[] = 'n.project_id = :project_id';
            $params['project_id'] = (int) $filters['project_id'];
        }

        if (!empty($filters['type'])) {
            $conditions[] = 'n.type = :type';
            $params['type'] = (string) $filters['type'];
        }

        if (!empty($filters['keyword'])) {
            $kw = '%' . trim((string) $filters['keyword']) . '%';
            $conditions[] = '(n.title LIKE :kw_title OR n.content LIKE :kw_content)';
            $params['kw_title'] = $kw;
            $params['kw_content'] = $kw;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT n.*,
                       u.name AS sender_name,
                       p.name AS project_name,
                       p.code AS project_code,
                       t.title AS task_title,
                       (SELECT COUNT(*) FROM thong_bao_nguoi_nhan r WHERE r.notification_id = n.id) AS total_recipients,
                       (SELECT COUNT(*) FROM thong_bao_nguoi_nhan r WHERE r.notification_id = n.id AND r.is_read = 1) AS read_count,
                       (SELECT GROUP_CONCAT(DISTINCT tk.name ORDER BY tk.name SEPARATOR ', ')
                        FROM thong_bao_nguoi_nhan r
                        INNER JOIN tai_khoan tk ON tk.id = r.user_id
                        WHERE r.notification_id = n.id) AS recipient_names
                FROM thong_bao n
                LEFT JOIN tai_khoan u ON u.id = n.created_by
                LEFT JOIN du_an p ON p.id = n.project_id
                LEFT JOIN cong_viec t ON t.id = n.task_id
                {$where}
                ORDER BY n.created_at DESC, n.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Tìm một thông báo theo ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT n.*,
                    u.name AS sender_name,
                    p.name AS project_name,
                    p.code AS project_code,
                    t.title AS task_title
             FROM thong_bao n
             LEFT JOIN tai_khoan u ON u.id = n.created_by
             LEFT JOIN du_an p ON p.id = n.project_id
             LEFT JOIN cong_viec t ON t.id = n.task_id
             WHERE n.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $recStmt = $this->db->prepare('SELECT user_id FROM thong_bao_nguoi_nhan WHERE notification_id = :id');
        $recStmt->execute(['id' => $id]);
        $row['recipient_ids'] = array_map('intval', $recStmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);

        return $row;
    }

    /**
     * Kiểm tra quyền quản lý (sửa/xóa) một thông báo.
     */
    public function canManage(array $user, array $notification): bool
    {
        $role = (string) ($user['role'] ?? '');
        if ($role === 'admin') {
            return true;
        }
        if ($role !== 'manager') {
            return false;
        }

        if ((int) ($notification['created_by'] ?? 0) === (int) $user['id']) {
            return true;
        }

        $projectId = (int) ($notification['project_id'] ?? 0);
        if ($projectId > 0) {
            $projectModel = new Project();
            $project = $projectModel->find($projectId);
            if ($project && ((int) $project['created_by'] === (int) $user['id'] || $projectModel->userBelongsToProject($projectId, (int) $user['id']))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tạo thông báo mới và gán danh sách người nhận.
     */
    public function createNotification(array $data, array $recipientIds): int|false
    {
        $recipientIds = $this->normalizeUserIds($recipientIds);
        if (!$recipientIds) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO thong_bao(title, content, type, scope, project_id, task_id, link_url, created_by, created_at, updated_at)
                 VALUES(:title, :content, :type, :scope, :project_id, :task_id, :link_url, :created_by, NOW(), NOW())'
            );
            $ok = $stmt->execute([
                'title' => $data['title'],
                'content' => $data['content'],
                'type' => $data['type'] ?? 'manual',
                'scope' => $data['scope'] ?? 'project',
                'project_id' => !empty($data['project_id']) ? (int) $data['project_id'] : null,
                'task_id' => !empty($data['task_id']) ? (int) $data['task_id'] : null,
                'link_url' => $data['link_url'] ?? null,
                'created_by' => (int) $data['created_by'],
            ]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            $notificationId = (int) $this->db->lastInsertId();
            $this->syncRecipients($notificationId, $recipientIds);

            $this->db->commit();
            return $notificationId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Cập nhật nội dung và người nhận của thông báo.
     */
    public function updateNotification(int $id, array $data, array $recipientIds): bool
    {
        $recipientIds = $this->normalizeUserIds($recipientIds);
        if (!$recipientIds) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'UPDATE thong_bao
                 SET title = :title,
                     content = :content,
                     scope = :scope,
                     project_id = :project_id,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $ok = $stmt->execute([
                'id' => $id,
                'title' => $data['title'],
                'content' => $data['content'],
                'scope' => $data['scope'] ?? 'project',
                'project_id' => !empty($data['project_id']) ? (int) $data['project_id'] : null,
            ]);

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            $this->syncRecipients($id, $recipientIds, true);
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
     * Xóa thông báo.
     */
    public function deleteNotification(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM thong_bao WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Đánh dấu một thông báo là đã đọc đối với người dùng.
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE thong_bao_nguoi_nhan
             SET is_read = 1, read_at = COALESCE(read_at, NOW())
             WHERE notification_id = :notification_id AND user_id = :user_id'
        );
        return $stmt->execute([
            'notification_id' => $notificationId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Đánh dấu tất cả thông báo của người dùng là đã đọc.
     */
    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE thong_bao_nguoi_nhan
             SET is_read = 1, read_at = COALESCE(read_at, NOW())
             WHERE user_id = :user_id AND is_read = 0'
        );
        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Gửi thông báo tự động khi có sự kiện công việc / dự án.
     */
    public function notifyEvent(
        array $recipientIds,
        int $actorId,
        string $title,
        string $content,
        string $type,
        ?int $projectId = null,
        ?int $taskId = null,
        ?string $linkUrl = null
    ): void {
        // Loại bỏ người tự thao tác khỏi danh sách nhận tự động (nếu có người nhận khác)
        $filtered = array_values(array_filter(
            $this->normalizeUserIds($recipientIds),
            static fn (int $uid): bool => $uid !== $actorId
        ));

        if (!$filtered) {
            return;
        }

        $this->createNotification([
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'scope' => $projectId !== null ? 'project' : 'user',
            'project_id' => $projectId,
            'task_id' => $taskId,
            'link_url' => $linkUrl,
            'created_by' => $actorId,
        ], $filtered);
    }

    private function normalizeUserIds(array $userIds): array
    {
        $normalized = [];
        foreach ($userIds as $id) {
            $val = (int) $id;
            if ($val > 0) {
                $normalized[$val] = $val;
            }
        }
        return array_values($normalized);
    }

    private function syncRecipients(int $notificationId, array $recipientIds, bool $preserveReadStatus = false): void
    {
        if (!$preserveReadStatus) {
            $del = $this->db->prepare('DELETE FROM thong_bao_nguoi_nhan WHERE notification_id = :id');
            $del->execute(['id' => $notificationId]);
        } else {
            $placeholders = implode(',', array_fill(0, count($recipientIds), '?'));
            $del = $this->db->prepare(
                "DELETE FROM thong_bao_nguoi_nhan WHERE notification_id = ? AND user_id NOT IN ({$placeholders})"
            );
            $del->execute(array_merge([$notificationId], $recipientIds));
        }

        $ins = $this->db->prepare(
            'INSERT IGNORE INTO thong_bao_nguoi_nhan(notification_id, user_id, is_read, read_at, created_at)
             VALUES(:notification_id, :user_id, 0, NULL, NOW())'
        );
        foreach ($recipientIds as $uid) {
            $ins->execute([
                'notification_id' => $notificationId,
                'user_id' => $uid,
            ]);
        }
    }

    private function ensureTables(): void
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS thong_bao (
                id INT(11) NOT NULL AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                type VARCHAR(50) NOT NULL DEFAULT 'manual',
                scope VARCHAR(30) NOT NULL DEFAULT 'project',
                project_id INT(11) DEFAULT NULL,
                task_id INT(11) DEFAULT NULL,
                link_url VARCHAR(255) DEFAULT NULL,
                created_by INT(11) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY idx_tb_project (project_id),
                KEY idx_tb_task (task_id),
                KEY idx_tb_created_by (created_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"
        );

        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS thong_bao_nguoi_nhan (
                id INT(11) NOT NULL AUTO_INCREMENT,
                notification_id INT(11) NOT NULL,
                user_id INT(11) NOT NULL,
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                read_at DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_notif_user (notification_id, user_id),
                KEY idx_tbnn_user (user_id),
                KEY idx_tbnn_notif (notification_id),
                CONSTRAINT fk_tbnn_notif FOREIGN KEY (notification_id) REFERENCES thong_bao (id) ON DELETE CASCADE,
                CONSTRAINT fk_tbnn_user FOREIGN KEY (user_id) REFERENCES tai_khoan (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"
        );

        try {
            $count = (int) $this->db->query('SELECT COUNT(*) FROM thong_bao')->fetchColumn();
            if ($count === 0) {
                $userExists = (int) $this->db->query('SELECT COUNT(*) FROM tai_khoan WHERE id IN (2, 3, 4)')->fetchColumn();
                if ($userExists >= 2) {
                    $this->createNotification([
                        'title' => 'Thông báo triển khai dự án Website giao việc nội bộ',
                        'content' => 'Đề nghị các thành viên trong dự án cập nhật tiến độ công việc và tải tài liệu báo cáo đã mã hóa E2E đúng hạn.',
                        'type' => 'manual',
                        'scope' => 'project',
                        'project_id' => 1,
                        'task_id' => null,
                        'link_url' => 'index.php?route=du_an/show&id=1',
                        'created_by' => 2,
                    ], [3, 4]);
                }
            }
        } catch (\Throwable $e) {
        }
    }
}
