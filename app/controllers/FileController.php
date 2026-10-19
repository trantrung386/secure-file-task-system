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
    /**
     * Danh sách và quản lý tài liệu dành cho Quản lý/Trưởng phòng và Admin.
     * Manager chỉ quản lý tài liệu thuộc các dự án mình tham gia; Admin quản lý toàn bộ.
     */
    public function index(): void
    {
        $this->requireRole(['manager', 'admin']);
        $user = Auth::user();

        $rawProjectId = $_GET['project_id'] ?? '';
        $rawTaskId = $_GET['task_id'] ?? '';
        $rawFileType = trim((string) ($_GET['file_type'] ?? ''));
        $rawKeyword = trim((string) ($_GET['keyword'] ?? ''));

        $cleanProjectId = \App\Core\Validator::validateId($rawProjectId);
        $cleanTaskId = \App\Core\Validator::validateId($rawTaskId);
        $allowedFileTypes = ['project_doc', 'task_doc', 'project_manager', 'manager', 'assignment_manager', 'employee'];
        $cleanFileType = in_array($rawFileType, $allowedFileTypes, true) ? $rawFileType : '';
        $cleanKeyword = \App\Core\Validator::sanitizeString($rawKeyword);
        if (mb_strlen($cleanKeyword, 'UTF-8') > 100) {
            $cleanKeyword = mb_substr($cleanKeyword, 0, 100, 'UTF-8');
        }

        $projectModel = new Project();
        $projects = $projectModel->forUser($user);
        $allowedProjectIds = array_map(static fn (array $p): int => (int) $p['id'], $projects);

        // Nếu Manager truyền project_id không thuộc dự án mình tham gia thì bỏ lọc đó
        if ($cleanProjectId !== null && $user['role'] !== 'admin' && !in_array($cleanProjectId, $allowedProjectIds, true)) {
            $cleanProjectId = null;
        }

        $filters = [
            'project_id' => $cleanProjectId !== null ? (string) $cleanProjectId : '',
            'task_id' => $cleanTaskId !== null ? (string) $cleanTaskId : '',
            'file_type' => $cleanFileType,
            'keyword' => $cleanKeyword,
        ];

        $attachmentModel = new Attachment();
        $documents = $attachmentModel->forManagerOrAdmin($user, $filters);
        $allAccessibleDocs = $attachmentModel->forManagerOrAdmin($user);

        $docStats = [
            'total' => count($allAccessibleDocs),
            'project_docs' => 0,
            'task_docs' => 0,
            'total_bytes' => 0,
        ];
        foreach ($allAccessibleDocs as $doc) {
            $docStats['total_bytes'] += (int) ($doc['file_size'] ?? 0);
            if (!empty($doc['task_id'])) {
                $docStats['task_docs']++;
            } else {
                $docStats['project_docs']++;
            }
        }

        $tasks = (new Task())->forUser($user);

        $this->render('files/index', [
            'user' => $user,
            'documents' => $documents,
            'projects' => $projects,
            'tasks' => $tasks,
            'filters' => $filters,
            'docStats' => $docStats,
        ], 'Quản lý tài liệu');
    }

    /**
     * Tải lên tài liệu mới (mã hóa E2E) vào dự án hoặc công việc thuộc phạm vi quản lý.
     */
    public function upload(): void
    {
        $this->requireRole(['manager', 'admin']);
        $this->requirePostCsrf();

        $user = Auth::user();
        $projectId = \App\Core\Validator::validateId($_POST['project_id'] ?? null);
        $taskId = \App\Core\Validator::validateId($_POST['task_id'] ?? null);
        $reason = trim((string) ($_POST['upload_reason'] ?? ''));

        if ($projectId === null) {
            \flash('danger', 'Vui lòng chọn dự án cho tài liệu.');
            \redirect('files/index');
        }

        $projectModel = new Project();
        $project = $projectModel->find($projectId);
        $canAccessProject = $project && (
            $user['role'] === 'admin'
            || (int) $project['created_by'] === (int) $user['id']
            || $projectModel->userBelongsToProject($projectId, (int) $user['id'])
        );

        if (!$canAccessProject) {
            \flash('danger', 'Bạn chỉ được phép quản lý và tải tài liệu lên dự án mà mình tham gia.');
            \redirect('files/index');
        }

        if ($taskId !== null) {
            $task = (new Task())->find($taskId);
            if (!$task || (int) $task['project_id'] !== $projectId) {
                \flash('danger', 'Công việc được chọn không thuộc dự án này.');
                \redirect('files/index');
            }
        }

        if (empty($_FILES['attachment']['name'])) {
            \flash('danger', 'Vui lòng chọn file tài liệu cần tải lên.');
            \redirect('files/index');
        }

        try {
            $attachmentId = (new \App\Services\AttachmentModuleService())->storeE2EUpload($_FILES['attachment'], [
                'task_id' => $taskId,
                'project_id' => $projectId,
                'module_name' => $taskId !== null ? 'cong_viec' : 'du_an',
                'uploaded_by' => (int) $user['id'],
                'file_type' => $taskId !== null ? 'manager' : 'project_manager',
                'upload_reason' => $reason !== '' ? $reason : null,
                'encrypted_key' => null,
            ]);

            if ($attachmentId !== false) {
                (new Log())->create((int) $user['id'], 'upload', 'attachment', (int) $attachmentId, 'Tải lên tài liệu từ Quản lý tài liệu');
                \flash('success', 'Đã tải lên và mã hóa E2E tài liệu thành công.');
            } else {
                \flash('danger', 'Không thể lưu thông tin tài liệu.');
            }
        } catch (\Throwable $e) {
            \flash('danger', 'Tải lên tài liệu thất bại: ' . $e->getMessage());
        }

        \redirect('files/index');
    }

    /**
     * Cập nhật ghi chú / mô tả của tài liệu.
     */
    public function update(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        $this->requirePostCsrf();

        $user = Auth::user();
        $attachmentModel = new Attachment();
        $attachment = $attachmentModel->find($id);

        if (!$attachment) {
            \flash('danger', 'Không tìm thấy tài liệu.');
            \redirect('files/index');
        }

        if (!$attachmentModel->canManageAttachment($user, $attachment)) {
            \flash('danger', 'Bạn không có quyền chỉnh sửa tài liệu thuộc dự án này.');
            \redirect('files/index');
        }

        $reason = \App\Core\Validator::sanitizeString((string) ($_POST['upload_reason'] ?? ''));
        if (mb_strlen($reason, 'UTF-8') > 255) {
            $reason = mb_substr($reason, 0, 255, 'UTF-8');
        }

        if ($attachmentModel->updateReason($id, $reason)) {
            (new Log())->create((int) $user['id'], 'update', 'attachment', $id, 'Cập nhật ghi chú tài liệu: ' . $attachment['original_name']);
            \flash('success', 'Đã cập nhật thông tin tài liệu.');
        } else {
            \flash('danger', 'Không thể cập nhật thông tin tài liệu.');
        }

        \redirect('files/index');
    }

    /**
     * Xóa tài liệu (Manager chỉ xóa được tài liệu thuộc dự án mình tham gia; Admin xóa được tất cả).
     */
    public function destroy(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        $this->requirePostCsrf();

        $user = Auth::user();
        $attachmentModel = new Attachment();
        $attachment = $attachmentModel->find($id);

        if (!$attachment) {
            \flash('danger', 'Không tìm thấy tài liệu cần xóa.');
            \redirect('files/index');
        }

        if (!$attachmentModel->canManageAttachment($user, $attachment)) {
            \flash('danger', 'Bạn không có quyền xóa tài liệu của dự án không tham gia.');
            \redirect('files/index');
        }

        $name = (string) ($attachment['original_name'] ?? '');
        if ($attachmentModel->deleteAttachment($id)) {
            (new Log())->create((int) $user['id'], 'delete', 'attachment', $id, 'Xóa tài liệu: ' . $name);
            \flash('success', 'Đã xóa tài liệu thành công.');
        } else {
            \flash('danger', 'Không thể xóa tài liệu.');
        }

        \redirect('files/index');
    }

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
                    || $projectModel->userBelongsToProject((int) $task['project_id'], (int) $user['id'])
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
