<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model quản lý metadata của file đính kèm đã mã hóa.
 */
class Attachment extends BaseModel
{
    /**
     * Lấy toàn bộ file thuộc một công việc.
     */
    public function byTask(int $taskId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, u.name AS uploader_name
             FROM tep_dinh_kem a
             LEFT JOIN tai_khoan u ON u.id = a.uploaded_by
             WHERE a.task_id = :task_id
             ORDER BY a.created_at DESC'
        );
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    /**
     * Tìm một file theo ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tep_dinh_kem WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Lưu metadata file sau khi đã mã hóa và ghi ra storage.
     */
    public function create(array $data): bool
    {
        // file_type mặc định là 'employee', có thể override thành 'manager'
        $fileType = $data['file_type'] ?? 'employee';
        
        $stmt = $this->db->prepare(
            'INSERT INTO tep_dinh_kem(task_id, original_name, stored_name, mime_type, file_size, encrypted_path, uploaded_by, file_type, upload_reason, encrypted_key, iv, created_at)
             VALUES(:task_id, :original_name, :stored_name, :mime_type, :file_size, :encrypted_path, :uploaded_by, :file_type, :upload_reason, :encrypted_key, :iv, NOW())'
        );
        
        return $stmt->execute([
            'task_id' => $data['task_id'],
            'original_name' => $data['original_name'],
            'stored_name' => $data['stored_name'],
            'mime_type' => $data['mime_type'],
            'file_size' => $data['file_size'],
            'encrypted_path' => $data['encrypted_path'],
            'uploaded_by' => $data['uploaded_by'],
            'file_type' => $fileType,
            'upload_reason' => $data['upload_reason'] ?? null,
            'encrypted_key' => $data['encrypted_key'] ?? null,
            'iv' => $data['iv'] ?? null,
        ]);
    }
}
