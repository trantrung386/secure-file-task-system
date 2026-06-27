<div class="section-header">
    <div>
        <div class="section-title">Quản lý tài khoản</div>
        <p class="section-desc">Admin quản lý người dùng, vai trò hệ thống và trạng thái hoạt động.</p>
    </div>
    <a href="<?= e(route_url('tai_khoan/create')) ?>" class="btn btn-primary">+ Tạo tài khoản</a>
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
