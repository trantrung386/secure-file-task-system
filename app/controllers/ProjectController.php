<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Attachment;
use App\Models\Log;
use App\Models\Project;
use App\Services\AttachmentModuleService;

class ProjectController extends BaseController
{
    /**
     * Hiển thị danh sách dự án mà người dùng có quyền truy cập.
     */
    public function index(): void
    {
        $this->requireLogin();
        $du_an = (new Project())->forUser(Auth::user());
        $this->render('du_an/index', ['du_an' => $du_an], 'Quản lý dự án');
    }

    /**
     * Hiển thị thông tin tổng hợp của một dự án có quyền truy cập.
     */
    public function show(int $id): void
    {
        $this->requireLogin();
        $projectModel = new Project();
        $project = $projectModel->find($id);
        $user = Auth::user();
        $canAccessProject = $project
            && (
                ($user['role'] ?? '') === 'admin'
                || (int) $project['created_by'] === (int) $user['id']
                || $projectModel->userBelongsToProject($id, (int) $user['id'])
            );

        if (!$canAccessProject) {
            http_response_code(403);
            exit('Bạn không có quyền xem dự án này.');
        }

        $members = $projectModel->members($id);
        $projectFiles = (new Attachment())->byProject($id);
        $tasks = (new \App\Models\Task())->forUser($user, ['project_id' => $id]);
        $canManageProject = $projectModel->userCanManageProject($user, $id);

        $this->render('du_an/show', compact('project', 'members', 'projectFiles', 'tasks', 'canManageProject'), 'Chi tiết dự án');
    }

    /**
     * Mở form tạo dự án mới.
     */
    public function create(): void
    {
        $this->requireRole(['manager', 'admin']);
        $model = new Project();
        $this->render('du_an/form', [
            'project' => null,
            'generatedCode' => $model->generateNextCode(),
            'validationError' => null,
        ], 'Tạo dự án');
    }

    /**
     * Lưu dự án mới và tự động thêm người tạo làm trưởng nhóm của dự án.
     */
    public function store(): void
    {
        $this->requireRole(['manager', 'admin']);
        if (!\is_post()) {
            \redirect('du_an/index');
        }

        $user = Auth::user();
        $model = new Project();

        $validator = \App\Core\Validator::make($_POST, [
            'name' => 'required|string|min:2|max:255',
            'description' => 'string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:planning,active,completed',
        ]);

        if ($validator->fails()) {
            $this->render('du_an/form', [
                'project' => $_POST,
                'generatedCode' => $model->generateNextCode(),
                'validationError' => $validator->firstError(),
            ], 'Tạo dự án');
            return;
        }

        $payload = $this->projectPayloadFromPost(['created_by' => $user['id']]);
        $dateError = $this->validateDateRange($payload['start_date'], $payload['end_date']);

        if ($dateError !== null) {
            $this->render('du_an/form', [
                'project' => $payload,
                'generatedCode' => $model->generateNextCode(),
                'validationError' => $dateError,
            ], 'Tạo dự án');
            return;
        }

        $projectId = $model->create($payload);
        $model->addMember($projectId, (int) $user['id'], 'lead');
        (new Log())->create($user['id'], 'create', 'project', $projectId, 'Tạo dự án mới');

        if (!empty($_FILES['project_attachment']['name'])) {
            $this->handleProjectFileUpload($projectId);
        }

        \flash('success', 'Tạo dự án thành công.');
        \redirect('du_an/index');
    }

    /**
     * Mở form cập nhật dự án.
     */
    public function edit(int $id): void
    {
        $this->requireLogin();
        $model = new Project();
        $project = $model->find($id);
        if (!$project) {
            \flash('danger', 'Không tìm thấy dự án.');
            \redirect('du_an/index');
        }

        if (!$model->userCanManageProject(Auth::user(), $id)) {
            \flash('danger', 'Bạn không có quyền cập nhật dự án này.');
            \redirect('du_an/index');
        }

        $this->render('du_an/form', [
            'project' => $project,
            'generatedCode' => $project['code'] ?? '',
            'validationError' => null,
        ], 'Cập nhật dự án');
    }

