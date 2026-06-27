<?php
$flash = consume_flash();
$authUser = current_user();
$currentRoute = (string) ($_GET['route'] ?? ($authUser ? 'dashboard/index' : 'home/index'));
$isPublicPage = !$authUser && str_starts_with($currentRoute, 'home/');
$isLoginPage = !$authUser && str_starts_with($currentRoute, 'auth/');
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= e($title) ?> - <?= e(APP_NAME) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css?v=20260404a" rel="stylesheet">
</head>
<body>
<?php if ($isPublicPage): ?>
    <div class="public-site-shell">
        <header class="public-header">
            <div class="public-container public-header-inner">
                <a class="brand-public" href="<?= e(route_url('home/index')) ?>">
                    <span class="brand-public-mark">TTW</span>
                    <span>
                        <strong>TruTo Work</strong>
                        <small>Quản lý công việc theo dự án</small>
                    </span>
                </a>

                <nav class="public-nav">
                    <a class="<?= $currentRoute === 'home/index' ? 'is-active' : '' ?>" href="<?= e(route_url('home/index')) ?>">Trang chủ</a>
                    <a class="<?= $currentRoute === 'home/about' ? 'is-active' : '' ?>" href="<?= e(route_url('home/about')) ?>">Giới thiệu</a>
                    <a href="<?= e(route_url('auth/login')) ?>">Đăng nhập</a>
                </nav>
            </div>
        </header>

        <main class="public-main">
            <div class="public-container">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm border-0 mt-3">
                        <?= e($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </main>

        <footer class="public-footer">
            <div class="public-container public-footer-inner">
                <span>TruTo Work</span>
            </div>
        </footer>
    </div>
<?php elseif ($isLoginPage): ?>
    <main class="auth-shell">
        <div class="container py-4 py-lg-5">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm border-0 mb-4 mx-auto auth-flash">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </main>
<?php else: ?>
    <div class="app-shell">
        <aside class="sidebar shadow-sm">
            <div class="sidebar-brand">
                <div class="brand-icon">TTW</div>
                <div class="brand-title">TruTo Work</div>
            </div>

            <nav class="sidebar-menu">
                <a class="menu-link <?= e(active_menu('dashboard/')) ?>" href="<?= e(route_url('dashboard/index')) ?>">
                    <span>Tổng quan</span>
                </a>
                <a class="menu-link <?= e(active_menu('cong_viec/')) ?>" href="<?= e(route_url('cong_viec/index')) ?>">
                    <span>Quản lý công việc</span>
                </a>
                <a class="menu-link <?= e(active_menu('kpi/')) ?>" href="<?= e(route_url('kpi/index')) ?>">
                    <span>Báo cáo KPI</span>
                </a>

                <?php if (in_array($authUser['role'], ['admin', 'manager'], true)): ?>
                    <a class="menu-link <?= e(active_menu('du_an/')) ?>" href="<?= e(route_url('du_an/index')) ?>">
                        <span>Quản lý dự án</span>
                    </a>
                <?php endif; ?>

                <?php if ($authUser['role'] === 'admin'): ?>
                    <a class="menu-link <?= e(active_menu('tai_khoan/')) ?>" href="<?= e(route_url('tai_khoan/index')) ?>">
                        <span>Quản lý tài khoản</span>
                    </a>
                    <a class="menu-link <?= e(active_menu('nhat_ky/')) ?>" href="<?= e(route_url('nhat_ky/index')) ?>">
                        <span>Nhật ký truy cập</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="small text-secondary">Nền tảng quản lý công việc theo dự án.</div>
            </div>
        </aside>

        <div class="main-shell">
            <header class="topbar shadow-sm">
                <div class="page-info">
                    <div class="page-label">TruTo Work</div>
                </div>
                <div class="topbar-user">
                    <div class="avatar-badge"><?= e(strtoupper(substr($authUser['name'], 0, 1))) ?></div>
                    <div>
                        <div class="fw-semibold"><?= e($authUser['name']) ?></div>
                        <div class="small text-secondary"><?= e(role_label($authUser['role'])) ?></div>
                    </div>
                    <a class="btn btn-outline-secondary btn-sm" href="<?= e(route_url('auth/logout')) ?>">Đăng xuất</a>
                </div>
            </header>

            <main class="content-shell">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm border-0">
                        <?= e($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </main>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
