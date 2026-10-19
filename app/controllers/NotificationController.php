<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Log;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;

/**
 * Controller xử lý Quản lý thông báo (Quản lý/Trưởng phòng, Admin) và Xem thông báo (Nhân viên).
 */
class NotificationController extends BaseController
{
    /**
     * Hiển thị danh sách thông báo theo vai trò.
     */
    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();
        $isManagerOrAdmin = in_array($user['role'] ?? '', ['manager', 'admin'], true);

        $rawReadStatus = trim((string) ($_GET['read_status'] ?? ''));
        $rawType = trim((string) ($_GET['type'] ?? ''));
        $rawProjectId = $_GET['project_id'] ?? '';
        $rawKeyword = trim((string) ($_GET['keyword'] ?? ''));
        $rawTab = trim((string) ($_GET['tab'] ?? ($isManagerOrAdmin ? 'manage' : 'inbox')));

        $cleanReadStatus = in_array($rawReadStatus, ['unread', 'read'], true) ? $rawReadStatus : '';
        $cleanType = in_array($rawType, ['manual', 'task_assigned', 'task_status', 'task_reviewed', 'project_member'], true) ? $rawType : '';
        $cleanProjectId = \App\Core\Validator::validateId($rawProjectId);
        $cleanKeyword = \App\Core\Validator::sanitizeString($rawKeyword);
        if (mb_strlen($cleanKeyword, 'UTF-8') > 100) {
            $cleanKeyword = mb_substr($cleanKeyword, 0, 100, 'UTF-8');
        }
        $activeTab = ($isManagerOrAdmin && in_array($rawTab, ['manage', 'inbox'], true)) ? $rawTab : 'inbox';

        $filters = [
            'read_status' => $cleanReadStatus,
            'type' => $cleanType,
            'project_id' => $cleanProjectId !== null ? (string) $cleanProjectId : '',
            'keyword' => $cleanKeyword,
        ];

        $notificationModel = new Notification();
        $inboxNotifications = $notificationModel->forRecipient((int) $user['id'], $filters);
        $unreadCount = $notificationModel->unreadCount((int) $user['id']);

        $managedNotifications = [];
        $projects = (new Project())->forUser($user);
        $availableRecipients = [];

        if ($isManagerOrAdmin) {
            $managedNotifications = $notificationModel->managedByUser($user, $filters);
            $availableRecipients = $this->recipientsForManagerOrAdmin($user, $projects);
        }

