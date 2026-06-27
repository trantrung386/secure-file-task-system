<div class="section-header">
    <div>
        <div class="section-title">Quản lý công việc</div>
        <p class="section-desc">Theo dõi danh sách công việc theo trạng thái và thao tác phù hợp.</p>
    </div>
    <?php if (can_manage_tasks()): ?>
        <a href="<?= e(route_url('cong_viec/create')) ?>" class="btn btn-primary">+ Tạo công việc</a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form class="row g-3">
            <input type="hidden" name="route" value="cong_viec/index">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Lọc theo dự án</label>
                <select class="form-select" name="project_id">
                    <option value="">Tất cả dự án</option>
                    <?php foreach ($du_an as $project): ?>
                        <option value="<?= e((string) $project['id']) ?>" <?= ((string) ($filters['project_id'] ?? '') === (string) $project['id']) ? 'selected' : '' ?>><?= e($project['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Lọc theo trạng thái</label>
                <select class="form-select" name="status">
                    <?php foreach (['' => 'Tất cả trạng thái', 'new' => 'Mới tạo', 'assigned' => 'Đã phân công', 'in_progress' => 'Đang thực hiện', 'blocked' => 'Bị chặn', 'submitted' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'redo' => 'Yêu cầu làm lại'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= (($filters['status'] ?? '') === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-outline-primary">Áp dụng bộ lọc</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/index')) ?>">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Công việc</th>
                    <th>Dự án</th>
                    <th>Người phụ trách</th>
                    <th>Trạng thái</th>
                    <th>Hạn chót</th>
                    <th class="task-actions-head"><span class="task-actions-head-grid"><span class="task-actions-head-label">Thao tác</span></span></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cong_viec as $task): ?>
                <?php
                    $primaryActionLabel = task_primary_action_label($task, current_user());
                    $primaryActionUrl = task_primary_action_url($task, current_user());
                ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= e($task['title']) ?></div>
                        <?php $exp = (int) ($task['expected_score'] ?? 0); $exp = max(0, min(100, $exp)); ?>
                        <div class="small text-secondary"><?= e(priority_label($task['priority'])) ?> • KPI <?= e((string) $exp) ?>%</div>
                    </td>
                    <td><?= e($task['project_name']) ?></td>
                    <td title="<?= e($task['assignee_names'] ?? 'Chưa phân công') ?>"><?= e(task_assignee_summary($task['assignee_names'] ?? '')) ?></td>
                    <td><span class="badge text-bg-<?= e(status_badge_class($task['status'])) ?>"><?= e(status_label($task['status'])) ?></span></td>
                    <td><?= e(format_date($task['deadline'])) ?></td>
                    <td class="task-actions-cell">
                        <div class="task-actions-grid">
                            <div class="task-action-slot">
                                <a class="btn btn-sm btn-outline-dark w-100" href="<?= e(route_url('cong_viec/show', ['id' => $task['id']])) ?>">Chi tiết</a>
                            </div>
                            <div class="task-action-slot">
                                <?php if ($primaryActionLabel && $primaryActionUrl): ?>
                                    <a class="btn btn-sm btn-outline-primary w-100" href="<?= e($primaryActionUrl) ?>"><?= e($primaryActionLabel) ?></a>
                                <?php else: ?>
                                    <span class="task-action-placeholder"></span>
                                <?php endif; ?>
                            </div>
                            <div class="task-action-slot">
                                <?php if (task_can_delete($task)): ?>
                                    <form method="post" action="<?= e(route_url('cong_viec/destroy', ['id' => $task['id']])) ?>" onsubmit="return confirm('Bạn có chắc muốn xóa công việc này không?');">
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Xóa</button>
                                    </form>
                                <?php else: ?>
                                    <span class="task-action-placeholder"></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$cong_viec): ?>
                <tr><td colspan="6" class="text-muted">Không có dữ liệu công việc phù hợp.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
