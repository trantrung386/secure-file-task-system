<div class="section-header">
    <div>
        <div class="section-title">Thành viên dự án</div>
        <p class="section-desc"><?= e($project['name']) ?></p>
    </div>
    <a href="<?= e(route_url('du_an/index')) ?>" class="btn btn-outline-secondary">Quay lại</a>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <?php if ($canManageProject): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header">Thêm thành viên vào dự án</div>
                <div class="card-body">
                    <form method="post" action="<?= e(route_url('du_an/addMember', ['id' => $project['id']])) ?>">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Người dùng</label>
                            <select class="form-select" name="user_id" required>
                                <option value="">-- Chọn thành viên --</option>
                                <?php foreach ($availableUsers as $user): ?>
                                    <option value="<?= e((string) $user['id']) ?>"><?= e($user['name']) ?> (<?= e($user['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Vai trò trong dự án</label>
                            <select class="form-select" name="project_role">
                                <option value="member">Thành viên</option>
                                <option value="lead">Trưởng nhóm</option>
                                <option value="reviewer">Người duyệt</option>
                            </select>
                        </div>
                        <button class="btn btn-primary">Thêm thành viên</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header">File dự án</div>
            <div class="card-body">
                <?php foreach (($projectFiles ?? []) as $file): ?>
                    <div class="border rounded-4 p-3 mb-3">
                        <div class="fw-semibold"><?= e($file['original_name']) ?></div>
                        <div class="small text-secondary">
                            Tải lên bởi <strong><?= e($file['uploader_name']) ?></strong>
                            - <?= e(format_datetime($file['created_at'])) ?>
                        </div>
                        <?php if (!empty($file['upload_reason'])): ?>
                            <div class="small text-muted mt-1"><em><?= e($file['upload_reason']) ?></em></div>
                        <?php endif; ?>
                        <a class="btn btn-sm btn-outline-primary mt-2" href="<?= e(route_url('files/download', ['id' => $file['id']])) ?>">Tải file</a>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($projectFiles)): ?>
                    <div class="text-muted">Chưa có file dự án.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header">Danh sách thành viên hiện tại</div>
            <div class="card-body table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Họ tên</th>
                            <th>Vai trò dự án</th>
                            <th>Vai trò hệ thống</th>
                            <th>Trạng thái</th>
                            <?php if ($canManageProject): ?><th class="text-end">Xóa</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?= e($member['name']) ?><div class="small text-secondary"><?= e($member['email']) ?></div></td>
                            <td><?= e(match ($member['project_role']) { 'lead' => 'Trưởng nhóm', 'reviewer' => 'Người duyệt', default => 'Thành viên' }) ?></td>
                            <td><?= e(role_label($member['role'])) ?></td>
                            <td><span class="badge text-bg-<?= e(status_badge_class($member['status'])) ?>"><?= e(status_label($member['status'])) ?></span></td>
                            <?php if ($canManageProject): ?>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-danger" href="<?= e(route_url('du_an/removeMember', ['id' => $project['id'], 'user_id' => $member['user_id']])) ?>" onclick="return confirm('Xóa thành viên này khỏi dự án?')">Xóa</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$members): ?>
                        <tr><td colspan="<?= $canManageProject ? '5' : '4' ?>" class="text-muted">Chưa có thành viên.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
