<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Attachment;
use App\Models\Log;
use App\Models\Project;
use App\Models\Task;
use App\Services\FileCryptoService;

/**
 * Controller cho toàn bộ nghiệp vụ công việc.
 */
class TaskController extends BaseController
{
    /**
     * Hiển thị danh sách công việc theo bộ lọc và quyền truy cập.
     */
    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'project_id' => $_GET['project_id'] ?? '',
        ];

        $taskModel = new Task();
        $projectModel = new Project();

        $cong_viec = $taskModel->forUser($user, $filters);
        $du_an = $projectModel->forUser($user);

        $this->render('cong_viec/index', compact('cong_viec', 'du_an', 'filters'), 'Quản lý công việc');
    }

    /**
     * Hiển thị chi tiết công việc, file đính kèm và lịch sử trạng thái.
     */
    public function show(int $id): void
    {
        $this->requireLogin();
        $user = Auth::user();
        $taskModel = new Task();

        if (!$taskModel->canAccess($user, $id)) {
            http_response_code(403);
            exit('Bạn không có quyền xem công việc này.');
        }

        $task = $taskModel->find($id);
        $logs = $taskModel->logs($id);
        $tep_dinh_kem = (new Attachment())->byTask($id);

        $this->render('cong_viec/show', compact('task', 'logs', 'tep_dinh_kem'), 'Chi tiết công việc');
    }

    /**
     * Mở form tạo công việc.
     */
    public function create(): void
    {
        $this->requireRole(['manager', 'admin']);
        $du_an = (new Project())->forUser(Auth::user());
        $this->render('cong_viec/form', ['task' => null, 'du_an' => $du_an], 'Tạo công việc');
    }

    /**
     * Lưu công việc mới vào CSDL.
     */
    public function store(): void
    {
        $this->requireRole(['manager', 'admin']);
        if (!\is_post()) {
            \redirect('cong_viec/index');
        }

        $taskId = (new Task())->create([
            'project_id' => (int) ($_POST['project_id'] ?? 0),
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'priority' => $_POST['priority'] ?? 'medium',
            'deadline' => $_POST['deadline'] ?? null,
            'created_by' => Auth::user()['id'],
            'expected_score' => (int) ($_POST['expected_score'] ?? 0),
        ]);

        (new Log())->create(Auth::user()['id'], 'create', 'task', $taskId, 'Tạo công việc');
        
        // Xử lý upload file từ manager (nếu có)
        if (!empty($_FILES['task_attachment']['name'])) {
            $this->handleTaskFileUpload($taskId);
        }
        
        \flash('success', 'Tạo công việc thành công.');
        \redirect('cong_viec/show', ['id' => $taskId]);
    }

    /**
     * Mở form phân công công việc cho thành viên dự án.
     */
    public function assign(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        $taskModel = new Task();
        $projectModel = new Project();

        $task = $taskModel->find($id);
        $project = $projectModel->find((int) $task['project_id']);
        $members = $projectModel->members((int) $task['project_id']);
        $selectedAssigneeIds = array_map(static fn (array $item): int => (int) $item['id'], $taskModel->assignedUsers($id));

        $this->render('cong_viec/assign', compact('task', 'project', 'members', 'selectedAssigneeIds'), 'Phân công công việc');
    }

    /**
     * Lưu thông tin phân công công việc.
     */
    public function saveAssignment(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        if (!\is_post()) {
            \redirect('cong_viec/index');
        }

        $taskModel = new Task();
        $task = $taskModel->find($id);
        $projectModel = new Project();
        $assigneeIds = array_map('intval', $_POST['assignee_ids'] ?? []);

        if (!$task) {
            \flash('danger', 'Không tìm thấy công việc cần phân công.');
            \redirect('cong_viec/index');
        }

        $assigneeIds = array_values(array_unique(array_filter($assigneeIds, static fn (int $value): bool => $value > 0)));
        if (!$assigneeIds) {
            \flash('danger', 'Vui lòng chọn ít nhất một thành viên phụ trách.');
            \redirect('cong_viec/assign', ['id' => $id]);
        }

        foreach ($assigneeIds as $assigneeId) {
            if (!$projectModel->userBelongsToProject((int) $task['project_id'], $assigneeId)) {
                \flash('danger', 'Có thành viên không thuộc dự án nên không thể phân công.');
                \redirect('cong_viec/assign', ['id' => $id]);
            }
        }

        $ok = $taskModel->assign($id, [
            'assignee_ids' => $assigneeIds,
            'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
            'deadline' => $_POST['deadline'] ?? $task['deadline'],
        ]);

        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'assign', 'task', $id, 'Điều chỉnh phân công công việc');
            \flash('success', 'Lưu phân công thành công.');
        } else {
            \flash('danger', 'Phân công công việc thất bại.');
        }

        \redirect('cong_viec/show', ['id' => $id]);
    }

    /**
     * Nhân viên cập nhật trạng thái công việc của mình.
     */
    public function updateStatus(int $id): void
    {
        $this->requireLogin();
        if (!\is_post()) {
            \redirect('cong_viec/show', ['id' => $id]);
        }

        $user = Auth::user();
        $taskModel = new Task();
        $task = $taskModel->find($id);

        if (!$task || !$taskModel->isUserAssigned($id, (int) $user['id'])) {
            \flash('danger', 'Bạn không có quyền cập nhật trạng thái công việc này.');
            \redirect('cong_viec/index');
        }

        $status = $_POST['status'] ?? 'in_progress';
        $note = trim($_POST['note'] ?? '');
        $ok = $taskModel->updateStatus($id, $status, $note, (int) $user['id']);

        if ($ok) {
            (new Log())->create($user['id'], 'update_status', 'task', $id, 'Cập nhật trạng thái công việc');
            \flash('success', 'Cập nhật trạng thái thành công.');
        } else {
            \flash('danger', 'Cập nhật trạng thái thất bại.');
        }

        \redirect('cong_viec/show', ['id' => $id]);
    }

    /**
     * Upload file kết quả và mã hóa trước khi lưu.
     */
    public function upload(int $id): void
    {
        $this->requireLogin();
        if (!\is_post()) {
            \redirect('cong_viec/show', ['id' => $id]);
        }

        $user = Auth::user();
        $taskModel = new Task();

        if (!$taskModel->canAccess($user, $id)) {
            \flash('danger', 'Bạn không có quyền upload vào công việc này.');
            \redirect('cong_viec/index');
        }

        if (empty($_FILES['attachment']['name'])) {
            \flash('danger', 'Vui lòng chọn file.');
            \redirect('cong_viec/show', ['id' => $id]);
        }

        $file = $_FILES['attachment'];
        if ((int) $file['size'] > MAX_UPLOAD_BYTES) {
            \flash('danger', 'File vượt quá dung lượng cho phép (20MB).');
            \redirect('cong_viec/show', ['id' => $id]);
        }

        $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'txt'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExt, true)) {
            \flash('danger', 'Định dạng file không được hỗ trợ. Chỉ hỗ trợ: pdf, doc, docx, xls, xlsx, png, jpg, jpeg, txt');
            \redirect('cong_viec/show', ['id' => $id]);
        }

        try {
            $cryptoService = new FileCryptoService();
            $stored = $cryptoService->hybridEncryptAndStore(
                $file['tmp_name'],
                $file['name'],
                $cryptoService->getRsaPublicKey()
            );

            $ok = (new Attachment())->create([
                'task_id' => $id,
                'original_name' => $file['name'],
                'stored_name' => $stored['stored_name'],
                'mime_type' => $file['type'] ?: 'application/octet-stream',
                'file_size' => (int) $file['size'],
                'encrypted_path' => $stored['path'],
                'uploaded_by' => $user['id'],
                'encrypted_key' => $stored['key'],
                'iv' => $stored['iv'],
            ]);

            if ($ok) {
                (new Log())->create($user['id'], 'upload', 'attachment', $id, 'Upload file công việc');
                \flash('success', 'Upload và mã hóa file thành công.');
            } else {
                \flash('danger', 'Không thể lưu metadata file.');
            }
        } catch (\Throwable $e) {
            \flash('danger', 'Upload thất bại: ' . $e->getMessage());
        }

        \redirect('cong_viec/show', ['id' => $id]);
    }

    /**
     * Mở màn hình duyệt và đánh giá công việc.
     */
    public function review(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        $task = (new Task())->find($id);
        $tep_dinh_kem = (new Attachment())->byTask($id);
        $this->render('cong_viec/review', compact('task', 'tep_dinh_kem'), 'Duyệt và đánh giá');
    }

    /**
     * Lưu kết quả duyệt: đạt hoặc yêu cầu làm lại.
     * Manager có thể upload file hướng dẫn/tài liệu kèm theo.
     */
    public function saveReview(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        if (!\is_post()) {
            \redirect('cong_viec/show', ['id' => $id]);
        }

        $status = $_POST['review_status'] ?? 'approved';
        $comment = trim($_POST['review_comment'] ?? '');
        $score = (int) ($_POST['review_score'] ?? 0);

        $ok = (new Task())->review($id, $status, $comment, $score, (int) Auth::user()['id']);

        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'review', 'task', $id, 'Duyệt và đánh giá công việc');
            
            // Xử lý upload file từ manager (nếu có)
            if (!empty($_FILES['manager_attachment']['name'])) {
                $this->handleManagerFileUpload($id);
            }
            
            \flash('success', 'Đã lưu kết quả duyệt.');
        } else {
            \flash('danger', 'Duyệt công việc thất bại.');
        }

        \redirect('cong_viec/show', ['id' => $id]);
    }

    /**
     * Xử lý upload file từ manager và mã hóa trước lưu.
     */
    private function handleManagerFileUpload(int $taskId): void
    {
        $user = Auth::user();
        $file = $_FILES['manager_attachment'] ?? null;

        if (!$file || empty($file['name'])) {
            return;
        }

        try {
            // Kiểm tra kích thước file
            if ((int) $file['size'] > MAX_UPLOAD_BYTES) {
                \flash('warning', 'File manager vượt quá 5MB, bỏ qua upload.');
                return;
            }

            // Kiểm tra định dạng file
            $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'txt'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExt, true)) {
                \flash('warning', 'Định dạng file manager không được hỗ trợ, bỏ qua upload.');
                return;
            }

            // Mã hóa file
            $stored = (new FileCryptoService())->encryptAndStore($file['tmp_name'], $file['name']);

            // Lưu metadata
            $attachmentModel = new Attachment();
            $reason = trim($_POST['upload_reason'] ?? '');
            
            $ok = $attachmentModel->create([
                'task_id' => $taskId,
                'original_name' => $file['name'],
                'stored_name' => $stored['stored_name'],
                'mime_type' => $file['type'] ?: 'application/octet-stream',
                'file_size' => (int) $file['size'],
                'encrypted_path' => $stored['path'],
                'uploaded_by' => $user['id'],
                'file_type' => 'manager',
                'upload_reason' => $reason ?: null,
            ]);

            if ($ok) {
                (new Log())->create($user['id'], 'upload', 'attachment', $taskId, "Upload file hướng dẫn (Manager): $reason");
                \flash('info', 'File hướng dẫn đã upload và mã hóa.');
            } else {
                \flash('warning', 'Không thể lưu metadata file manager.');
            }
        } catch (\Throwable $e) {
            \flash('warning', 'Upload file manager thất bại: ' . $e->getMessage());
        }
    }

    /**
     * Xóa công việc theo điều kiện trạng thái thực tế.
     */
    public function destroy(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        if (!\is_post()) {
            \redirect('cong_viec/index');
        }

        $taskModel = new Task();
        $task = $taskModel->find($id);
        if (!$task) {
            \flash('danger', 'Không tìm thấy công việc cần xóa.');
            \redirect('cong_viec/index');
        }

        if (!$taskModel->canDelete(Auth::user(), $id)) {
            \flash('danger', 'Chỉ được xóa công việc ở trạng thái mới tạo, đã phân công, bị chặn hoặc yêu cầu làm lại.');
            \redirect('cong_viec/show', ['id' => $id]);
        }

        $title = $task['title'];
        if ($taskModel->deleteTask($id)) {
            (new Log())->create(Auth::user()['id'], 'delete', 'task', $id, 'Xóa công việc: ' . $title);
            \flash('success', 'Đã xóa công việc thành công.');
            \redirect('cong_viec/index');
        }

        \flash('danger', 'Không thể xóa công việc.');
        \redirect('cong_viec/show', ['id' => $id]);
    }

    /**
     * Xử lý upload file từ manager khi tạo công việc và mã hóa trước lưu.
     */
    private function handleTaskFileUpload(int $taskId): void
    {
        $user = Auth::user();
        $file = $_FILES['task_attachment'] ?? null;

        if (!$file || empty($file['name'])) {
            return;
        }

        try {
            // Kiểm tra kích thước file (20MB cho manager upload)
            if ((int) $file['size'] > MAX_UPLOAD_BYTES) {
                \flash('warning', 'File vượt quá 20MB, bỏ qua upload.');
                return;
            }

            // Kiểm tra định dạng file
            $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'txt'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExt, true)) {
                \flash('warning', 'Định dạng file không được hỗ trợ, bỏ qua upload.');
                return;
            }

            // Mã hóa file
            $stored = (new FileCryptoService())->encryptAndStore($file['tmp_name'], $file['name']);

            // Lưu metadata
            $attachmentModel = new Attachment();
            $reason = trim($_POST['upload_reason'] ?? '');
            
            $ok = $attachmentModel->create([
                'task_id' => $taskId,
                'original_name' => $file['name'],
                'stored_name' => $stored['stored_name'],
                'mime_type' => $file['type'] ?: 'application/octet-stream',
                'file_size' => (int) $file['size'],
                'encrypted_path' => $stored['path'],
                'uploaded_by' => $user['id'],
                'file_type' => 'project_manager',
                'upload_reason' => $reason ?: null,
            ]);

            if ($ok) {
                (new Log())->create($user['id'], 'upload', 'attachment', $taskId, "Upload file khi tạo công việc: $reason");
                \flash('info', 'File đã upload và mã hóa thành công.');
            } else {
                \flash('warning', 'Không thể lưu metadata file.');
            }
        } catch (\Throwable $e) {
            \flash('warning', 'Upload file thất bại: ' . $e->getMessage());
        }
    }
}