    /**
     * Lưu thông tin chỉnh sửa dự án.
     */
    public function update(int $id): void
    {
        $this->requireLogin();
        if (!\is_post()) {
            \redirect('du_an/index');
        }

        $model = new Project();
        $existingProject = $model->find($id);
        if (!$existingProject) {
            \flash('danger', 'Không tìm thấy dự án để cập nhật.');
            \redirect('du_an/index');
        }

        if (!$model->userCanManageProject(Auth::user(), $id)) {
            \flash('danger', 'Bạn không có quyền cập nhật dự án này.');
            \redirect('du_an/index');
        }

        $validator = \App\Core\Validator::make($_POST, [
            'name' => 'required|string|min:2|max:255',
            'description' => 'string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:planning,active,completed',
        ]);

        if ($validator->fails()) {
            $this->render('du_an/form', [
                'project' => array_merge($existingProject, $_POST),
                'generatedCode' => $existingProject['code'] ?? '',
                'validationError' => $validator->firstError(),
            ], 'Cập nhật dự án');
            return;
        }

        $payload = $this->projectPayloadFromPost();
        $dateError = $this->validateDateRange($payload['start_date'], $payload['end_date']);

        if ($dateError !== null) {
            $this->render('du_an/form', [
                'project' => array_merge($existingProject, $payload),
                'generatedCode' => $existingProject['code'] ?? '',
                'validationError' => $dateError,
            ], 'Cập nhật dự án');
            return;
        }

        $ok = $model->update($id, $payload);

        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'update', 'project', $id, 'Cập nhật dự án');
            if (!empty($_FILES['project_attachment']['name'])) {
                $this->handleProjectFileUpload($id);
            } else {
                \flash('success', 'Cập nhật dự án thành công.');
            }
        } else {
            \flash('danger', 'Cập nhật dự án thất bại.');
        }

        \redirect('du_an/index');
    }

    /**
     * Xóa dự án khi còn ở trạng thái phù hợp.
     */
    public function destroy(int $id): void
    {
        $this->requireLogin();
        if (!\is_post()) {
            \redirect('du_an/index');
        }

        $model = new Project();
        $project = $model->find($id);
        if (!$project) {
            \flash('danger', 'Không tìm thấy dự án.');
            \redirect('du_an/index');
        }

        if (!$model->userCanManageProject(Auth::user(), $id)) {
            \flash('danger', 'Ban khong co quyen xoa du an nay.');
            \redirect('du_an/index');
        }

        if (!\project_can_delete($project, Auth::user())) {
            \flash('warning', 'Chỉ có thể xóa dự án khi đang ở trạng thái lên kế hoạch.');
            \redirect('du_an/index');
        }

        $ok = $model->delete($id);
        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'delete', 'project', $id, 'Xóa dự án');
            \flash('success', 'Đã xóa dự án.');
        } else {
            \flash('danger', 'Không thể xóa dự án.');
        }

        \redirect('du_an/index');
    }

    /**
     * Hiển thị danh sách thành viên và form thêm thành viên của một dự án.
     */
    public function members(int $id): void
    {
        $this->requireLogin();
        $model = new Project();

        $project = $model->find($id);
        $user = Auth::user();
        $canAccessProject = $project
            && (
                ($user['role'] ?? '') === 'admin'
                || (int) $project['created_by'] === (int) $user['id']
                || $model->userBelongsToProject($id, (int) $user['id'])
            );
        if (!$canAccessProject) {
            http_response_code(403);
            exit('Ban khong co quyen xem du an nay.');
        }

        $members = $model->members($id);
        $canManageProject = $model->userCanManageProject($user, $id);
        $availableUsers = $canManageProject ? $model->availableEmployees($id) : [];
        $projectFiles = (new Attachment())->byProject($id);

        $this->render('du_an/members', compact('project', 'members', 'availableUsers', 'projectFiles', 'canManageProject'), 'Thanh vien du an');
    }

    /**
     * Thêm thành viên vào dự án.
     */
    public function addMember(int $projectId): void
    {
        $this->requireLogin();
        if (!\is_post()) {
            \redirect('du_an/members', ['id' => $projectId]);
        }

        if (!(new Project())->userCanManageProject(Auth::user(), $projectId)) {
            \flash('danger', 'Ban khong co quyen them thanh vien vao du an nay.');
            \redirect('du_an/members', ['id' => $projectId]);
        }

        $validator = \App\Core\Validator::make($_POST, [
            'user_id' => 'required|positive_int',
            'project_role' => 'required|in:lead,member',
        ]);

        if ($validator->fails()) {
            \flash('danger', $validator->firstError());
            \redirect('du_an/members', ['id' => $projectId]);
        }

        $validated = $validator->validated();
        $userId = (int) $validated['user_id'];
        $projectRole = $validated['project_role'];
        $ok = (new Project())->addMember($projectId, $userId, $projectRole);

        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'create', 'project_member', $projectId, 'Thêm thành viên vào dự án');
            \flash('success', 'Đã thêm thành viên vào dự án.');
        } else {
            \flash('danger', 'Không thể thêm thành viên.');
        }

        \redirect('du_an/members', ['id' => $projectId]);
    }

    /**
     * Xóa thành viên khỏi dự án.
     */
    public function removeMember(int $projectId): void
    {
        $this->requireLogin();
        $userId = \App\Core\Validator::validateId($_GET['user_id'] ?? null);

        if ($userId === null) {
            \flash('danger', 'ID thành viên không hợp lệ.');
            \redirect('du_an/members', ['id' => $projectId]);
        }

        if (!(new Project())->userCanManageProject(Auth::user(), $projectId)) {
            \flash('danger', 'Ban khong co quyen xoa thanh vien khoi du an nay.');
            \redirect('du_an/members', ['id' => $projectId]);
        }

        $ok = (new Project())->removeMember($projectId, $userId);
        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'delete', 'project_member', $projectId, 'Xóa thành viên khỏi dự án');
            \flash('success', 'Đã xóa thành viên khỏi dự án.');
        } else {
            \flash('danger', 'Không thể xóa thành viên.');
        }

        \redirect('du_an/members', ['id' => $projectId]);
    }

    /**
     * Gom dữ liệu dự án từ form.
     */
    private function projectPayloadFromPost(array $extra = []): array
    {
        return array_merge([
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'priority' => $_POST['priority'] ?? 'medium',
            'status' => $_POST['status'] ?? 'active',
        ], $extra);
    }

    /**
     * Kiểm tra logic ngày của dự án.
     */
    private function validateDateRange(?string $startDate, ?string $endDate): ?string
    {
        if (!$startDate || !$endDate) {
            return null;
        }

        $start = strtotime($startDate);
        $end = strtotime($endDate);
        if ($start === false || $end === false) {
            return 'Ngày dự án không đúng định dạng.';
        }

        if ($start > $end) {
            return 'Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc.';
        }

        return null;
    }

    /**
     * Xử lý upload file dự án từ manager và mã hóa trước lưu.
     */
    private function handleProjectFileUpload(int $projectId): void
    {
        $user = Auth::user();
        $file = $_FILES['project_attachment'] ?? null;

        if (!$file || empty($file['name'])) {
            return;
        }

        try {
            $reason = trim($_POST['upload_reason'] ?? '');
            $attachmentId = (new AttachmentModuleService())->storeE2EUpload($file, [
                'task_id' => null,
                'project_id' => $projectId,
                'module_name' => 'du_an',
                'uploaded_by' => $user['id'],
                'file_type' => 'project_manager',
                'upload_reason' => $reason ?: null,
                'encrypted_key' => null,
            ]);

            if ($attachmentId !== false) {
                (new Log())->create($user['id'], 'upload', 'project_attachment', $projectId, "Upload file dự án: $reason");
                \flash('info', 'File tài liệu dự án đã upload và mã hóa.');
            } else {
                \flash('warning', 'Không thể lưu metadata file dự án.');
            }
        } catch (\Throwable $e) {
            \flash('warning', 'Upload file dự án thất bại: ' . $e->getMessage());
        }
    }
}
