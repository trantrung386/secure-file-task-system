<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Attachment;

/**
 * Service upload dung chung theo module nghiep vu (cong_viec, du_an...).
 */
class AttachmentModuleService
{
    private const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'txt'];

    public function storeE2EUpload(array $file, array $data): int|false
    {
        $this->validateUpload($file);

        $iv = trim((string) ($_POST['e2e_iv'] ?? ''));
        $recipients = json_decode((string) ($_POST['e2e_keys'] ?? '[]'), true);
        if ($iv === '' || !is_array($recipients) || !$recipients) {
            throw new \RuntimeException('Thiếu metadata E2E hoặc khóa người nhận.');
        }

        $hasRecipientKey = false;
        foreach ($recipients as $recipient) {
            if (!empty($recipient['user_id']) && !empty($recipient['encrypted_key'])) {
                $hasRecipientKey = true;
                break;
            }
        }
        if (!$hasRecipientKey) {
            throw new \RuntimeException('Thiếu dữ liệu khóa E2E mã hóa file cho người nhận.');
        }

        $data['original_name'] = trim((string) ($_POST['e2e_original_name'] ?? $file['name']));
        $data['mime_type'] = trim((string) ($_POST['e2e_original_type'] ?? ($file['type'] ?: 'application/octet-stream')));
        $data['file_size'] = (int) ($_POST['e2e_original_size'] ?? $file['size']);
        $data['iv'] = $iv;

        return $this->saveFromUpload($file, $data, $recipients);
    }

    private function validateUpload(array $file): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($file['name'])) {
            throw new \RuntimeException('File upload không hợp lệ.');
        }

        if ((int) ($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
            throw new \RuntimeException('File vượt quá dung lượng cho phép (20MB).');
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new \RuntimeException('Định dạng file không được hỗ trợ.');
        }
    }

    private function saveFromUpload(array $file, array $data, array $recipients): int|false
    {
        $stored = (new FileCryptoService())->captureCiphertextUpload((string) ($file['tmp_name'] ?? ''));

        $attachmentModel = new Attachment();
        $attachmentId = $attachmentModel->create([
            'task_id' => $data['task_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'module_name' => $data['module_name'] ?? 'cong_viec',
            'original_name' => $data['original_name'],
            'stored_name' => $stored['stored_name'],
            'mime_type' => $data['mime_type'] ?? 'application/octet-stream',
            'file_size' => (int) ($data['file_size'] ?? 0),
            'encrypted_path' => null,
            'encrypted_blob' => $stored['ciphertext'],
            'storage_mode' => 'db',
            'uploaded_by' => (int) $data['uploaded_by'],
            'file_type' => $data['file_type'] ?? 'employee',
            'upload_reason' => $data['upload_reason'] ?? null,
            'encrypted_key' => $data['encrypted_key'] ?? null,
            'iv' => $data['iv'] ?? null,
        ]);

        if ($attachmentId !== false) {
            $attachmentModel->addRecipients($attachmentId, $recipients);
        }

        return $attachmentId;
    }
}
