<?php
$selectedRecipients = $notification['recipient_ids'] ?? [];
$currentScope = (string) ($notification['scope'] ?? 'project');
?>
<div class="section-header">
    <div>
        <div class="section-title">Cập nhật thông báo</div>
        <p class="section-desc">Chỉnh sửa tiêu đề, nội dung và phạm vi người nhận của thông báo.</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(route_url('thong_bao/index', ['tab' => 'manage'])) ?>">Quay lại</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= e(route_url('thong_bao/update', ['id' => $notification['id']])) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tiêu đề thông báo <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control"
                           name="title"
                           required
                           maxlength="255"
                           value="<?= e((string) ($notification['title'] ?? '')) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Phạm vi gửi <span class="text-danger">*</span></label>
                    <select class="form-select" name="scope" id="editNotifScopeSelect">
                        <option value="project" <?= $currentScope === 'project' ? 'selected' : '' ?>>Theo dự án</option>
                        <option value="user" <?= $currentScope === 'user' ? 'selected' : '' ?>>Chọn người nhận cụ thể</option>
                        <option value="all_projects" <?= $currentScope === 'all_projects' ? 'selected' : '' ?>>
                            <?= ($user['role'] ?? '') === 'admin' ? 'Toàn bộ người dùng hệ thống' : 'Tất cả thành viên các dự án của tôi' ?>
                        </option>
                    </select>
                </div>

                <div class="col-md-3" id="editNotifProjectGroup">
                    <label class="form-label fw-semibold">Dự án áp dụng</label>
                    <select class="form-select" name="project_id">
                        <option value="">-- Chọn dự án --</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?= e((string) $project['id']) ?>"
                                <?= ((int) ($notification['project_id'] ?? 0) === (int) $project['id']) ? 'selected' : '' ?>>
                                <?= e($project['code'] . ' - ' . $project['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-12" id="editNotifRecipientsGroup">
                    <label class="form-label fw-semibold">Người nhận cụ thể</label>
                    <div class="border rounded-3 p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                        <div class="row g-2">
                            <?php foreach ($availableRecipients as $rec): ?>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="recipient_ids[]"
                                               value="<?= e((string) $rec['id']) ?>"
                                               id="editRecCheck<?= (int) $rec['id'] ?>"
                                               <?= in_array((int) $rec['id'], $selectedRecipients, true) ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="editRecCheck<?= (int) $rec['id'] ?>">
                                            <strong><?= e($rec['name']) ?></strong> (<?= e(role_label($rec['role'] ?? 'employee')) ?>)
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Nội dung thông báo <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="content" rows="4" required><?= e((string) ($notification['content'] ?? '')) ?></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Lưu cập nhật</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('thong_bao/index', ['tab' => 'manage'])) ?>">Hủy</a>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const scopeSelect = document.getElementById('editNotifScopeSelect');
    const projectGroup = document.getElementById('editNotifProjectGroup');
    const recipientsGroup = document.getElementById('editNotifRecipientsGroup');
    if (!scopeSelect || !projectGroup || !recipientsGroup) return;

    const syncScopeUI = () => {
        const val = scopeSelect.value;
        projectGroup.classList.toggle('d-none', val !== 'project');
        recipientsGroup.classList.toggle('d-none', val !== 'user');
    };

    scopeSelect.addEventListener('change', syncScopeUI);
    syncScopeUI();
})();
</script>

