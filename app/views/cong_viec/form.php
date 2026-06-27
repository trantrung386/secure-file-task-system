<div class="section-header">
    <div>
        <div class="section-title">Tạo công việc mới</div>
        <p class="section-desc">Nhập đầy đủ thông tin để gắn công việc vào dự án, làm cơ sở cho bước phân công và tính KPI.</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/index')) ?>">Quay lại</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" enctype="multipart/form-data" action="<?= e(route_url('cong_viec/store')) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Dự án</label>
                    <select class="form-select" name="project_id" required>
                        <option value="">-- Chọn dự án --</option>
                        <?php foreach ($du_an as $project): ?>
                            <option value="<?= e((string) $project['id']) ?>"><?= e($project['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tiêu đề công việc</label>
                    <input class="form-control" name="title" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Mô tả công việc</label>
                    <textarea class="form-control" rows="4" name="description" placeholder="Mô tả phạm vi, đầu ra và ghi chú cần thiết"></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mức ưu tiên</label>
                    <select class="form-select" name="priority">
                        <option value="low">Thấp</option>
                        <option value="medium" selected>Trung bình</option>
                        <option value="high">Cao</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Hạn chót</label>
                    <input type="datetime-local" class="form-control" name="deadline" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Yêu cầu hoàn thành (KPI) (%)</label>
                    <input type="number" class="form-control" name="expected_score" value="100" min="0" max="100" step="1">
                </div>

                <!-- Upload file tài liệu công việc (Manager) -->
                <div class="col-12">
                    <hr>
                    <h5 class="mb-3">📤 Upload file tài liệu công việc (Tuỳ chọn)</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Chọn file</label>
                            <input type="file" class="form-control" id="task_attachment" name="task_attachment">
                            <div id="task_error" class="alert alert-danger alert-sm mt-2" style="display: none;"></div>
                            <div class="small text-secondary mt-2">
                                File sẽ được mã hóa. Hỗ trợ: pdf, doc, docx, xls, xlsx, png, jpg, jpeg, txt. Max 20MB.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lý do/Mô tả file</label>
                            <input type="text" class="form-control" name="upload_reason" placeholder="VD: Tài liệu yêu cầu, hướng dẫn chi tiết, file mẫu, ...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" id="task_submit_btn">Lưu công việc</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/index')) ?>">Hủy</a>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'txt'];
    const maxBytes = 20 * 1024 * 1024; // 20MB
    const fileInput = document.getElementById('task_attachment');
    const errorDiv = document.getElementById('task_error');
    const submitBtn = document.getElementById('task_submit_btn');

    if (!fileInput) return;

    fileInput.addEventListener('change', function() {
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';

        if (!this.files || this.files.length === 0) {
            return; // Không chọn file là được phép
        }

        const file = this.files[0];
        const errors = [];

        // Kiểm tra kích thước file
        if (file.size > maxBytes) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            errors.push(`❌ File vượt quá dung lượng cho phép (${sizeMB}MB > 20MB)`);
        }

        // Kiểm tra định dạng file
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowedExt.includes(ext)) {
            errors.push(`❌ Định dạng file không được hỗ trợ (.${ext}). Chỉ hỗ trợ: pdf, doc, docx, xls, xlsx, png, jpg, jpeg, txt`);
        }

        if (errors.length > 0) {
            errorDiv.innerHTML = errors.join('<br>');
            errorDiv.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            submitBtn.disabled = false;
        }
    });
})();
</script>
