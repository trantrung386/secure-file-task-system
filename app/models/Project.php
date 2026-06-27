<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model xử lý dữ liệu dự án và thành viên dự án.
 */
class Project extends BaseModel
{
    /**
     * Lấy danh sách dự án theo vai trò người dùng.
     */
    public function forUser(array $user): array
    {
        if ($user['role'] === 'admin') {
            return $this->all();
        }

        if ($user['role'] === 'manager') {
            $stmt = $this->db->prepare(
                'SELECT DISTINCT p.*, u.name AS creator_name
                 FROM du_an p
                 LEFT JOIN tai_khoan u ON u.id = p.created_by
                 LEFT JOIN thanh_vien_du_an pm ON pm.project_id = p.id
                 WHERE p.created_by = :uid OR pm.user_id = :uid
                 ORDER BY p.created_at DESC'
            );
            $stmt->execute(['uid' => $user['id']]);
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare(
            'SELECT p.*, u.name AS creator_name
             FROM du_an p
             INNER JOIN thanh_vien_du_an pm ON pm.project_id = p.id AND pm.user_id = :uid
             LEFT JOIN tai_khoan u ON u.id = p.created_by
             ORDER BY p.created_at DESC'
        );
        $stmt->execute(['uid' => $user['id']]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy toàn bộ dự án.
     */
    public function all(): array
    {
        $sql = 'SELECT p.*, u.name AS creator_name
                FROM du_an p
                LEFT JOIN tai_khoan u ON u.id = p.created_by
                ORDER BY p.created_at DESC';
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Tìm một dự án theo ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.name AS creator_name
             FROM du_an p
             LEFT JOIN tai_khoan u ON u.id = p.created_by
             WHERE p.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Tạo dự án mới với mã tự sinh theo mẫu TTW-{NĂM}-{STT}.
     */
    public function create(array $data): int
    {
        $year = (int) date('Y');
        $code = $this->generateNextCode($year);

        $stmt = $this->db->prepare(
            'INSERT INTO du_an(code, name, description, start_date, end_date, priority, status, created_by, created_at)
             VALUES(:code, :name, :description, :start_date, :end_date, :priority, :status, :created_by, NOW())'
        );
        $stmt->execute([
            'code' => $code,
            'name' => $data['name'],
            'description' => $data['description'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'priority' => $data['priority'],
            'status' => $data['status'],
            'created_by' => $data['created_by'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Sinh mã dự án tiếp theo theo năm hiện tại. Ví dụ: TTW-2026-001.
     */
    public function generateNextCode(?int $year = null): string
    {
        $year = $year ?: (int) date('Y');
        $prefix = sprintf('TTW-%d-', $year);

        $stmt = $this->db->prepare(
            'SELECT code
             FROM du_an
             WHERE code LIKE :prefix
             ORDER BY code DESC
             LIMIT 1'
        );
        $stmt->execute(['prefix' => $prefix . '%']);
        $latestCode = (string) ($stmt->fetchColumn() ?: '');

        $nextSequence = 1;
        if ($latestCode !== '' && preg_match('/^TTW-(\d{4})-(\d{3})$/', $latestCode, $matches)) {
            $nextSequence = ((int) $matches[2]) + 1;
        }

        return sprintf('TTW-%d-%03d', $year, $nextSequence);
    }

    /**
     * Cập nhật thông tin dự án nhưng không cho sửa mã dự án.
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE du_an
             SET name = :name, description = :description,
                 start_date = :start_date, end_date = :end_date,
                 priority = :priority, status = :status
             WHERE id = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'priority' => $data['priority'],
            'status' => $data['status'],
        ]);
    }

    /**
     * Xóa dự án.
     */
    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM du_an WHERE id = :id')
            ->execute(['id' => $id]);
    }


    /**
     * Lấy thành viên thuộc dự án.
     */
    public function members(int $projectId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pm.id, pm.project_role, pm.joined_at, u.id AS user_id, u.name, u.email, u.role, u.status
             FROM thanh_vien_du_an pm
             INNER JOIN tai_khoan u ON u.id = pm.user_id
             WHERE pm.project_id = :project_id
             ORDER BY pm.project_role DESC, u.name'
        );
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy người dùng có thể thêm vào dự án.
     */
    public function availableEmployees(int $projectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.name, u.email
             FROM tai_khoan u
             WHERE u.status = 'active'
               AND u.role IN ('manager', 'employee')
               AND NOT EXISTS (
                   SELECT 1 FROM thanh_vien_du_an pm
                   WHERE pm.project_id = :project_id AND pm.user_id = u.id
               )
             ORDER BY u.role DESC, u.name"
        );
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    /**
     * Thêm thành viên vào dự án.
     */
    public function addMember(int $projectId, int $userId, string $projectRole): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO thanh_vien_du_an(project_id, user_id, project_role, joined_at)
             VALUES(:project_id, :user_id, :project_role, NOW())'
        );
        return $stmt->execute([
            'project_id' => $projectId,
            'user_id' => $userId,
            'project_role' => $projectRole,
        ]);
    }

    /**
     * Xóa thành viên khỏi dự án.
     */
    public function removeMember(int $projectId, int $userId): bool
    {
        return $this->db->prepare('DELETE FROM thanh_vien_du_an WHERE project_id = :project_id AND user_id = :user_id')
            ->execute(['project_id' => $projectId, 'user_id' => $userId]);
    }

    /**
     * Kiểm tra người dùng có thuộc dự án hay không.
     */
    public function userBelongsToProject(int $projectId, int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM thanh_vien_du_an WHERE project_id = :project_id AND user_id = :user_id');
        $stmt->execute(['project_id' => $projectId, 'user_id' => $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
