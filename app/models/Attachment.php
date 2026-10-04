<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model quản lý metadata của file đính kèm đã mã hóa.
 */
class Attachment extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureE2EStorage();
    }

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

    public function byProject(int $projectId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, u.name AS uploader_name
             FROM tep_dinh_kem a
             LEFT JOIN tai_khoan u ON u.id = a.uploaded_by
             WHERE a.project_id = :project_id
             ORDER BY a.created_at DESC'
        );
        $stmt->execute(['project_id' => $projectId]);
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
    public function create(array $data): int|false
    {
        // file_type mặc định là 'employee', có thể override thành 'manager'
        $fileType = $data['file_type'] ?? 'employee';
        $moduleName = $data['module_name'] ?? 'cong_viec';
        $storageMode = $data['storage_mode'] ?? 'db';
        
        $stmt = $this->db->prepare(
            'INSERT INTO tep_dinh_kem(task_id, project_id, module_name, original_name, stored_name, mime_type, file_size, encrypted_path, encrypted_blob, storage_mode, uploaded_by, file_type, upload_reason, encrypted_key, iv, created_at)
             VALUES(:task_id, :project_id, :module_name, :original_name, :stored_name, :mime_type, :file_size, :encrypted_path, :encrypted_blob, :storage_mode, :uploaded_by, :file_type, :upload_reason, :encrypted_key, :iv, NOW())'
        );
        
        $stmt->bindValue(':task_id', $data['task_id']);
        $stmt->bindValue(':project_id', $data['project_id'] ?? null);
        $stmt->bindValue(':module_name', $moduleName);
        $stmt->bindValue(':original_name', $data['original_name']);
        $stmt->bindValue(':stored_name', $data['stored_name']);
        $stmt->bindValue(':mime_type', $data['mime_type']);
        $stmt->bindValue(':file_size', $data['file_size']);
        $stmt->bindValue(':encrypted_path', $data['encrypted_path'] ?? '');
        $stmt->bindValue(':encrypted_blob', $data['encrypted_blob'] ?? null, \PDO::PARAM_LOB);
        $stmt->bindValue(':storage_mode', $storageMode);
        $stmt->bindValue(':uploaded_by', $data['uploaded_by']);
        $stmt->bindValue(':file_type', $fileType);
        $stmt->bindValue(':upload_reason', $data['upload_reason'] ?? null);
        $stmt->bindValue(':encrypted_key', $data['encrypted_key'] ?? null);
        $stmt->bindValue(':iv', $data['iv'] ?? null);

        $ok = $stmt->execute();

        return $ok ? (int) $this->db->lastInsertId() : false;
    }

    public function addRecipients(int $attachmentId, array $recipients): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO file_recipients(attachment_id, user_id, encrypted_key, created_at)
             VALUES(:attachment_id, :user_id, :encrypted_key, NOW())
             ON DUPLICATE KEY UPDATE encrypted_key = VALUES(encrypted_key)'
        );

        foreach ($recipients as $recipient) {
            if (empty($recipient['user_id']) || empty($recipient['encrypted_key'])) {
                continue;
            }

            $stmt->execute([
                'attachment_id' => $attachmentId,
                'user_id' => (int) $recipient['user_id'],
                'encrypted_key' => (string) $recipient['encrypted_key'],
            ]);
        }
    }

    public function recipientKey(int $attachmentId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT encrypted_key
             FROM file_recipients
             WHERE attachment_id = :attachment_id AND user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute([
            'attachment_id' => $attachmentId,
            'user_id' => $userId,
        ]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function ensureE2EStorage(): void
    {
        try {
            $this->db->exec('ALTER TABLE tep_dinh_kem MODIFY task_id int(11) DEFAULT NULL');
        } catch (\Throwable $e) {
            // Some MySQL versions require dropping/recreating the FK manually.
        }
        try {
            $this->db->exec('ALTER TABLE tep_dinh_kem MODIFY encrypted_path varchar(255) DEFAULT NULL');
        } catch (\Throwable $e) {
        }
        $stmt = $this->db->query("SHOW COLUMNS FROM tep_dinh_kem LIKE 'project_id'");
        if (!$stmt->fetch()) {
            $this->db->exec('ALTER TABLE tep_dinh_kem ADD COLUMN project_id int(11) DEFAULT NULL AFTER task_id');
            $this->db->exec('ALTER TABLE tep_dinh_kem ADD KEY idx_tep_dinh_kem_project (project_id)');
        }
        $stmt = $this->db->query("SHOW COLUMNS FROM tep_dinh_kem LIKE 'module_name'");
        if (!$stmt->fetch()) {
            $this->db->exec("ALTER TABLE tep_dinh_kem ADD COLUMN module_name varchar(50) NOT NULL DEFAULT 'cong_viec' AFTER project_id");
        }
        $stmt = $this->db->query("SHOW COLUMNS FROM tep_dinh_kem LIKE 'encrypted_blob'");
        if (!$stmt->fetch()) {
            $this->db->exec('ALTER TABLE tep_dinh_kem ADD COLUMN encrypted_blob LONGBLOB DEFAULT NULL AFTER encrypted_path');
        }
        $stmt = $this->db->query("SHOW COLUMNS FROM tep_dinh_kem LIKE 'storage_mode'");
        if (!$stmt->fetch()) {
            $this->db->exec("ALTER TABLE tep_dinh_kem ADD COLUMN storage_mode varchar(20) NOT NULL DEFAULT 'db' AFTER encrypted_blob");
        }
        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS file_recipients (
                id INT(11) NOT NULL AUTO_INCREMENT,
                attachment_id INT(11) NOT NULL,
                user_id INT(11) NOT NULL,
                encrypted_key LONGTEXT NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_attachment_user (attachment_id, user_id),
                KEY idx_attachment_id (attachment_id),
                KEY idx_user_id (user_id),
                CONSTRAINT fk_file_recipients_attachment FOREIGN KEY (attachment_id) REFERENCES tep_dinh_kem (id) ON DELETE CASCADE,
                CONSTRAINT fk_file_recipients_user FOREIGN KEY (user_id) REFERENCES tai_khoan (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci'
        );
    }
}
