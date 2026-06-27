<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model ghi nhật ký truy cập và thao tác hệ thống.
 */
class Log extends BaseModel
{
    /**
     * Tạo một bản ghi log mới.
     */
    public function create(int|string|null $userId, string $action, string $targetType, int|string|null $targetId, string $description): bool
    {
        $userId = ($userId === null || $userId === '') ? null : (int) $userId;
        $targetId = ($targetId === null || $targetId === '') ? null : (int) $targetId;

        $stmt = $this->db->prepare(
            'INSERT INTO nhat_ky_truy_cap(user_id, action, target_type, target_id, description, created_at)
             VALUES(:user_id, :action, :target_type, :target_id, :description, NOW())'
        );
        return $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'description' => $description,
        ]);
    }

    /**
     * Lấy danh sách log gần nhất để hiển thị cho Admin.
     */
    public function recent(int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.name AS user_name
             FROM nhat_ky_truy_cap l
             LEFT JOIN tai_khoan u ON u.id = l.user_id
             ORDER BY l.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
