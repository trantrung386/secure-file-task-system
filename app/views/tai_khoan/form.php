<?php $isEdit = !empty($user); ?>
<div class="section-header">
    <div>
        <div class="section-title"><?= $isEdit ? 'Cập nhật tài khoản' : 'Tạo tài khoản mới' ?></div>
        <p class="section-desc">Form dùng để demo use case thêm, sửa, khóa/mở và quản lý phân quyền người dùng.</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(route_url('tai_khoan/index')) ?>">Quay lại</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" action="<?= e($isEdit ? route_url('tai_khoan/update', ['id' => $user['id']]) : route_url('tai_khoan/store')) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Họ tên</label>
                    <input class="form-control" name="name" required value="<?= e($user['name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input class="form-control" type="email" name="email" required value="<?= e($user['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold"><?= $isEdit ? 'Mật khẩu mới (nếu đổi)' : 'Mật khẩu' ?></label>
                    <input class="form-control" type="password" name="password" <?= $isEdit ? '' : 'required' ?>>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phòng ban</label>
                    <input class="form-control" name="department" value="<?= e($user['department'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Vai trò</label>
                    <select class="form-select" name="role">
                        <?php foreach (['admin' => 'Admin', 'manager' => 'Trưởng nhóm', 'employee' => 'Nhân viên'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= (($user['role'] ?? 'employee') === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="active" <?= (($user['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Đang hoạt động</option>
                        <option value="inactive" <?= (($user['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Ngưng hoạt động</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary"><?= $isEdit ? 'Lưu cập nhật' : 'Tạo tài khoản' ?></button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('tai_khoan/index')) ?>">Hủy</a>
            </div>
        </form>
    </div>
</div>
