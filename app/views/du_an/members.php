<div class="section-header">
    <div>
        <div class="section-title">Thành viên dự án</div>
        <p class="section-desc"><?= e($project['name']) ?> • Menu con của use case Quản lý dự án.</p>
    </div>
    <a href="<?= e(route_url('du_an/index')) ?>" class="btn btn-outline-secondary">Quay lại</a>
</div>

<div class="row g-4">
    <div class="col-lg-5">
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
                            <th class="text-end">Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?= e($member['name']) ?><div class="small text-secondary"><?= e($member['email']) ?></div></td>
                            <td><?= e(match ($member['project_role']) { 'lead' => 'Trưởng nhóm', 'reviewer' => 'Người duyệt', default => 'Thành viên' }) ?></td>
                            <td><?= e(role_label($member['role'])) ?></td>
                            <td><span class="badge text-bg-<?= e(status_badge_class($member['status'])) ?>"><?= e(status_label($member['status'])) ?></span></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-danger" href="<?= e(route_url('du_an/removeMember', ['id' => $project['id'], 'user_id' => $member['user_id']])) ?>" onclick="return confirm('Xóa thành viên này khỏi dự án?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$members): ?>
                        <tr><td colspan="5" class="text-muted">Chưa có thành viên.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
