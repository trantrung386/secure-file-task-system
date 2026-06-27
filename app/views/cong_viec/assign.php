<div class="section-header">
    <div>
        <div class="section-title">Phân công công việc</div>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/show', ['id' => $task['id'] ?? ''])) ?>">Quay lại</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <style>
        /* Scoped tweaks for a cleaner form layout */
        .assign-form .form-label { font-weight:600; }
        .assign-form .card-sm { border-radius:10px; }
        .assign-form .file-card { background:#fafbfc; }
        .assign-form .mt-2.text-truncate { max-width:100%; }
        </style>
        <form method="post" action="<?= e(route_url('cong_viec/saveAssignment', ['id' => $task['id'] ?? ''])) ?>" enctype="multipart/form-data" class="assign-form">

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Tiêu đề công việc</label>
                    <input type="text" class="form-control" name="assignment_title" placeholder="Nhập tiêu đề công việc" value="<?= e($task['title'] ?? '') ?>">
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-12 col-lg-7">
                    <label class="form-label fw-semibold">Thành viên phụ trách</label>

                    <?php
                    // Ensure variables exist to avoid undefined variable notices in the view
                    $members = isset($members) && is_array($members) ? $members : [];
                    $selectedAssigneeIds = isset($selectedAssigneeIds) && is_array($selectedAssigneeIds) ? $selectedAssigneeIds : [];

                    $selectedNames = [];
                    foreach ($members as $member) {
                        if (isset($member['user_id']) && in_array((int) $member['user_id'], $selectedAssigneeIds, true)) {
                            $selectedNames[] = $member['name'] ?? '';
                        }
                    }
                    ?>

                    <div class="assignee-dropdown" data-assignee-dropdown>
                        <button type="button" class="assignee-toggle" data-assignee-toggle aria-expanded="false">
                            <span class="assignee-toggle-label" data-assignee-label>
                                <?= !empty($selectedNames) ? e(implode(', ', $selectedNames)) : 'Chọn thành viên phụ trách' ?>
                            </span>
                            <span class="assignee-toggle-icon">▾</span>
                        </button>

                        <div class="assignee-menu" data-assignee-menu hidden>
                            <div class="assignee-menu-head">Danh sách thành viên</div>
                            <div class="assignee-option-list">
                                <?php foreach ($members as $member): ?>
                                    <?php $checked = in_array((int) $member['user_id'], $selectedAssigneeIds ?? [], true); ?>
                                    <label class="assignee-option">
                                        <input type="checkbox" name="assignee_ids[]" value="<?= e((string) $member['user_id']) ?>" <?= $checked ? 'checked' : '' ?>>
                                        <span class="assignee-option-text">
                                            <strong><?= e($member['name']) ?></strong>
                                            <small><?= e(role_label($member['role'])) ?></small>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="assignee-selected" data-assignee-selected>
                        <?php foreach ($members as $member): ?>
                            <?php $checked = in_array((int) $member['user_id'], $selectedAssigneeIds ?? [], true); ?>
                            <span class="assignee-pill <?= $checked ? '' : 'd-none' ?>" data-assignee-pill="<?= e((string) $member['user_id']) ?>">
                                <?= e($member['name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-2">
                    <label class="form-label fw-semibold">Ngày giao việc</label>
                    <input type="datetime-local" class="form-control" name="start_date" value="">
                </div>

                <div class="col-sm-6 col-lg-3">
                    <label class="form-label fw-semibold">Hạn chót thực hiện</label>
                    <input type="datetime-local" class="form-control" name="deadline" value="">
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-12 col-lg-8">
                    <label class="form-label fw-semibold">Mô tả chi tiết & yêu cầu cụ thể</label>
                    <textarea class="form-control" name="detailed_description" rows="3" placeholder="Mô tả chi tiết công việc, các bước thực hiện, yêu cầu kỹ thuật, tiêu chí nghiệm thu"></textarea>
                </div>

                <div class="col-12 col-lg-4">
                    <label class="form-label fw-semibold">Mức độ ưu tiên</label>
                    <select class="form-select mb-2" name="priority">
                        <option value="">-- Chọn mức độ --</option>
                        <option value="low">Thấp</option>
                        <option value="medium">Trung bình</option>
                        <option value="high">Cao</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-12">
                    <hr>
                    <h5 class="mb-3">📤 Upload file tài liệu dự án (Tuỳ chọn)</h5>
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold">Chọn file</label>
                            <input type="file" class="form-control" name="attachments[]" id="attachments" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.txt">
                            <div id="assignment_error" class="alert alert-danger alert-sm mt-2" style="display:none;"></div>
                            <div id="selectedFiles" class="mt-2 text-truncate text-muted"></div>
                            <div class="small text-secondary mt-2">File sẽ được mã hóa. Hỗ trợ: pdf, doc, docx, xls, xlsx, png, jpg, jpeg, txt. Max 20MB mỗi file.</div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold">Lý do/Mô tả file</label>
                            <input type="text" class="form-control" name="attachment_note" id="attachment_note" placeholder="VD: Tài liệu yêu cầu, hướng dẫn chi tiết, file mẫu, ...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2 flex-wrap">
                <button class="btn btn-primary" id="assign_submit_btn">Lưu phân công</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/show', ['id' => $task['id'] ?? ''])) ?>">Hủy</a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const dropdown = document.querySelector('[data-assignee-dropdown]');
    if (!dropdown) return;

    const toggle = dropdown.querySelector('[data-assignee-toggle]');
    const menu = dropdown.querySelector('[data-assignee-menu]');
    const label = dropdown.querySelector('[data-assignee-label]');
    const selectedWrap = document.querySelector('[data-assignee-selected]');
    const inputs = dropdown.querySelectorAll('input[type="checkbox"]');

    const updateSelected = () => {
        const checked = Array.from(inputs).filter(input => input.checked);
        const names = checked.map(input => {
            const text = input.closest('.assignee-option').querySelector('strong');
            return text ? text.textContent.trim() : '';
        }).filter(Boolean);

        label.textContent = names.length ? names.join(', ') : 'Chọn thành viên phụ trách';

        if (selectedWrap) {
            selectedWrap.querySelectorAll('[data-assignee-pill]').forEach(pill => pill.classList.add('d-none'));
            checked.forEach(input => {
                const pill = selectedWrap.querySelector(`[data-assignee-pill="${input.value}"]`);
                if (pill) pill.classList.remove('d-none');
            });
        }
    };

    toggle.addEventListener('click', function () {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        menu.hidden = isOpen;
        dropdown.classList.toggle('is-open', !isOpen);
    });

    document.addEventListener('click', function (event) {
        if (!dropdown.contains(event.target)) {
            toggle.setAttribute('aria-expanded', 'false');
            menu.hidden = true;
            dropdown.classList.remove('is-open');
        }
    });

    inputs.forEach(input => input.addEventListener('change', updateSelected));
    updateSelected();
})();
</script>

<script>
// Show selected file names and validate uploads (size + extension)
(function () {
    const attachments = document.getElementById('attachments');
    const selectedFiles = document.getElementById('selectedFiles');
    const errorDiv = document.getElementById('assignment_error');
    const submitBtn = document.getElementById('assign_submit_btn');
    if (!attachments) return;

    const allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'txt'];
    const maxBytes = 20 * 1024 * 1024; // 20MB

    const render = () => {
        const files = Array.from(attachments.files || []);
        if (!files.length) {
            if (selectedFiles) selectedFiles.textContent = '';
            if (errorDiv) errorDiv.style.display = 'none';
            if (submitBtn) submitBtn.disabled = false;
            return;
        }

        // show names
        if (selectedFiles) selectedFiles.textContent = files.map(f => f.name).join(', ');

        // validate
        const errors = [];
        files.forEach(file => {
            if (file.size > maxBytes) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                errors.push(`❌ ${file.name} vượt quá ${sizeMB}MB (hạn 20MB)`);
            }
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            if (!allowedExt.includes(ext)) {
                errors.push(`❌ ${file.name} định dạng .${ext} không được hỗ trợ`);
            }
        });

        if (errors.length) {
            if (errorDiv) {
                errorDiv.innerHTML = errors.join('<br>');
                errorDiv.style.display = 'block';
            }
            if (submitBtn) submitBtn.disabled = true;
        } else {
            if (errorDiv) errorDiv.style.display = 'none';
            if (submitBtn) submitBtn.disabled = false;
        }
    };

    attachments.addEventListener('change', render);
    render();
})();
</script>
