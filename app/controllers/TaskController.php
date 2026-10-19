<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Attachment;
use App\Models\Log;
use App\Models\Project;
use App\Models\Task;
use App\Services\AttachmentModuleService;

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

        $rawStatus = (string) ($_GET['status'] ?? '');
        $rawProjectId = $_GET['project_id'] ?? '';
        $rawKeyword = trim((string) ($_GET['keyword'] ?? ''));

        $statusAllowed = ['new', 'assigned', 'in_progress', 'blocked', 'submitted', 'approved', 'redo'];
        $cleanStatus = in_array($rawStatus, $statusAllowed, true) ? $rawStatus : '';
        $cleanProjectId = \App\Core\Validator::validateId($rawProjectId);
        $cleanKeyword = \App\Core\Validator::sanitizeString($rawKeyword);
        if (mb_strlen($cleanKeyword, 'UTF-8') > 100) {
            $cleanKeyword = mb_substr($cleanKeyword, 0, 100, 'UTF-8');
        }

        $filters = [
            'status' => $cleanStatus,
            'project_id' => $cleanProjectId !== null ? (string) $cleanProjectId : '',
            'keyword' => $cleanKeyword,
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
        $this->requireLogin();
        $du_an = (new Project())->forUser(Auth::user());
        $this->render('cong_viec/form', ['task' => null, 'du_an' => $du_an], 'Tạo công việc');
    }

    /**
     * Lưu công việc mới vào CSDL.
     */
    public function store(): void
    {
        $this->requireLogin();
        $this->requirePostCsrf();

        $validator = \App\Core\Validator::make($_POST, [
            'project_id' => 'required|positive_int',
            'title' => 'required|string|min:2|max:255',
            'description' => 'string',
            'priority' => 'required|in:low,medium,high',
            'deadline' => 'date',
            'expected_score' => 'integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            \flash('danger', $validator->firstError());
            \redirect('cong_viec/create');
        }

        $validated = $validator->validated();
        $projectId = (int) $validated['project_id'];
        $title = $validated['title'];

        if (!(new Project())->userCanManageProject(Auth::user(), $projectId)) {
            \flash('danger', 'Ban khong co quyen tao cong viec trong du an nay.');
            \redirect('cong_viec/create');
        }

        try {
            $taskId = (new Task())->create([
                'project_id' => $projectId,
                'title' => $title,
                'description' => $validated['description'] ?? '',
                'priority' => $validated['priority'],
                'deadline' => $this->normalizeDateInput($validated['deadline'] ?? null),
                'created_by' => Auth::user()['id'],
                'expected_score' => (int) ($validated['expected_score'] ?? 0),
            ]);
        } catch (\Throwable $e) {
            \flash('danger', 'Khong the tao cong viec: ' . $e->getMessage());
            \redirect('cong_viec/create');
        }

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
        $this->requireLogin();
        $taskModel = new Task();
        $projectModel = new Project();

        $task = $taskModel->find($id);
        if (!$task || !$projectModel->userCanManageProject(Auth::user(), (int) $task['project_id'])) {
            http_response_code(403);
            exit('Ban khong co quyen phan cong cong viec nay.');
        }

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
        $this->requireLogin();
        $this->requirePostCsrf();

        $taskModel = new Task();
        $task = $taskModel->find($id);
        $projectModel = new Project();

        if (!$task) {
            \flash('danger', 'Không tìm thấy công việc cần phân công.');
            \redirect('cong_viec/index');
        }

        if (!$projectModel->userCanManageProject(Auth::user(), (int) $task['project_id'])) {
            \flash('danger', 'Ban khong co quyen phan cong cong viec nay.');
            \redirect('cong_viec/show', ['id' => $id]);
        }

        $validator = \App\Core\Validator::make($_POST, [
            'assignee_ids' => 'required|array_of_positive_int',
            'start_date' => 'date',
            'deadline' => 'date',
        ]);

        if ($validator->fails()) {
            \flash('danger', $validator->firstError());
            \redirect('cong_viec/assign', ['id' => $id]);
        }

        $assigneeIds = array_map('intval', $_POST['assignee_ids'] ?? []);
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
            try {
                $deadlineLabel = \format_date((string) ($_POST['deadline'] ?? $task['deadline']));
                (new \App\Models\Notification())->notifyEvent(
                    $assigneeIds,
                    (int) Auth::user()['id'],
                    'Phân công công việc: ' . $task['title'],
                    'Bạn đã được phân công thực hiện công việc "' . $task['title'] . '" thuộc dự án "' . ($task['project_name'] ?? '') . '". Hạn chót: ' . $deadlineLabel . '.',
                    'task_assigned',
                    (int) $task['project_id'],
                    $id,
                    \route_url('cong_viec/show', ['id' => $id])
                );
            } catch (\Throwable $e) {
            }
            if (!empty($_FILES['attachment']['name'])) {
                try {
                    $reason = trim($_POST['upload_reason'] ?? '');
                    $this->storeE2EUploadedFile($id, $_FILES['attachment'], 'assignment_manager', $reason ?: null);
                    (new Log())->create(Auth::user()['id'], 'upload', 'attachment', $id, 'Upload tài liệu khi phân công công việc');
                    \flash('success', 'Lưu phân công và upload file thành công.');
                } catch (\Throwable $e) {
                    \flash('warning', 'Lưu phân công thành công nhưng upload file thất bại: ' . $e->getMessage());
                }
            } else {
                \flash('success', 'Lưu phân công thành công.');
            }
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
        $this->requirePostCsrf();

        $user = Auth::user();
        $taskModel = new Task();
        $task = $taskModel->find($id);

        if (!$task || !$taskModel->isUserAssigned($id, (int) $user['id'])) {
            \flash('danger', 'Bạn không có quyền cập nhật trạng thái công việc này.');
            \redirect('cong_viec/index');
        }

        $validator = \App\Core\Validator::make($_POST, [
            'status' => 'required|in:new,assigned,in_progress,blocked,submitted,approved,redo',
            'note' => 'string|max:1000',
        ]);

        if ($validator->fails()) {
            \flash('danger', $validator->firstError());
            \redirect('cong_viec/show', ['id' => $id]);
        }

        $validated = $validator->validated();
        $status = $validated['status'];
        $note = $validated['note'] ?? '';
        $ok = $taskModel->updateStatus($id, $status, $note, (int) $user['id']);

        if ($ok) {
            (new Log())->create($user['id'], 'update_status', 'task', $id, 'Cập nhật trạng thái công việc');
            try {
                $projectModel = new Project();
                $project = $projectModel->find((int) $task['project_id']);
                $managerIds = [(int) $task['created_by']];
                if ($project) {
                    $managerIds[] = (int) $project['created_by'];
                }
                foreach ($projectModel->members((int) $task['project_id']) as $m) {
                    if (($m['project_role'] ?? '') === 'lead' || ($m['role'] ?? '') === 'manager') {
                        $managerIds[] = (int) $m['user_id'];
                    }
                }
                $noteText = trim((string) $note) !== '' ? ' Ghi chú: ' . trim((string) $note) : '';
                (new \App\Models\Notification())->notifyEvent(
                    $managerIds,
                    (int) $user['id'],
                    'Cập nhật tiến độ: ' . $task['title'],
                    $user['name'] . ' đã chuyển trạng thái công việc "' . $task['title'] . '" sang "' . \status_label($status) . '".' . $noteText,
                    'task_status',
                    (int) $task['project_id'],
                    $id,
                    \route_url('cong_viec/show', ['id' => $id])
                );
            } catch (\Throwable $e) {
            }
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
        $this->requirePostCsrf();

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

        try {
            $fileType = ($user['role'] === 'employee') ? 'employee' : 'manager';
            $reason = trim($_POST['upload_reason'] ?? '');
            $ok = $this->storeE2EUploadedFile($id, $file, $fileType, $reason ?: null);

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
        $this->requireLogin();
        $task = (new Task())->find($id);
        if (!$task || !(new Project())->userCanManageProject(Auth::user(), (int) $task['project_id'])) {
            http_response_code(403);
            exit('Ban khong co quyen duyet cong viec nay.');
        }

        $tep_dinh_kem = (new Attachment())->byTask($id);
        $this->render('cong_viec/review', compact('task', 'tep_dinh_kem'), 'Duyệt và đánh giá');
    }

    /**
     * Lưu kết quả duyệt: đạt hoặc yêu cầu làm lại.
     * Manager có thể upload file hướng dẫn/tài liệu kèm theo.
     */
    public function saveReview(int $id): void
    {
        $this->requireLogin();
        $this->requirePostCsrf();

        $task = (new Task())->find($id);
        if (!$task || !(new Project())->userCanManageProject(Auth::user(), (int) $task['project_id'])) {
            \flash('danger', 'Ban khong co quyen duyet cong viec nay.');
            \redirect('cong_viec/show', ['id' => $id]);
        }

        $validator = \App\Core\Validator::make($_POST, [
            'review_status' => 'required|in:approved,redo',
            'review_score' => 'integer|min:0|max:100',
            'review_comment' => 'string|max:1000',
        ]);

        if ($validator->fails()) {
            \flash('danger', $validator->firstError());
            \redirect('cong_viec/review', ['id' => $id]);
        }

        $validated = $validator->validated();
        $status = $validated['review_status'];
        $comment = $validated['review_comment'] ?? '';
        $score = isset($validated['review_score']) ? (int) $validated['review_score'] : 0;

        $taskModel = new Task();
        $ok = $taskModel->review($id, $status, $comment, $score, (int) Auth::user()['id']);

        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'review', 'task', $id, 'Duyệt và đánh giá công việc');
            try {
                $assignees = array_map(static fn (array $u): int => (int) $u['id'], $taskModel->assignedUsers($id));
                if (!empty($task['assignee_id'])) {
                    $assignees[] = (int) $task['assignee_id'];
                }
                $commentText = trim((string) $comment) !== '' ? ' Nhận xét: ' . trim((string) $comment) : '';
                (new \App\Models\Notification())->notifyEvent(
                    $assignees,
                    (int) Auth::user()['id'],
                    'Kết quả duyệt công việc: ' . $task['title'],
                    'Công việc "' . $task['title'] . '" đã được đánh giá: ' . \status_label($status) . ' (Điểm: ' . $score . ').' . $commentText,
                    'task_reviewed',
                    (int) $task['project_id'],
                    $id,
                    \route_url('cong_viec/show', ['id' => $id])
                );
            } catch (\Throwable $e) {
            }
            
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
            $reason = trim($_POST['upload_reason'] ?? '');
            $ok = $this->storeE2EUploadedFile($taskId, $file, 'manager', $reason ?: null);

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
        $this->requireLogin();
        $this->requirePostCsrf();

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
            $reason = trim($_POST['upload_reason'] ?? '');
            $ok = $this->storeE2EUploadedFile($taskId, $file, 'project_manager', $reason ?: null);

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

    private function storeE2EUploadedFile(int $taskId, array $file, string $fileType, ?string $reason): int|false
    {
        return (new AttachmentModuleService())->storeE2EUpload($file, [
            'task_id' => $taskId,
            'project_id' => null,
            'module_name' => 'cong_viec',
            'uploaded_by' => Auth::user()['id'],
            'file_type' => $fileType,
            'upload_reason' => $reason,
            'encrypted_key' => null,
        ]);
    }

    private function normalizeDateInput(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp === false ? $value : date('Y-m-d', $timestamp);
    }
}
