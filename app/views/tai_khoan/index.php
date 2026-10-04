<div class="section-header">
    <div>
        <div class="section-title">Quản lý tài khoản</div>
        <p class="section-desc">Admin quản lý người dùng, vai trò hệ thống và trạng thái hoạt động.</p>
    </div>
    <a href="<?= e(route_url('tai_khoan/create')) ?>" class="btn btn-primary">+ Tạo tài khoản</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form class="row g-3" method="get">
            <input type="hidden" name="route" value="tai_khoan/index">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tìm kiếm tài khoản</label>
                <input type="text" class="form-control" name="keyword" placeholder="Nhập họ tên, email hoặc phòng ban..." value="<?= e($keyword ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Lọc theo vai trò</label>
                <select class="form-select" name="role">
                    <option value="">Tất cả vai trò</option>
                    <option value="admin" <?= (($role ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="manager" <?= (($role ?? '') === 'manager') ? 'selected' : '' ?>>Trưởng nhóm</option>
                    <option value="employee" <?= (($role ?? '') === 'employee') ? 'selected' : '' ?>>Nhân viên</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button class="btn btn-outline-primary flex-grow-1" type="submit">Tìm kiếm</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('tai_khoan/index')) ?>">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Phòng ban</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tai_khoan as $item): ?>
                <tr>
                    <td><?= e($item['name']) ?></td>
                    <td><?= e($item['email']) ?></td>
                    <td><?= e(role_label($item['role'])) ?></td>
                    <td><?= e($item['department']) ?></td>
                    <td><span class="badge text-bg-<?= e(status_badge_class($item['status'])) ?>"><?= e(status_label($item['status'])) ?></span></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="<?= e(route_url('tai_khoan/edit', ['id' => $item['id']])) ?>">Sửa</a>
                        <a class="btn btn-sm btn-outline-warning" href="<?= e(route_url('tai_khoan/toggle', ['id' => $item['id']])) ?>">Khóa/Mở</a>
                        <a class="btn btn-sm btn-outline-danger" href="<?= e(route_url('tai_khoan/delete', ['id' => $item['id']])) ?>" onclick="return confirm('Xóa tài khoản này?')">Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$tai_khoan): ?>
                <tr><td colspan="6" class="text-muted">Chưa có tài khoản nào.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
