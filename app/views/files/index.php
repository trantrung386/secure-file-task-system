<?php
$formatBytes = static function (int $bytes): string {
    if ($bytes <= 0) {
        return '0 KB';
    }
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }
    return max(1, (int) round($bytes / 1024)) . ' KB';
};

$fileTypeBadge = static function (array $doc): array {
    $type = (string) ($doc['file_type'] ?? 'employee');
    if (empty($doc['task_id'])) {
        return ['Tài liệu dự án', 'primary'];
    }
    return match ($type) {
        'project_manager' => ['File khi tạo việc', 'warning'],
        'assignment_manager' => ['File khi phân công', 'info'],
        'manager' => ['Hướng dẫn Manager', 'info'],
        default => ['Nộp từ Nhân viên', 'success'],
    };
};
?>
<div class="section-header">
    <div>
        <div class="section-title">Quản lý tài liệu</div>
        <p class="section-desc">
            <?php if (($user['role'] ?? '') === 'admin'): ?>
                Quản lý tập trung toàn bộ tài liệu mã hóa E2E của tất cả các dự án và công việc trong hệ thống.
            <?php else: ?>
                Quản lý các tài liệu mã hóa E2E thuộc những dự án và công việc mà bạn tham gia quản lý.
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#uploadDocumentCollapse" aria-expanded="false">
            + Tải tài liệu mới
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metric-box h-100">
            <div class="metric-label">Tổng tài liệu</div>
            <div class="metric-value"><?= e((string) ($docStats['total'] ?? 0)) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-box h-100">
            <div class="metric-label">Tài liệu dự án</div>
            <div class="metric-value text-primary"><?= e((string) ($docStats['project_docs'] ?? 0)) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-box h-100">
            <div class="metric-label">Tài liệu công việc</div>
            <div class="metric-value text-success"><?= e((string) ($docStats['task_docs'] ?? 0)) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-box h-100">
            <div class="metric-label">Tổng dung lượng</div>
            <div class="metric-value text-dark"><?= e($formatBytes((int) ($docStats['total_bytes'] ?? 0))) ?></div>
        </div>
    </div>
</div>

