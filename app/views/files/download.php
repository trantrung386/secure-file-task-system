<div class="section-header">
    <div>
        <div class="section-title">Tai file E2E</div>
        <p class="section-desc"><?= e($attachment['original_name']) ?></p>
    </div>
    <?php if (!empty($attachment['task_id'])): ?>
        <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/show', ['id' => $attachment['task_id']])) ?>">Quay lai</a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex flex-column gap-3">
            <div>
                <div class="fw-semibold">File duoc giai ma truc tiep tren trinh duyet cua ban.</div>
                <div class="small text-secondary">Server chi gui ciphertext va khong co khoa rieng de doc noi dung file.</div>
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
                Giai ma va tai file
            </button>

            <div class="alert alert-info mb-0" data-e2e-download-status>
                Bam nut de tai ciphertext, giai ma bang private key cua tai khoan hien tai, roi luu file goc.
            </div>
        </div>
    </div>
</div>
