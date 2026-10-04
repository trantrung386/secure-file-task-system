<div class="section-header">
    <div>
        <div class="section-title"><?= e($project['name']) ?></div>
        <p class="section-desc"><?= e($project['code']) ?> - Chi tiết dự án, công việc và tài liệu đính kèm.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($canManageProject): ?>
            <a class="btn btn-outline-primary" href="<?= e(route_url('du_an/edit', ['id' => $project['id']])) ?>">Cập nhật</a>
        <?php endif; ?>
        <a class="btn btn-outline-dark" href="<?= e(route_url('du_an/members', ['id' => $project['id']])) ?>">Thành viên</a>
        <a class="btn btn-outline-secondary" href="<?= e(route_url('du_an/index')) ?>">Quay lại</a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><strong>Trạng thái:</strong> <span class="badge text-bg-<?= e(status_badge_class($project['status'])) ?>"><?= e(status_label($project['status'])) ?></span></div>
            <div class="col-md-4"><strong>Ưu tiên:</strong> <?= e(priority_label($project['priority'])) ?></div>
            <div class="col-md-4"><strong>Thời gian:</strong> <?= e(format_date($project['start_date'])) ?> - <?= e(format_date($project['end_date'])) ?></div>
        </div>
        <?php if (!empty($project['description'])): ?>
            <hr>
            <h2 class="h6 fw-bold">Mô tả dự án</h2>
            <p class="mb-0"><?= nl2br(e($project['description'])) ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Công việc của dự án</span>
                <span class="badge text-bg-secondary"><?= e((string) count($tasks)) ?></span>
            </div>
            <div class="card-body table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Công việc</th><th>Phụ trách</th><th>Hạn chót</th><th>Trạng thái</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><div class="fw-semibold"><?= e($task['title']) ?></div><div class="small text-secondary"><?= e(priority_label($task['priority'])) ?></div></td>
                            <td><?= e($task['assignee_names'] ?? 'Chưa phân công') ?></td>
                            <td><?= e(format_date($task['deadline'])) ?></td>
                            <td><span class="badge text-bg-<?= e(status_badge_class($task['status'])) ?>"><?= e(status_label($task['status'])) ?></span></td>
                            <td><a class="btn btn-sm btn-outline-primary" href="<?= e(route_url('cong_viec/show', ['id' => $task['id']])) ?>">Xem</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$tasks): ?>
                        <tr><td colspan="5" class="text-muted">Chưa có công việc thuộc dự án này.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>File dự án</span>
                <span class="badge text-bg-secondary"><?= e((string) count($projectFiles)) ?></span>
            </div>
            <div class="card-body">
                <?php foreach ($projectFiles as $file): ?>
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="fw-semibold text-break"><?= e($file['original_name']) ?></div>
                        <div class="small text-secondary">Tải lên bởi <?= e($file['uploader_name']) ?> - <?= e(format_datetime($file['created_at'])) ?></div>
                        <?php if (!empty($file['upload_reason'])): ?><div class="small text-muted mt-1"><?= e($file['upload_reason']) ?></div><?php endif; ?>
                        <a class="btn btn-sm btn-outline-primary mt-2" href="<?= e(route_url('files/download', ['id' => $file['id']])) ?>">Tải file</a>
                    </div>
                <?php endforeach; ?>
                <?php if (!$projectFiles): ?><div class="text-muted">Chưa có file dự án.</div><?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header">Thành viên dự án (<?= e((string) count($members)) ?>)</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($members as $member): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?= e($member['name']) ?></span>
                        <span class="small text-secondary"><?= e(match ($member['project_role']) { 'lead' => 'Trưởng nhóm', 'reviewer' => 'Người duyệt', default => 'Thành viên' }) ?></span>
                    </li>
                <?php endforeach; ?>
                <?php if (!$members): ?><li class="list-group-item text-muted">Chưa có thành viên.</li><?php endif; ?>
            </ul>
        </div>
    </div>
</div>
