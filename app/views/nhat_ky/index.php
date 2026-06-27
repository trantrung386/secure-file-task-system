<div class="section-header">
    <div>
        <div class="section-title">Nhật ký truy cập</div>
        <p class="section-desc">Admin theo dõi các thao tác đăng nhập, tạo dữ liệu, phân công, upload và tải file.</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Người dùng</th>
                    <th>Hành động</th>
                    <th>Đối tượng</th>
                    <th>Mô tả</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e(format_datetime($log['created_at'])) ?></td>
                    <td><?= e($log['user_name'] ?? 'Hệ thống') ?></td>
                    <td><span class="badge text-bg-secondary"><?= e($log['action']) ?></span></td>
                    <td><?= e($log['target_type']) ?><?= $log['target_id'] ? ' #' . e((string) $log['target_id']) : '' ?></td>
                    <td><?= e($log['description']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$logs): ?>
                <tr><td colspan="5" class="text-muted">Chưa có dữ liệu nhật ký.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
