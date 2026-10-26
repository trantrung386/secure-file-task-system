<div class="section-header">
    <div>
        <div class="section-title">Tải tài liệu mã hóa E2E</div>
        <p class="section-desc"><?= e($attachment['original_name']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if (!empty($attachment['task_id'])): ?>
            <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/show', ['id' => $attachment['task_id']])) ?>">Quay lại công việc</a>
        <?php elseif (!empty($attachment['resolved_project_id']) || !empty($attachment['project_id'])): ?>
            <a class="btn btn-outline-secondary" href="<?= e(route_url('du_an/show', ['id' => $attachment['resolved_project_id'] ?? $attachment['project_id']])) ?>">Quay lại dự án</a>
        <?php endif; ?>
        <?php if (in_array(current_user()['role'] ?? '', ['manager', 'admin'], true)): ?>
            <a class="btn btn-outline-primary" href="<?= e(route_url('files/index')) ?>">Quản lý tài liệu</a>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex flex-column gap-3">
            <div>
                <div class="fw-semibold">Tài liệu được giải mã trực tiếp trên trình duyệt của bạn.</div>
                <div class="small text-secondary">Máy chủ chỉ lưu trữ bản mã (ciphertext) và không nắm giữ khóa bí mật để đọc nội dung file.</div>
            </div>

            <button
                class="btn btn-primary align-self-start"
                data-e2e-download
                data-ciphertext-url="<?= e($ciphertextUrl) ?>"
                data-encrypted-key="<?= e($recipientKey) ?>"
                data-iv="<?= e($attachment['iv']) ?>"
                data-filename="<?= e($attachment['original_name']) ?>"
                data-mime="<?= e($attachment['mime_type'] ?: 'application/octet-stream') ?>"
            >
                Giải mã và tải file
            </button>

            <div class="alert alert-info mb-0" data-e2e-download-status>
                Bấm nút trên để tải bản mã (ciphertext), giải mã bằng khóa bí mật (private key) của tài khoản hiện tại rồi lưu file gốc về máy.
            </div>
        </div>
    </div>
</div>
