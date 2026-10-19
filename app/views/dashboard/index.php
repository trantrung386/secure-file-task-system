<?php
$isManagerOrAdmin = in_array($user['role'] ?? '', ['admin', 'manager'], true);
$rates = $stats['rates'] ?? [
    'doing' => 0.0,
    'submitted' => 0.0,
    'approved' => 0.0,
    'overdue' => 0.0,
    'redo' => 0.0,
    'blocked' => 0.0,
    'new' => 0.0,
];
$dashboardHeading = match ($user['role'] ?? 'employee') {
    'admin' => 'Tổng quan hệ thống',
    'manager' => 'Tổng quan quản lý dự án & công việc',
    default => 'Tổng quan công việc của tôi',
};
$dashboardDesc = match ($user['role'] ?? 'employee') {
    'admin' => 'Tổng hợp nhanh toàn bộ dự án, công việc, tài khoản và hoạt động trên hệ thống.',
    'manager' => 'Theo dõi tiến độ các dự án phụ trách, tỷ lệ trạng thái công việc, tài liệu và thông báo nhóm.',
    default => 'Theo dõi các công việc được giao, tiến độ thực hiện, KPI cá nhân và thông báo mới.',
};
?>
<div class="section-header">
    <div>
        <div class="section-title"><?= e($dashboardHeading) ?></div>
        <p class="section-desc"><?= e($dashboardDesc) ?></p>
    </div>
    <span class="badge rounded-pill text-bg-primary px-3 py-2"><?= e(role_label($user['role'])) ?></span>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="metric-box h-100">
            <div class="metric-label">Tổng công việc</div>
            <div class="metric-value"><?= e((string) $stats['total']) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="metric-box h-100">
            <div class="metric-label">Đang thực hiện</div>
            <div class="metric-value text-primary"><?= e((string) ($stats['doing'] ?? 0)) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="metric-box h-100">
            <div class="metric-label">Chờ duyệt</div>
            <div class="metric-value text-dark"><?= e((string) $stats['submitted']) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-xl">
        <div class="metric-box h-100">
            <div class="metric-label">Đã duyệt</div>
            <div class="metric-value text-success"><?= e((string) $stats['approved']) ?></div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl">
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
    <?php if ($isManagerOrAdmin && $stats['submitted'] > 0): ?>
        <a class="quick-link-card" href="<?= e(route_url('cong_viec/index', ['status' => 'submitted'])) ?>">
            <div class="fw-bold mb-2">Duyệt công việc</div>
            <div class="small text-secondary">Có <?= e((string) $stats['submitted']) ?> công việc chờ duyệt.</div>
        </a>
    <?php endif; ?>
    <a class="quick-link-card" href="<?= e(route_url('kpi/index')) ?>">
        <div class="fw-bold mb-2">Báo cáo KPI</div>
        <div class="small text-secondary">Thống kê hiệu suất đúng hạn, trễ hạn, bị trả lại và điểm đánh giá.</div>
    </a>
    <?php if ($isManagerOrAdmin): ?>
        <a class="quick-link-card" href="<?= e(route_url('du_an/index')) ?>">
            <div class="fw-bold mb-2">Quản lý dự án</div>
            <div class="small text-secondary">Tạo dự án, cập nhật dự án và quản lý thành viên theo nhóm.</div>
        </a>
        <a class="quick-link-card" href="<?= e(route_url('files/index')) ?>">
            <div class="fw-bold mb-2">Quản lý tài liệu (<?= e((string) ($documentCount ?? 0)) ?>)</div>
            <div class="small text-secondary">Quản lý tập trung tài liệu mã hóa E2E của các dự án đang tham gia.</div>
        </a>
        <a class="quick-link-card" href="<?= e(route_url('thong_bao/index')) ?>">
            <div class="fw-bold mb-2">
                Quản lý thông báo
                <?php if (($unreadNotificationCount ?? 0) > 0): ?>
                    <span class="badge text-bg-danger ms-1"><?= e((string) $unreadNotificationCount) ?> mới</span>
                <?php endif; ?>
            </div>
            <div class="small text-secondary">Tạo thông báo gửi tới dự án/nhân viên và xem thông báo tiến độ.</div>
        </a>
    <?php else: ?>
        <a class="quick-link-card" href="<?= e(route_url('thong_bao/index')) ?>">
            <div class="fw-bold mb-2">
                Xem thông báo
                <?php if (($unreadNotificationCount ?? 0) > 0): ?>
                    <span class="badge text-bg-danger ms-1"><?= e((string) $unreadNotificationCount) ?> mới</span>
                <?php endif; ?>
            </div>
            <div class="small text-secondary">Theo dõi thông báo công việc được giao, kết quả duyệt và thông báo dự án.</div>
        </a>
    <?php endif; ?>
    <?php if ($user['role'] === 'admin'): ?>
        <a class="quick-link-card" href="<?= e(route_url('tai_khoan/index')) ?>">
            <div class="fw-bold mb-2">Quản lý tài khoản</div>
            <div class="small text-secondary">Thêm, sửa, khóa hoặc xóa người dùng trong hệ thống.</div>
        </a>
    <?php endif; ?>
</div>

