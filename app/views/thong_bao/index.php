<?php
$notifTypeBadge = static function (string $type): array {
    return match ($type) {
        'task_assigned' => ['Phân công việc', 'info'],
        'task_status' => ['Cập nhật tiến độ', 'primary'],
        'task_reviewed' => ['Kết quả duyệt', 'success'],
        'project_member' => ['Thành viên dự án', 'warning'],
        default => ['Thông báo quản lý', 'dark'],
    };
};

$totalInbox = count($inboxNotifications);
$readInbox = 0;
foreach ($inboxNotifications as $item) {
    if (!empty($item['is_read'])) {
        $readInbox++;
    }
}
?>
<div class="section-header">
    <div>
        <div class="section-title"><?= $isManagerOrAdmin ? 'Quản lý thông báo' : 'Xem thông báo' ?></div>
        <p class="section-desc">
            <?php if ($isManagerOrAdmin): ?>
                Tạo, chỉnh sửa, xóa thông báo gửi đến dự án/nhân viên và theo dõi các thông báo tự động từ công việc.
            <?php else: ?>
                Theo dõi thông báo từ Quản lý/Trưởng phòng và các cập nhật công việc, dự án của bạn.
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($isManagerOrAdmin): ?>
            <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#createNotificationCollapse" aria-expanded="false">
                + Tạo thông báo mới
            </button>
        <?php endif; ?>
        <?php if ($unreadCount > 0): ?>
            <form method="post" action="<?= e(route_url('thong_bao/markAllRead')) ?>">
                <button type="submit" class="btn btn-outline-success">
                    Đánh dấu đã đọc tất cả (<?= e((string) $unreadCount) ?>)
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($isManagerOrAdmin): ?>
    <div class="collapse mb-4" id="createNotificationCollapse">
        <div class="card border-0 shadow-sm">
            <div class="card-header">Tạo và gửi thông báo mới</div>
            <div class="card-body">
                <form method="post" action="<?= e(route_url('thong_bao/store')) ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tiêu đề thông báo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" required maxlength="255" placeholder="Ví dụ: Thông báo họp tiến độ dự án tuần này">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Phạm vi gửi <span class="text-danger">*</span></label>
                            <select class="form-select" name="scope" id="notifScopeSelect">
                                <option value="project">Theo dự án</option>
                                <option value="user">Chọn người nhận cụ thể</option>
                                <option value="all_projects">
                                    <?= ($user['role'] ?? '') === 'admin' ? 'Toàn bộ người dùng hệ thống' : 'Tất cả thành viên các dự án của tôi' ?>
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3" id="notifProjectGroup">
                            <label class="form-label fw-semibold">Dự án áp dụng</label>
                            <select class="form-select" name="project_id">
                                <option value="">-- Chọn dự án --</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?= e((string) $project['id']) ?>">
                                        <?= e($project['code'] . ' - ' . $project['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12 d-none" id="notifRecipientsGroup">
                            <label class="form-label fw-semibold">Chọn người nhận <span class="text-danger">*</span></label>
                            <div class="border rounded-3 p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                                <div class="row g-2">
                                    <?php foreach ($availableRecipients as $rec): ?>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="recipient_ids[]" value="<?= e((string) $rec['id']) ?>" id="recCheck<?= (int) $rec['id'] ?>">
                                                <label class="form-check-label small" for="recCheck<?= (int) $rec['id'] ?>">
                                                    <strong><?= e($rec['name']) ?></strong> (<?= e(role_label($rec['role'] ?? 'employee')) ?>)
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (!$availableRecipients): ?>
                                        <div class="text-muted small">Chưa có thành viên nào trong dự án.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Nội dung thông báo <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="content" rows="3" required placeholder="Nhập nội dung chi tiết thông báo..."></textarea>
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Gửi thông báo</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#createNotificationCollapse">Hủy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'manage' ? 'active' : 'bg-white border text-dark' ?>"
               href="<?= e(route_url('thong_bao/index', ['tab' => 'manage'])) ?>">
                Quản lý thông báo (<?= e((string) count($managedNotifications)) ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'inbox' ? 'active' : 'bg-white border text-dark' ?>"
               href="<?= e(route_url('thong_bao/index', ['tab' => 'inbox'])) ?>">
                Hộp thư của tôi
                <?php if ($unreadCount > 0): ?>
                    <span class="badge text-bg-danger ms-1"><?= e((string) $unreadCount) ?></span>
                <?php endif; ?>
            </a>
        </li>
    </ul>
<?php else: ?>
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="metric-box h-100">
                <div class="metric-label">Tổng thông báo</div>
                <div class="metric-value"><?= e((string) $totalInbox) ?></div>
            </div>
        </div>
        <div class="col-4">
            <div class="metric-box h-100">
                <div class="metric-label">Chưa đọc</div>
                <div class="metric-value text-danger"><?= e((string) $unreadCount) ?></div>
            </div>
        </div>
        <div class="col-4">
            <div class="metric-box h-100">
                <div class="metric-label">Đã đọc</div>
                <div class="metric-value text-success"><?= e((string) $readInbox) ?></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="index.php" class="row g-3 align-items-end">
            <input type="hidden" name="route" value="thong_bao/index">
            <input type="hidden" name="tab" value="<?= e($activeTab) ?>">

            <?php if ($activeTab === 'inbox'): ?>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select class="form-select" name="read_status">
                        <option value="">Tất cả</option>
                        <option value="unread" <?= ($filters['read_status'] === 'unread') ? 'selected' : '' ?>>Chưa đọc</option>
                        <option value="read" <?= ($filters['read_status'] === 'read') ? 'selected' : '' ?>>Đã đọc</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Dự án</label>
                <select class="form-select" name="project_id">
                    <option value="">Tất cả dự án</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= e((string) $project['id']) ?>" <?= ($filters['project_id'] === (string) $project['id']) ? 'selected' : '' ?>>
                            <?= e($project['code'] . ' - ' . $project['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Loại thông báo</label>
                <select class="form-select" name="type">
                    <option value="">Tất cả loại</option>
                    <option value="manual" <?= ($filters['type'] === 'manual') ? 'selected' : '' ?>>Thông báo từ Quản lý</option>
                    <option value="task_assigned" <?= ($filters['type'] === 'task_assigned') ? 'selected' : '' ?>>Phân công công việc</option>
                    <option value="task_status" <?= ($filters['type'] === 'task_status') ? 'selected' : '' ?>>Cập nhật tiến độ</option>
                    <option value="task_reviewed" <?= ($filters['type'] === 'task_reviewed') ? 'selected' : '' ?>>Kết quả duyệt việc</option>
                    <option value="project_member" <?= ($filters['type'] === 'project_member') ? 'selected' : '' ?>>Thành viên dự án</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Từ khóa</label>
                <input type="text" class="form-control" name="keyword" value="<?= e($filters['keyword']) ?>" placeholder="Tiêu đề, nội dung...">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Lọc</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('thong_bao/index', ['tab' => $activeTab])) ?>">Xóa</a>
            </div>
        </form>
    </div>
</div>

<?php if ($isManagerOrAdmin && $activeTab === 'manage'): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Danh sách thông báo quản lý</span>
            <span class="badge text-bg-secondary"><?= e((string) count($managedNotifications)) ?> thông báo</span>
        </div>
        <div class="card-body table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Thông báo</th>
                        <th>Loại</th>
                        <th>Dự án / Công việc</th>
                        <th>Người nhận</th>
                        <th class="text-center">Đã đọc</th>
                        <th>Thời gian</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($managedNotifications as $notif): ?>
                    <?php [$typeLabel, $typeColor] = $notifTypeBadge((string) ($notif['type'] ?? 'manual')); ?>
                    <tr>
                        <td style="min-width: 260px;">
                            <div class="fw-semibold"><?= e($notif['title']) ?></div>
                            <div class="small text-secondary mt-1"><?= nl2br(e($notif['content'])) ?></div>
                            <div class="small text-muted mt-1">Người gửi: <strong><?= e($notif['sender_name'] ?? 'Hệ thống') ?></strong></div>
                        </td>
                        <td><span class="badge text-bg-<?= e($typeColor) ?>"><?= e($typeLabel) ?></span></td>
                        <td>
                            <?php if (!empty($notif['project_name'])): ?>
                                <div class="fw-semibold"><?= e($notif['project_name']) ?></div>
                            <?php else: ?>
                                <span class="text-muted small">Chung / Cá nhân</span>
                            <?php endif; ?>
                            <?php if (!empty($notif['task_title'])): ?>
                                <div class="small text-secondary">Việc: <?= e($notif['task_title']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="small"><?= e(task_assignee_summary((string) ($notif['recipient_names'] ?? ''), 3)) ?></div>
                        </td>
                        <td class="text-center">
                            <span class="badge text-bg-light border text-dark">
                                <?= e((string) ($notif['read_count'] ?? 0)) ?> / <?= e((string) ($notif['total_recipients'] ?? 0)) ?>
                            </span>
                        </td>
                        <td><?= e(format_datetime($notif['created_at'])) ?></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a class="btn btn-sm btn-outline-primary" href="<?= e(route_url('thong_bao/edit', ['id' => $notif['id']])) ?>">Sửa</a>
                                <form method="post"
                                      action="<?= e(route_url('thong_bao/destroy', ['id' => $notif['id']])) ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$managedNotifications): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Chưa có thông báo nào trong danh sách quản lý.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Danh sách thông báo nhận được</span>
            <span class="badge text-bg-secondary"><?= e((string) count($inboxNotifications)) ?> thông báo</span>
        </div>
        <div class="card-body">
            <?php foreach ($inboxNotifications as $notif): ?>
                <?php
                $isUnread = empty($notif['is_read']);
                [$typeLabel, $typeColor] = $notifTypeBadge((string) ($notif['type'] ?? 'manual'));
                $hasLink = !empty($notif['task_id']) || !empty($notif['project_id']);
                ?>
                <div class="border rounded-4 p-3 mb-3 <?= $isUnread ? 'bg-light border-primary' : '' ?>"
                     style="<?= $isUnread ? 'border-left: 4px solid #2563eb !important;' : '' ?>">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <?php if ($isUnread): ?>
                                    <span class="badge text-bg-danger">Mới</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Đã đọc</span>
                                <?php endif; ?>
                                <span class="badge text-bg-<?= e($typeColor) ?>"><?= e($typeLabel) ?></span>
                                <span class="fw-bold fs-6"><?= e($notif['title']) ?></span>
                            </div>

                            <div class="text-dark my-2"><?= nl2br(e($notif['content'])) ?></div>

                            <div class="small text-secondary d-flex flex-wrap gap-3">
                                <span>Từ: <strong><?= e($notif['sender_name'] ?? 'Hệ thống') ?></strong></span>
                                <span>Thời gian: <?= e(format_datetime($notif['created_at'])) ?></span>
                                <?php if (!empty($notif['project_name'])): ?>
                                    <span>Dự án: <strong><?= e($notif['project_name']) ?></strong></span>
                                <?php endif; ?>
                                <?php if (!empty($notif['task_title'])): ?>
                                    <span>Công việc: <strong><?= e($notif['task_title']) ?></strong></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <?php if ($hasLink): ?>
                                <a class="btn btn-sm btn-primary"
                                   href="<?= e(route_url('thong_bao/markRead', ['id' => $notif['id'], 'open' => 1])) ?>">
                                    Xem chi tiết
                                </a>
                            <?php endif; ?>
                            <?php if ($isUnread): ?>
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="<?= e(route_url('thong_bao/markRead', ['id' => $notif['id']])) ?>">
                                    Đánh dấu đã đọc
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!$inboxNotifications): ?>
                <div class="text-center text-muted py-4">Bạn chưa có thông báo nào.</div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script>
(() => {
    const scopeSelect = document.getElementById('notifScopeSelect');
    const projectGroup = document.getElementById('notifProjectGroup');
    const recipientsGroup = document.getElementById('notifRecipientsGroup');
    if (!scopeSelect || !projectGroup || !recipientsGroup) return;

    const syncScopeUI = () => {
        const val = scopeSelect.value;
        projectGroup.classList.toggle('d-none', val !== 'project');
        recipientsGroup.classList.toggle('d-none', val !== 'user');
    };

    scopeSelect.addEventListener('change', syncScopeUI);
    syncScopeUI();
})();
</script>

