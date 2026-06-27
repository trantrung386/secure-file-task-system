<?php
$taskAccessModel = new \App\Models\Task();
$primaryActionLabel = task_primary_action_label($task, current_user());
$primaryActionUrl = task_primary_action_url($task, current_user());
$canAssignedEmployeeUpdate = current_user()['role'] === 'employee'
    && $taskAccessModel->isUserAssigned((int) $task['id'], (int) current_user()['id'])
    && in_array($task['status'], ['assigned', 'in_progress', 'blocked', 'redo'], true);
$statusOptions = ['in_progress' => 'Đang thực hiện', 'blocked' => 'Bị chặn', 'submitted' => 'Chờ duyệt'];
if (($task['status'] ?? '') === 'blocked') {
    $statusOptions = ['in_progress' => 'Tiếp tục thực hiện', 'submitted' => 'Chờ duyệt'];
}
?>
<div class="section-header">
    <div>
        <div class="section-title"><?= e($task['title']) ?></div>
        <p class="section-desc"><?= e($task['project_name']) ?> • Người tạo: <?= e($task['creator_name']) ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($primaryActionLabel && $primaryActionUrl): ?>
            <a class="btn btn-outline-primary" href="<?= e($primaryActionUrl) ?>"><?= e($primaryActionLabel) ?></a>
        <?php endif; ?>
        <?php if (task_can_delete($task)): ?>
            <form method="post" action="<?= e(route_url('cong_viec/destroy', ['id' => $task['id']])) ?>" onsubmit="return confirm('Bạn có chắc muốn xóa công việc này không?');">
                <button type="submit" class="btn btn-outline-danger">Xóa</button>
            </form>
        <?php endif; ?>
        <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/index')) ?>">Quay lại</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><strong>Dự án:</strong> <?= e($task['project_name']) ?></div>
                    <div class="col-md-6"><strong>Người phụ trách:</strong> <?= e($task['assignee_names'] ?? 'Chưa phân công') ?></div>
                    <div class="col-md-6"><strong>Mức ưu tiên:</strong> <?= e(priority_label($task['priority'])) ?></div>
                    <div class="col-md-6"><strong>Trạng thái:</strong> <span class="badge text-bg-<?= e(status_badge_class($task['status'])) ?>"><?= e(status_label($task['status'])) ?></span></div>
                    <div class="col-md-6"><strong>Hạn chót:</strong> <?= e(format_datetime($task['deadline'])) ?></div>
                    <?php $exp = (int) ($task['expected_score'] ?? 0); $exp = max(0, min(100, $exp)); ?>
                    <div class="col-md-6"><strong>Yêu cầu hoàn thành (KPI):</strong> <?= e((string) $exp) ?>%</div>
                </div>
                <hr>
                <h2 class="h6 fw-bold">Mô tả công việc</h2>
                <p class="mb-0"><?= nl2br(e($task['description'])) ?></p>

                <?php if ($task['review_comment']): ?>
                    <hr>
                    <h2 class="h6 fw-bold">Nhận xét sau duyệt</h2>
                    <p class="mb-1"><?= nl2br(e($task['review_comment'])) ?></p>
                    <div class="small text-secondary">Điểm đánh giá: <?= e((string) $task['review_score']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($canAssignedEmployeeUpdate): ?>
            <div class="card border-0 shadow-sm mb-4" id="employee-adjust-section">
                <div class="card-body">
                    <h2 class="h5 mb-3">Điều chỉnh công việc</h2>
                    <form method="post" action="<?= e(route_url('cong_viec/updateStatus', ['id' => $task['id']])) ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Trạng thái mới</label>
                                <select class="form-select" name="status">
                                    <?php foreach ($statusOptions as $status => $label): ?>
                                        <option value="<?= e($status) ?>"><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Ghi chú tiến độ</label>
                                <input class="form-control" name="note" placeholder="Ví dụ: đã hoàn thành 80% chức năng upload file">
                            </div>
                        </div>
                        <button class="btn btn-primary mt-3">Lưu điều chỉnh</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Upload file kết quả</h2>
                    <form method="post" enctype="multipart/form-data" action="<?= e(route_url('cong_viec/upload', ['id' => $task['id']])) ?>">
                        <input type="file" class="form-control" name="attachment" required>
                        <div class="small text-secondary mt-2">Tải file kết quả để quản lý kiểm tra và duyệt công việc.</div>
                        <button class="btn btn-dark mt-3">Upload và mã hóa file</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if (task_can_review($task)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Duyệt & đánh giá công việc</h2>
                    <form method="post" action="<?= e(route_url('cong_viec/saveReview', ['id' => $task['id']])) ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kết quả duyệt</label>
                                <select class="form-select" name="review_status">
                                    <option value="approved">Duyệt đạt</option>
                                    <option value="redo">Yêu cầu làm lại</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Điểm đánh giá</label>
                                <input type="number" class="form-control" name="review_score" value="<?= e((string) ($task['review_score'] ?? 0)) ?>" min="0" max="20">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nhận xét</label>
                                <textarea class="form-control" name="review_comment" rows="3" placeholder="Nhập nhận xét, mức độ hoàn thành hoặc lý do yêu cầu chỉnh sửa"></textarea>
                            </div>
                        </div>
                        <button class="btn btn-success mt-3">Lưu kết quả duyệt</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header">📎 File đính kèm</div>
            <div class="card-body">
                <?php 
                $userRole = current_user()['role'];
                $isAssignee = $taskAccessModel->isUserAssigned((int) $task['id'], (int) current_user()['id']);
                $canAccessTask = $taskAccessModel->canAccess(current_user(), (int) $task['id']);
                ?>
                
                <?php foreach ($tep_dinh_kem as $file): ?>
                    <?php
                    $canViewManagerFile = ($file['file_type'] === 'manager') && 
                        in_array($userRole, ['manager', 'admin'], true);
                    
                    $canViewProjectManagerFile = ($file['file_type'] === 'project_manager') && $canAccessTask;
                    
                    if ($file['file_type'] === 'employee' || $canViewManagerFile || $canViewProjectManagerFile):
                    ?>
                        <div class="border rounded-4 p-3 mb-3">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">
                                        <?= e($file['original_name']) ?>
                                        <?php if ($file['file_type'] === 'manager'): ?>
                                            <span class="badge text-bg-info ms-2">Hướng dẫn từ Manager</span>
                                        <?php elseif ($file['file_type'] === 'project_manager'): ?>
                                            <span class="badge text-bg-warning ms-2">File từ Manager</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-success ms-2">Nộp từ Employee</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="small text-secondary mb-2 mt-1">
                                        Tải lên bởi <strong><?= e($file['uploader_name']) ?></strong> 
                                        • <?= e(format_datetime($file['created_at'])) ?>
                                    </div>
                                    
                                    <?php if (($file['file_type'] === 'manager' || $file['file_type'] === 'project_manager') && $file['upload_reason']): ?>
                                        <div class="small text-muted">
                                            📝 <em><?= e($file['upload_reason']) ?></em>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a class="btn btn-sm btn-outline-primary mt-2" href="<?= e(route_url('files/download', ['id' => $file['id']])) ?>">
                                ⬇️ Tải file
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <?php if (!$tep_dinh_kem): ?>
                    <div class="text-muted">Chưa có file đính kèm.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header">Lịch sử trạng thái</div>
            <div class="card-body">
                <?php foreach ($logs as $log): ?>
                    <div class="timeline-item">
                        <div class="fw-semibold"><?= e(status_label($log['old_status'])) ?> → <?= e(status_label($log['new_status'])) ?></div>
                        <div class="small text-secondary"><?= e($log['name']) ?> • <?= e(format_datetime($log['created_at'])) ?></div>
                        <?php if ($log['note']): ?><div class="small"><?= e($log['note']) ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$logs): ?>
                    <div class="text-muted">Chưa có lịch sử thay đổi.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
