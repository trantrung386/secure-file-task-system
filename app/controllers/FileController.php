<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Attachment;
use App\Models\Log;
use App\Models\Project;
use App\Models\Task;

class FileController extends BaseController
{
    public function download(int $id): void
    {
        $this->requireLogin();

        $attachmentModel = new Attachment();
        $attachment = $attachmentModel->find($id);
        if (!$attachment) {
            exit('File khong ton tai.');
        }

        $this->authorize($attachment);
        $recipientKey = $attachmentModel->recipientKey($id, (int) Auth::user()['id']);
        if (!$recipientKey) {
            exit('Tai khoan nay khong co khoa E2E de giai ma file.');
        }

        (new Log())->create(Auth::user()['id'], 'download', 'attachment', $id, 'Mo trang giai ma file E2E');
        $this->render('files/download', [
            'attachment' => $attachment,
            'recipientKey' => $recipientKey['encrypted_key'],
            'ciphertextUrl' => \route_url('files/ciphertext', ['id' => $id]),
        ], 'Tai file E2E');
    }

    public function ciphertext(int $id): void
    {
        $this->requireLogin();

        $attachment = (new Attachment())->find($id);
        if (!$attachment) {
            http_response_code(404);
            exit('File khong ton tai.');
        }

        $this->authorize($attachment);
        if (!(new Attachment())->recipientKey($id, (int) Auth::user()['id'])) {
            http_response_code(403);
            exit('Tai khoan nay khong co khoa E2E de tai file.');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($attachment['stored_name']) . '"');
        $storageMode = (string) ($attachment['storage_mode'] ?? 'fs');

        if ($storageMode === 'db' && isset($attachment['encrypted_blob']) && $attachment['encrypted_blob'] !== null) {
            $ciphertext = (string) $attachment['encrypted_blob'];
            header('Content-Length: ' . strlen($ciphertext));
            echo $ciphertext;
            exit;
        }

        if (!is_file((string) $attachment['encrypted_path'])) {
            http_response_code(404);
            exit('Ciphertext khong ton tai.');
        }

        header('Content-Length: ' . filesize((string) $attachment['encrypted_path']));
        readfile((string) $attachment['encrypted_path']);
        exit;
    }

    private function authorize(array $attachment): void
    {
        $user = Auth::user();
        if (($user['role'] ?? '') === 'admin') {
            return;
        }

        $projectId = (int) ($attachment['project_id'] ?? 0);
        if ($projectId > 0) {
            $projectModel = new Project();
            $project = $projectModel->find($projectId);
            if ($project && ((int) $project['created_by'] === (int) $user['id'] || $projectModel->userBelongsToProject($projectId, (int) $user['id']))) {
                return;
            }

            http_response_code(403);
            exit('Ban khong co quyen tai file du an nay.');
        }

        $taskId = (int) ($attachment['task_id'] ?? 0);
        if ($taskId > 0) {
            $taskModel = new Task();
            $task = $taskModel->find($taskId);
            $projectModel = new Project();
            $canAccessTaskFile = $task
                && (
                    $taskModel->isUserAssigned($taskId, (int) $user['id'])
                    || (int) $task['created_by'] === (int) $user['id']
                    || $projectModel->userCanManageProject($user, (int) $task['project_id'])
                );

            if (!$canAccessTaskFile) {
                http_response_code(403);
                exit('Ban khong duoc phan cong nen khong co quyen tai file cong viec nay.');
            }
            return;
        }

        if ((int) $attachment['uploaded_by'] !== (int) $user['id']) {
            http_response_code(403);
            exit('Ban khong co quyen tai file nay.');
        }
    }
}
