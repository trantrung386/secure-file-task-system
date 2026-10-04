<div class="section-header">
    <div>
        <div class="section-title">Quản lý dự án</div>
        <p class="section-desc">Theo dõi dự án, thành viên và file dự án.</p>
    </div>
    <?php if (in_array(current_user()['role'] ?? '', ['admin', 'manager'], true)): ?>
        <a href="<?= e(route_url('du_an/create')) ?>" class="btn btn-primary">+ Tạo dự án</a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form class="row g-3" method="get">
            <input type="hidden" name="route" value="du_an/index">
            <div class="col-md-9">
                <label class="form-label fw-semibold">Tìm kiếm dự án</label>
                <input type="text" class="form-control" name="keyword" placeholder="Nhập mã dự án, tên hoặc mô tả dự án..." value="<?= e($keyword ?? '') ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button class="btn btn-outline-primary flex-grow-1" type="submit">Tìm kiếm</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('du_an/index')) ?>">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
        <?php $projectAccessModel = new \App\Models\Project(); ?>
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Mã dự án</th>
                    <th>Tên dự án</th>
                    <th>Ưu tiên</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                    <th>Người tạo</th>
                    <th class="project-actions-head"><span class="project-actions-head-grid"><span class="project-actions-head-label">Thao tác</span></span></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($du_an as $project): ?>
                <?php $canManageThisProject = $projectAccessModel->userCanManageProject(current_user(), (int) $project['id']); ?>
                <tr>
                    <td><?= e($project['code']) ?></td>
                    <td><div class="fw-semibold"><?= e($project['name']) ?></div></td>
                    <td><?= e(priority_label($project['priority'])) ?></td>
                    <td><span class="badge text-bg-<?= e(status_badge_class($project['status'])) ?>"><?= e(status_label($project['status'])) ?></span></td>
                    <td><?= e(format_date($project['start_date'])) ?> - <?= e(format_date($project['end_date'])) ?></td>
                    <td><?= e($project['creator_name']) ?></td>
                    <td class="project-actions-cell">
                        <div class="project-actions-grid">
                            <div class="project-action-slot">
                                <a class="btn btn-sm btn-outline-dark w-100" href="<?= e(route_url('du_an/show', ['id' => $project['id']])) ?>">Chi tiết</a>
                            </div>
                            <div class="project-action-slot">
                                <?php if ($canManageThisProject): ?>
                                    <a class="btn btn-sm btn-outline-primary w-100" href="<?= e(route_url('du_an/edit', ['id' => $project['id']])) ?>">Cập nhật</a>
                                <?php else: ?>
                                    <span class="project-action-placeholder"></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($canManageThisProject): ?>
                                <div class="project-action-slot">
                                    <a class="btn btn-sm btn-outline-dark w-100" href="<?= e(route_url('du_an/members', ['id' => $project['id']])) ?>">Thành viên</a>
                                </div>
                                <div class="project-action-slot">
                                    <?php if (project_can_delete($project)): ?>
                                        <form method="post" action="<?= e(route_url('du_an/destroy', ['id' => $project['id']])) ?>" onsubmit="return confirm('Xóa dự án này?');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">Xóa</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="project-action-placeholder"></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$du_an): ?>
                <tr><td colspan="7" class="text-muted">Chua co du an nao.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