<?php if ($isManagerOrAdmin): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Biểu đồ & Thống kê tỷ lệ trạng thái công việc</span>
        <span class="small text-secondary">Tổng số: <strong><?= e((string) $stats['total']) ?></strong> công việc trong phạm vi quản lý</span>
    </div>
    <div class="card-body">
        <?php if ((int) $stats['total'] === 0): ?>
            <div class="text-muted">Chưa có dữ liệu công việc để thống kê tỷ lệ trạng thái.</div>
        <?php else: ?>
            <div class="row g-4 align-items-center">
                <div class="col-md-5">
                    <div style="max-width: 280px; margin: 0 auto;">
                        <canvas id="dashboardTaskStatusChart" height="260"></canvas>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-semibold text-primary">Đang làm (Đã phân công & Đang thực hiện)</span>
                            <span><strong><?= e((string) ($stats['doing'] ?? 0)) ?></strong> việc (<?= e((string) $rates['doing']) ?>%)</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" style="width: <?= e((string) $rates['doing']) ?>%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-semibold text-dark">Chờ duyệt</span>
                            <span><strong><?= e((string) $stats['submitted']) ?></strong> việc (<?= e((string) $rates['submitted']) ?>%)</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-dark" style="width: <?= e((string) $rates['submitted']) ?>%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-semibold text-success">Đã duyệt hoàn thành</span>
                            <span><strong><?= e((string) $stats['approved']) ?></strong> việc (<?= e((string) $rates['approved']) ?>%)</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: <?= e((string) $rates['approved']) ?>%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-semibold text-danger">Quá hạn (Chưa duyệt)</span>
                            <span><strong><?= e((string) $stats['overdue']) ?></strong> việc (<?= e((string) $rates['overdue']) ?>%)</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-danger" style="width: <?= e((string) $rates['overdue']) ?>%"></div>
                        </div>
                    </div>

                    <div class="row g-2 pt-2 border-top small text-secondary">
                        <div class="col-sm-4">Mới tạo: <strong><?= e((string) ($stats['new'] ?? 0)) ?></strong> (<?= e((string) $rates['new']) ?>%)</div>
                        <div class="col-sm-4">Yêu cầu làm lại: <strong class="text-danger"><?= e((string) ($stats['redo'] ?? 0)) ?></strong> (<?= e((string) $rates['redo']) ?>%)</div>
                        <div class="col-sm-4">Bị chặn: <strong class="text-warning"><?= e((string) ($stats['blocked'] ?? 0)) ?></strong> (<?= e((string) $rates['blocked']) ?>%)</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
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

        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    Thông báo mới nhất
                    <?php if (($unreadNotificationCount ?? 0) > 0): ?>
                        <span class="badge text-bg-danger ms-1"><?= e((string) $unreadNotificationCount) ?> chưa đọc</span>
                    <?php endif; ?>
                </span>
                <a class="btn btn-sm btn-outline-primary" href="<?= e(route_url('thong_bao/index', ['tab' => 'inbox'])) ?>">Xem tất cả</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentNotifications)): ?>
                    <div class="text-muted">Chưa có thông báo nào.</div>
                <?php else: ?>
                    <?php foreach ($recentNotifications as $notif): ?>
                        <div class="border rounded-3 p-3 mb-2 <?= empty($notif['is_read']) ? 'bg-light border-primary' : '' ?>">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="fw-semibold">
                                        <?php if (empty($notif['is_read'])): ?>
                                            <span class="badge text-bg-danger me-1">Mới</span>
                                        <?php endif; ?>
                                        <?= e($notif['title']) ?>
                                    </div>
                                    <div class="small text-secondary mt-1"><?= e($notif['content']) ?></div>
                                    <div class="small text-muted mt-1">
                                        <?= e($notif['sender_name'] ?? 'Hệ thống') ?> • <?= e(format_datetime($notif['created_at'])) ?>
                                    </div>
                                </div>
                                <a class="btn btn-sm btn-outline-primary flex-shrink-0"
                                   href="<?= e(route_url('thong_bao/markRead', ['id' => $notif['id'], 'open' => 1])) ?>">
                                    Mở
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Dự án tham gia</span>
                <span class="badge text-bg-secondary"><?= e((string) count($projects)) ?></span>
            </div>
            <div class="card-body">
                <?php foreach (array_slice($projects, 0, 5) as $project): ?>
                    <div class="border rounded-4 p-3 mb-3">
                        <a class="fw-semibold text-decoration-none" href="<?= e(route_url('du_an/show', ['id' => $project['id']])) ?>">
                            <?= e($project['name']) ?>
                        </a>
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
                    <div class="small mb-2">Trễ hạn: <strong><?= e((string) ($row['approved_late_tasks'] ?? $row['late_tasks'] ?? 0)) ?></strong></div>
                    <div class="small">Bị trả lại: <strong><?= e((string) $row['redo_tasks']) ?></strong></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($isManagerOrAdmin && (int) $stats['total'] > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const canvas = document.getElementById('dashboardTaskStatusChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const chartData = [
        <?= (int) ($stats['doing'] ?? 0) ?>,
        <?= (int) ($stats['submitted'] ?? 0) ?>,
        <?= (int) ($stats['approved'] ?? 0) ?>,
        <?= (int) ($stats['overdue'] ?? 0) ?>,
        <?= (int) ($stats['redo'] ?? 0) ?>,
        <?= (int) ($stats['new'] ?? 0) ?>
    ];

    new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Đang làm', 'Chờ duyệt', 'Đã duyệt', 'Quá hạn', 'Làm lại', 'Mới tạo'],
            datasets: [{
                data: chartData,
                backgroundColor: [
                    '#2563eb',
                    '#1e293b',
                    '#16a34a',
                    '#dc2626',
                    '#f59e0b',
                    '#94a3b8'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 12
                    }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>