        $pageTitle = $isManagerOrAdmin ? 'Quản lý thông báo' : 'Xem thông báo';
        $this->render('thong_bao/index', [
            'user' => $user,
            'isManagerOrAdmin' => $isManagerOrAdmin,
            'activeTab' => $activeTab,
            'inboxNotifications' => $inboxNotifications,
            'managedNotifications' => $managedNotifications,
            'unreadCount' => $unreadCount,
            'projects' => $projects,
            'availableRecipients' => $availableRecipients,
            'filters' => $filters,
        ], $pageTitle);
    }

    /**
     * Tạo và gửi thông báo mới (dành cho Quản lý/Trưởng phòng và Admin).
     */
    public function store(): void
    {
        $this->requireRole(['manager', 'admin']);
        $this->requirePostCsrf();

        $user = Auth::user();
        $validator = \App\Core\Validator::make($_POST, [
            'title' => 'required|string|min:2|max:255',
            'content' => 'required|string|min:2|max:3000',
            'scope' => 'required|in:project,user,all_projects',
        ]);

        if ($validator->fails()) {
            \flash('danger', $validator->firstError());
            \redirect('thong_bao/index', ['tab' => 'manage']);
        }

        $validated = $validator->validated();
        $scope = $validated['scope'];
        $projectId = \App\Core\Validator::validateId($_POST['project_id'] ?? null);

        $projectModel = new Project();
        $projects = $projectModel->forUser($user);
        $allowedProjectIds = array_map(static fn (array $p): int => (int) $p['id'], $projects);

        $recipientIds = $this->resolveRecipientsByScope($user, $scope, $projectId, $allowedProjectIds, $_POST['recipient_ids'] ?? []);
        if (!$recipientIds) {
            \flash('danger', 'Không tìm thấy người nhận hợp lệ cho phạm vi thông báo đã chọn.');
            \redirect('thong_bao/index', ['tab' => 'manage']);
        }

        $linkUrl = null;
        if ($projectId !== null && in_array($projectId, $allowedProjectIds, true)) {
            $linkUrl = \route_url('du_an/show', ['id' => $projectId]);
        }

        $notificationId = (new Notification())->createNotification([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => 'manual',
            'scope' => $scope,
            'project_id' => ($scope === 'project' && $projectId !== null) ? $projectId : null,
            'task_id' => null,
            'link_url' => $linkUrl,
            'created_by' => (int) $user['id'],
        ], $recipientIds);

        if ($notificationId !== false) {
            (new Log())->create((int) $user['id'], 'create', 'notification', (int) $notificationId, 'Tạo thông báo: ' . $validated['title']);
            \flash('success', 'Đã tạo và gửi thông báo thành công tới ' . count($recipientIds) . ' thành viên.');
        } else {
            \flash('danger', 'Không thể tạo thông báo.');
        }

        \redirect('thong_bao/index', ['tab' => 'manage']);
    }

    /**
     * Mở form chỉnh sửa thông báo.
     */
    public function edit(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        $user = Auth::user();

        $notificationModel = new Notification();
        $notification = $notificationModel->find($id);
        if (!$notification) {
            \flash('danger', 'Không tìm thấy thông báo.');
            \redirect('thong_bao/index', ['tab' => 'manage']);
        }

        if (!$notificationModel->canManage($user, $notification)) {
            \flash('danger', 'Bạn không có quyền chỉnh sửa thông báo này.');
            \redirect('thong_bao/index', ['tab' => 'manage']);
        }

        $projects = (new Project())->forUser($user);
        $availableRecipients = $this->recipientsForManagerOrAdmin($user, $projects);

        $this->render('thong_bao/form', [
            'user' => $user,
            'notification' => $notification,
            'projects' => $projects,
            'availableRecipients' => $availableRecipients,
        ], 'Cập nhật thông báo');
    }

    /**
     * Lưu thông tin cập nhật thông báo.
     */
    public function update(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        $this->requirePostCsrf();

        $user = Auth::user();
        $notificationModel = new Notification();
        $notification = $notificationModel->find($id);

        if (!$notification) {
            \flash('danger', 'Không tìm thấy thông báo.');
            \redirect('thong_bao/index', ['tab' => 'manage']);
        }

        if (!$notificationModel->canManage($user, $notification)) {
            \flash('danger', 'Bạn không có quyền chỉnh sửa thông báo này.');
            \redirect('thong_bao/index', ['tab' => 'manage']);
        }

        $validator = \App\Core\Validator::make($_POST, [
            'title' => 'required|string|min:2|max:255',
            'content' => 'required|string|min:2|max:3000',
            'scope' => 'required|in:project,user,all_projects',
        ]);

        if ($validator->fails()) {
            \flash('danger', $validator->firstError());
            \redirect('thong_bao/edit', ['id' => $id]);
        }

        $validated = $validator->validated();
        $scope = $validated['scope'];
        $projectId = \App\Core\Validator::validateId($_POST['project_id'] ?? null);

        $projects = (new Project())->forUser($user);
        $allowedProjectIds = array_map(static fn (array $p): int => (int) $p['id'], $projects);

        $recipientIds = $this->resolveRecipientsByScope($user, $scope, $projectId, $allowedProjectIds, $_POST['recipient_ids'] ?? []);
        if (!$recipientIds) {
            \flash('danger', 'Vui lòng chọn ít nhất một người nhận hợp lệ.');
            \redirect('thong_bao/edit', ['id' => $id]);
        }

        $ok = $notificationModel->updateNotification($id, [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'scope' => $scope,
            'project_id' => ($scope === 'project' && $projectId !== null) ? $projectId : null,
        ], $recipientIds);

        if ($ok) {
            (new Log())->create((int) $user['id'], 'update', 'notification', $id, 'Cập nhật thông báo: ' . $validated['title']);
            \flash('success', 'Đã cập nhật thông báo thành công.');
        } else {
            \flash('danger', 'Cập nhật thông báo thất bại.');
        }

        \redirect('thong_bao/index', ['tab' => 'manage']);
    }

    /**
     * Xóa thông báo.
     */
    public function destroy(int $id): void
    {
        $this->requireRole(['manager', 'admin']);
        $this->requirePostCsrf();

        $user = Auth::user();
        $notificationModel = new Notification();
        $notification = $notificationModel->find($id);

        if (!$notification) {
            \flash('danger', 'Không tìm thấy thông báo cần xóa.');
            \redirect('thong_bao/index', ['tab' => 'manage']);
        }

        if (!$notificationModel->canManage($user, $notification)) {
            \flash('danger', 'Bạn không có quyền xóa thông báo này.');
            \redirect('thong_bao/index', ['tab' => 'manage']);
        }

        if ($notificationModel->deleteNotification($id)) {
            (new Log())->create((int) $user['id'], 'delete', 'notification', $id, 'Xóa thông báo: ' . $notification['title']);
            \flash('success', 'Đã xóa thông báo.');
        } else {
            \flash('danger', 'Không thể xóa thông báo.');
        }

        \redirect('thong_bao/index', ['tab' => 'manage']);
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc (hỗ trợ mở liên kết kèm theo).
     */
    public function markRead(int $id): void
    {
        $this->requireLogin();
        $user = Auth::user();

        $notificationModel = new Notification();
        $notificationModel->markAsRead($id, (int) $user['id']);

        if (!empty($_GET['open'])) {
            $notification = $notificationModel->find($id);
            if ($notification) {
                if (!empty($notification['task_id'])) {
                    \redirect('cong_viec/show', ['id' => (int) $notification['task_id']]);
                }
                if (!empty($notification['project_id'])) {
                    \redirect('du_an/show', ['id' => (int) $notification['project_id']]);
                }
            }
        }

        \redirect('thong_bao/index', ['tab' => 'inbox']);
    }

    /**
     * Đánh dấu tất cả thông báo của người dùng hiện tại là đã đọc.
     */
    public function markAllRead(): void
    {
        $this->requireLogin();
        $this->requirePostCsrf();

        $user = Auth::user();
        (new Notification())->markAllAsRead((int) $user['id']);
        \flash('success', 'Đã đánh dấu đọc tất cả thông báo.');
        \redirect('thong_bao/index', ['tab' => 'inbox']);
    }

    /**
     * Lấy danh sách người nhận thuộc các dự án mà Quản lý tham gia (hoặc toàn bộ nếu là Admin).
     */
    private function recipientsForManagerOrAdmin(array $user, array $projects): array
    {
        if (($user['role'] ?? '') === 'admin') {
            return array_values(array_filter(
                (new User())->all(),
                static fn (array $u): bool => ($u['status'] ?? '') === 'active'
            ));
        }

        $projectModel = new Project();
        $map = [];
        foreach ($projects as $project) {
            $members = $projectModel->members((int) $project['id']);
            foreach ($members as $member) {
                $uid = (int) ($member['user_id'] ?? 0);
                if ($uid > 0 && ($member['status'] ?? 'active') === 'active') {
                    $map[$uid] = [
                        'id' => $uid,
                        'name' => $member['name'],
                        'email' => $member['email'],
                        'role' => $member['role'],
                    ];
                }
            }
        }

        return array_values($map);
    }

    /**
     * Xác định danh sách ID người nhận theo phạm vi gửi thông báo.
     */
    private function resolveRecipientsByScope(
        array $user,
        string $scope,
        ?int $projectId,
        array $allowedProjectIds,
        mixed $rawRecipientIds
    ): array {
        $projectModel = new Project();

        if ($scope === 'project') {
            if ($projectId === null || (!in_array($projectId, $allowedProjectIds, true) && ($user['role'] ?? '') !== 'admin')) {
                return [];
            }
            $members = $projectModel->members($projectId);
            return array_map(static fn (array $m): int => (int) $m['user_id'], $members);
        }

        if ($scope === 'all_projects') {
            if (($user['role'] ?? '') === 'admin') {
                $allUsers = (new User())->all();
                $ids = [];
                foreach ($allUsers as $u) {
                    if (($u['status'] ?? '') === 'active') {
                        $ids[] = (int) $u['id'];
                    }
                }
                return $ids;
            }

            $ids = [];
            foreach ($allowedProjectIds as $pid) {
                foreach ($projectModel->members($pid) as $m) {
                    $ids[] = (int) $m['user_id'];
                }
            }
            return array_values(array_unique($ids));
        }

        if ($scope === 'user' && is_array($rawRecipientIds)) {
            $allowedUsers = $this->recipientsForManagerOrAdmin($user, $projectModel->forUser($user));
            $allowedUserIds = array_map(static fn (array $u): int => (int) $u['id'], $allowedUsers);
            $selected = array_map('intval', $rawRecipientIds);
            return array_values(array_intersect($selected, $allowedUserIds));
        }

        return [];
    }
}

