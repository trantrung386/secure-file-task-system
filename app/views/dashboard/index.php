<div class="section-header">
    <div>
        <div class="section-title">Tổng quan hệ thống</div>
        <p class="section-desc">Tổng hợp nhanh dự án, công việc và kết quả xử lý.</p>
    </div>
    <span class="badge rounded-pill text-bg-primary px-3 py-2"><?= e(role_label($user['role'])) ?></span>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="metric-box h-100">
            <div class="metric-label">Tổng công việc</div>
            <div class="metric-value"><?= e((string) $stats['total']) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-box h-100">
            <div class="metric-label">Đã duyệt</div>
            <div class="metric-value text-success"><?= e((string) $stats['approved']) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-box h-100">
            <div class="metric-label">Chờ duyệt</div>
            <div class="metric-value text-dark"><?= e((string) $stats['submitted']) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-box h-100">
            <div class="metric-label">Quá hạn</div>
            <div class="metric-value text-danger"><?= e((string) $stats['overdue']) ?></div>
        </div>
    </div>
</div>

<div class="quick-link-grid mb-4">
    <a class="quick-link-card" href="<?= e(route_url('cong_viec/index')) ?>">
        <div class="fw-bold mb-2">Quản lý công việc</div>
        <div class="small text-secondary">Xem danh sách công việc, phân công, cập nhật trạng thái và duyệt hoàn thành.</div>
    </a>
    <?php if (in_array($user['role'], ['admin', 'manager'], true) && $stats['submitted'] > 0): ?>
        <a class="quick-link-card" href="<?= e(route_url('cong_viec/index', ['status' => 'submitted'])) ?>">
            <div class="fw-bold mb-2">Duyệt công việc</div>
            <div class="small text-secondary">Có <?= e((string) $stats['submitted']) ?> công việc chờ duyệt.</div>
        </a>
    <?php endif; ?>
    <a class="quick-link-card" href="<?= e(route_url('kpi/index')) ?>">
        <div class="fw-bold mb-2">Báo cáo KPI</div>
        <div class="small text-secondary">Thống kê hiệu suất đúng hạn, trễ hạn, bị trả lại và điểm đánh giá.</div>
    </a>
    <?php if (in_array($user['role'], ['admin', 'manager'], true)): ?>
        <a class="quick-link-card" href="<?= e(route_url('du_an/index')) ?>">
            <div class="fw-bold mb-2">Quản lý dự án</div>
            <div class="small text-secondary">Tạo dự án, cập nhật dự án và quản lý thành viên theo nhóm.</div>
        </a>
    <?php endif; ?>
    <?php if ($user['role'] === 'admin'): ?>
        <a class="quick-link-card" href="<?= e(route_url('tai_khoan/index')) ?>">
            <div class="fw-bold mb-2">Quản lý tài khoản</div>
            <div class="small text-secondary">Thêm, sửa, khóa hoặc xóa người dùng trong hệ thống.</div>
        </a>
    <?php endif; ?>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Công việc gần đây</span>
                <a class="btn btn-sm btn-outline-primary" href="<?= e(route_url('cong_viec/index')) ?>">Xem tất cả</a>
            </div>
            <div class="card-body table-responsive">
                <?php if (!$tasks): ?>
                    <div class="text-muted">Chưa có công việc nào.</div>
                <?php else: ?>
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Công việc</th>
                                <th>Dự án</th>
                                <th>Trạng thái</th>
                                <th>Hạn chót</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td>
                                    <a class="fw-semibold text-decoration-none" href="<?= e(route_url('cong_viec/show', ['id' => $task['id']])) ?>"><?= e($task['title']) ?></a>
                                    <?php $exp = (int) ($task['expected_score'] ?? 0); $exp = max(0, min(100, $exp)); ?>
                                    <div class="small text-secondary"><?= e(priority_label($task['priority'])) ?> • KPI <?= e((string) $exp) ?>%</div>
                                </td>
                                <td><?= e($task['project_name']) ?></td>
                                <td><span class="badge text-bg-<?= e(status_badge_class($task['status'])) ?>"><?= e(status_label($task['status'])) ?></span></td>
                                <td><?= e(format_date($task['deadline'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header">Dự án tham gia</div>
            <div class="card-body">
                <?php foreach (array_slice($projects, 0, 5) as $project): ?>
                    <div class="border rounded-4 p-3 mb-3">
                        <div class="fw-semibold"><?= e($project['name']) ?></div>
                        <div class="small text-secondary mb-2"><?= e($project['code']) ?></div>
                        <span class="badge text-bg-<?= e(status_badge_class($project['status'])) ?>"><?= e(status_label($project['status'])) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (!$projects): ?>
                    <div class="text-muted">Chưa có dự án nào.</div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($user['role'] === 'admin'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header">Nhật ký gần đây</div>
                <div class="card-body">
                    <div class="small text-secondary mb-3">Tổng tài khoản đang quản lý: <strong><?= e((string) $userCount) ?></strong></div>
                    <?php foreach ($logs as $log): ?>
                        <div class="timeline-item">
                            <div class="fw-semibold"><?= e($log['action']) ?> • <?= e($log['target_type']) ?></div>
                            <div class="small text-secondary"><?= e($log['user_name'] ?? 'Hệ thống') ?> • <?= e(format_datetime($log['created_at'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$logs): ?>
                        <div class="text-muted">Chưa có dữ liệu nhật ký.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($user['role'] === 'employee' && $kpiRows): ?>
            <?php $row = $kpiRows[0]; ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header">KPI cá nhân</div>
                <div class="card-body">
                    <?php $k = max(0, min(100, (int) $row['kpi_score'])); ?>
                    <div class="metric-value text-primary"><?= e((string) $k) ?>%</div>
                    <div class="text-secondary mb-3">Yêu cầu hoàn thành (KPI)</div>
                    <div class="small mb-2">Đúng hạn: <strong><?= e((string) $row['on_time_tasks']) ?></strong></div>
                    <div class="small mb-2">Trễ hạn: <strong><?= e((string) $row['late_tasks']) ?></strong></div>
                    <div class="small">Bị trả lại: <strong><?= e((string) $row['redo_tasks']) ?></strong></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