<div class="collapse mb-4" id="uploadDocumentCollapse">
    <div class="card border-0 shadow-sm">
        <div class="card-header">Tải lên tài liệu mới (Mã hóa E2E)</div>
        <div class="card-body">
            <?php if (!$projects): ?>
                <div class="alert alert-warning mb-0">Bạn chưa tham gia dự án nào nên chưa thể tải tài liệu lên.</div>
            <?php else: ?>
                <form method="post" enctype="multipart/form-data" action="<?= e(route_url('files/upload')) ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="docUploadProjectSelect">Dự án <span class="text-danger">*</span></label>
                            <select class="form-select" id="docUploadProjectSelect" name="project_id" required>
                                <option value="">-- Chọn dự án --</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?= e((string) $project['id']) ?>">
                                        <?= e($project['code'] . ' - ' . $project['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="docUploadTaskSelect">Gắn vào công việc (tùy chọn)</label>
                            <select class="form-select" id="docUploadTaskSelect" name="task_id">
                                <option value="">-- Tài liệu chung của dự án --</option>
                                <?php foreach ($tasks as $task): ?>
                                    <option value="<?= e((string) $task['id']) ?>" data-project-id="<?= e((string) $task['project_id']) ?>">
                                        <?= e($task['title']) ?> (<?= e($task['project_name']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="docUploadFileInput">Chọn file tài liệu <span class="text-danger">*</span></label>
                            <input type="file"
                                   class="form-control"
                                   id="docUploadFileInput"
                                   name="attachment"
                                   data-e2e-file
                                   data-project-select="#docUploadProjectSelect"
                                   required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="docUploadReasonInput">Ghi chú / Mô tả tài liệu</label>
                            <input type="text"
                                   class="form-control"
                                   id="docUploadReasonInput"
                                   name="upload_reason"
                                   maxlength="255"
                                   placeholder="Ví dụ: Tài liệu đặc tả yêu cầu, biểu mẫu báo cáo, hướng dẫn kỹ thuật...">
                            <div class="small text-secondary mt-1">
                                Hỗ trợ định dạng: pdf, doc, docx, xls, xlsx, png, jpg, jpeg, txt (Tối đa 20MB). File được mã hóa E2E cho các thành viên dự án.
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 d-none" data-e2e-status></div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Mã hóa & Tải lên</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#uploadDocumentCollapse">Đóng</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="index.php" class="row g-3 align-items-end">
            <input type="hidden" name="route" value="files/index">

            <div class="col-md-3">
                <label class="form-label fw-semibold">Dự án</label>
                <select class="form-select" name="project_id">
                    <option value="">Tất cả dự án được phép</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= e((string) $project['id']) ?>" <?= ($filters['project_id'] === (string) $project['id']) ? 'selected' : '' ?>>
                            <?= e($project['code'] . ' - ' . $project['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Công việc</label>
                <select class="form-select" name="task_id">
                    <option value="">Tất cả công việc</option>
                    <?php foreach ($tasks as $task): ?>
                        <option value="<?= e((string) $task['id']) ?>" <?= ($filters['task_id'] === (string) $task['id']) ? 'selected' : '' ?>>
                            <?= e($task['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Phân loại</label>
                <select class="form-select" name="file_type">
                    <option value="">Tất cả loại</option>
                    <option value="project_doc" <?= ($filters['file_type'] === 'project_doc') ? 'selected' : '' ?>>Tài liệu dự án</option>
                    <option value="task_doc" <?= ($filters['file_type'] === 'task_doc') ? 'selected' : '' ?>>Tài liệu công việc</option>
                    <option value="manager" <?= ($filters['file_type'] === 'manager') ? 'selected' : '' ?>>Hướng dẫn từ Manager</option>
                    <option value="employee" <?= ($filters['file_type'] === 'employee') ? 'selected' : '' ?>>Nộp từ Nhân viên</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Từ khóa</label>
                <input type="text" class="form-control" name="keyword" value="<?= e($filters['keyword']) ?>" placeholder="Tên file, mô tả...">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Lọc</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('files/index')) ?>">Xóa lọc</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Danh sách tài liệu</span>
        <span class="badge text-bg-secondary"><?= e((string) count($documents)) ?> tài liệu</span>
    </div>
    <div class="card-body table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Tên tài liệu</th>
                    <th>Dự án / Công việc</th>
                    <th>Phân loại</th>
                    <th>Dung lượng</th>
                    <th>Người tải lên</th>
                    <th>Thời gian</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($documents as $doc): ?>
                <?php [$badgeLabel, $badgeColor] = $fileTypeBadge($doc); ?>
                <tr>
                    <td style="min-width: 240px;">
                        <div class="fw-semibold text-break"><?= e($doc['original_name']) ?></div>
                        <?php if (!empty($doc['upload_reason'])): ?>
                            <div class="small text-muted mt-1">Ghi chú: <?= e($doc['upload_reason']) ?></div>
                        <?php endif; ?>
                        <div class="collapse mt-2" id="editDocReason<?= (int) $doc['id'] ?>">
                            <form method="post" action="<?= e(route_url('files/update', ['id' => $doc['id']])) ?>" class="d-flex gap-2">
                                <input type="text"
                                       class="form-control form-control-sm"
                                       name="upload_reason"
                                       maxlength="255"
                                       value="<?= e((string) ($doc['upload_reason'] ?? '')) ?>"
                                       placeholder="Nhập ghi chú tài liệu...">
                                <button type="submit" class="btn btn-sm btn-primary">Lưu</button>
                            </form>
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($doc['resolved_project_id']) && !empty($doc['project_name'])): ?>
                            <div>
                                <a class="fw-semibold text-decoration-none" href="<?= e(route_url('du_an/show', ['id' => $doc['resolved_project_id']])) ?>">
                                    <?= e($doc['project_name']) ?>
                                </a>
                                <?php if (!empty($doc['project_code'])): ?>
                                    <span class="small text-secondary">(<?= e($doc['project_code']) ?>)</span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small">Chưa gắn dự án</div>
                        <?php endif; ?>

                        <?php if (!empty($doc['task_id']) && !empty($doc['task_title'])): ?>
                            <div class="small mt-1">
                                Công việc:
                                <a class="text-decoration-none" href="<?= e(route_url('cong_viec/show', ['id' => $doc['task_id']])) ?>">
                                    <?= e($doc['task_title']) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge text-bg-<?= e($badgeColor) ?>"><?= e($badgeLabel) ?></span>
                    </td>
                    <td><?= e($formatBytes((int) ($doc['file_size'] ?? 0))) ?></td>
                    <td><?= e($doc['uploader_name'] ?? 'Hệ thống') ?></td>
                    <td><?= e(format_datetime($doc['created_at'])) ?></td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(route_url('files/download', ['id' => $doc['id']])) ?>">
                                Tải file
                            </a>
                            <button class="btn btn-sm btn-outline-secondary"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#editDocReason<?= (int) $doc['id'] ?>">
                                Sửa ghi chú
                            </button>
                            <form method="post"
                                  action="<?= e(route_url('files/destroy', ['id' => $doc['id']])) ?>"
                                  class="d-inline"
                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài liệu này?');">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$documents): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Chưa có tài liệu nào phù hợp với bộ lọc trong phạm vi dự án của bạn.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(() => {
    const projectSelect = document.getElementById('docUploadProjectSelect');
    const taskSelect = document.getElementById('docUploadTaskSelect');
    if (!projectSelect || !taskSelect) return;

    const taskOptions = Array.from(taskSelect.querySelectorAll('option[data-project-id]'));

    const syncTaskOptions = () => {
        const selectedProjectId = projectSelect.value;
        taskOptions.forEach((opt) => {
            const match = !selectedProjectId || opt.dataset.projectId === selectedProjectId;
            opt.hidden = !match;
            opt.disabled = !match;
        });
        const currentOption = taskSelect.selectedOptions[0];
        if (currentOption && currentOption.disabled) {
            taskSelect.value = '';
        }
    };

    projectSelect.addEventListener('change', syncTaskOptions);
    syncTaskOptions();
})();
</script>

