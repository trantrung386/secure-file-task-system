<div class="auth-compact-shell">
    <section class="auth-split-card">
        <div class="auth-visual-panel">
            <div class="auth-visual-top">
                <span class="public-kicker dark">TruTo Work</span>
                <h2>Quản lý dự án và công việc trên một giao diện gọn gàng.</h2>
                <p>Thiết kế sạch, dễ nhìn và phù hợp cho doanh nghiệp sử dụng hằng ngày.</p>
            </div>

            <div class="auth-visual-board">
                <img src="assets/images/task-landing.svg" alt="Minh họa giao việc">
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="auth-form-head">
                <a class="back-home-link" href="<?= e(route_url('home/index')) ?>">← Quay lại trang chủ</a>
                <h1>Đăng nhập</h1>
                <p>Đăng nhập để quản lý dự án, công việc và theo dõi tiến độ xử lý.</p>
            </div>

            <form method="post" action="<?= e(route_url('auth/login')) ?>" class="auth-form-main">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email đăng nhập</label>
                    <input type="email" name="email" class="form-control form-control-lg" required value="<?= e(old('email', 'admin@taskflow.local')) ?>" placeholder="Nhập email đăng nhập">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mật khẩu</label>
                    <input type="password" name="password" class="form-control form-control-lg" required value="admin123" placeholder="Nhập mật khẩu">
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Đăng nhập vào hệ thống</button>
            </form>
        </div>
    </section>
</div>

<script>
    document.querySelectorAll('.demo-account-item').forEach(function (item) {
        item.addEventListener('click', function () {
            const emailInput = document.querySelector('input[name="email"]');
            const passwordInput = document.querySelector('input[name="password"]');
            if (emailInput && passwordInput) {
                emailInput.value = this.dataset.email || '';
                passwordInput.value = this.dataset.password || '';
                emailInput.focus();
            }
        });
    });
</script>
