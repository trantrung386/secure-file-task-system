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
<?php if ($flash): ?>
    <div class="app-flash-container">
        <div class="alert app-flash-alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm border-0 mb-0" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>
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
            <?= $content ?>
        </div>
    </main>
<?php else: ?>
    <div class="app-shell">
        <div class="sidebar-backdrop" data-sidebar-close></div>
        <aside class="sidebar shadow-sm">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <div class="brand-icon">TTW</div>
                    <div class="brand-title">TruTo Work</div>
                </div>
                <button type="button" class="sidebar-close-btn d-lg-none" data-sidebar-close aria-label="Đóng menu">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <nav class="sidebar-menu">
                <a class="menu-link <?= e(active_menu('dashboard/')) ?>" href="<?= e(route_url('dashboard/index')) ?>" data-short-label="TG" title="Tổng quan">
                    <span>Tổng quan</span>
                </a>
                <a class="menu-link <?= e(active_menu('cong_viec/')) ?>" href="<?= e(route_url('cong_viec/index')) ?>" data-short-label="CV" title="Quản lý công việc">
                    <span>Quản lý công việc</span>
                </a>
                <a class="menu-link <?= e(active_menu('kpi/')) ?>" href="<?= e(route_url('kpi/index')) ?>" data-short-label="KPI" title="Báo cáo KPI">
                    <span>Báo cáo KPI</span>
                </a>

                <a class="menu-link <?= e(active_menu('du_an/')) ?>" href="<?= e(route_url('du_an/index')) ?>" data-short-label="DA" title="Quản lý dự án">
                    <span>Quản lý dự án</span>
                </a>

                <?php if ($authUser['role'] === 'admin'): ?>
                    <a class="menu-link <?= e(active_menu('tai_khoan/')) ?>" href="<?= e(route_url('tai_khoan/index')) ?>" data-short-label="TK" title="Quản lý tài khoản">
                        <span>Quản lý tài khoản</span>
                    </a>
                    <a class="menu-link <?= e(active_menu('nhat_ky/')) ?>" href="<?= e(route_url('nhat_ky/index')) ?>" data-short-label="NK" title="Nhật ký truy cập">
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
                <button class="sidebar-toggle" type="button" aria-label="Thu gọn hoặc mở menu" aria-expanded="true" data-sidebar-toggle>
                    <span></span><span></span><span></span>
                </button>
                <div class="page-info">
                    <div class="page-label">TruTo Work</div>
                </div>
                <div class="topbar-user">
                    <div class="avatar-badge"><?= e(strtoupper(substr($authUser['name'], 0, 1))) ?></div>
                    <div class="d-none d-sm-block">
                        <div class="fw-semibold text-truncate" style="max-width: 140px;"><?= e($authUser['name']) ?></div>
                        <div class="small text-secondary"><?= e(role_label($authUser['role'])) ?></div>
                    </div>
                    <a class="btn btn-outline-secondary btn-sm" href="<?= e(route_url('auth/logout')) ?>" title="Đăng xuất">
                        <span class="d-none d-md-inline">Đăng xuất</span>
                        <span class="d-md-none">Thoát</span>
                    </a>
                </div>
            </header>

            <main class="content-shell">
                <?= $content ?>
            </main>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.setTimeout(() => {
        document.querySelectorAll('.app-flash-alert').forEach((alertElement) => {
            bootstrap.Alert.getOrCreateInstance(alertElement).close();
        });
    }, 3000);

    (() => {
        const appShell = document.querySelector('.app-shell');
        const toggle = document.querySelector('[data-sidebar-toggle]');
        const closeBtns = document.querySelectorAll('[data-sidebar-close]');
        if (!appShell) return;

        const syncToggle = () => {
            if (!toggle) return;
            const expanded = !appShell.classList.contains('sidebar-collapsed') && !appShell.classList.contains('sidebar-open');
            toggle.setAttribute('aria-expanded', String(expanded));
        };

        if (toggle) {
            toggle.addEventListener('click', () => {
                if (window.matchMedia('(max-width: 991.98px)').matches) {
                    appShell.classList.toggle('sidebar-open');
                } else {
                    appShell.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('ttw:sidebar-collapsed', appShell.classList.contains('sidebar-collapsed') ? '1' : '0');
                }
                syncToggle();
            });
        }

        closeBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                appShell.classList.remove('sidebar-open');
                syncToggle();
            });
        });

        document.querySelectorAll('.sidebar-menu .menu-link').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.matchMedia('(max-width: 991.98px)').matches) {
                    appShell.classList.remove('sidebar-open');
                    syncToggle();
                }
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && appShell.classList.contains('sidebar-open')) {
                appShell.classList.remove('sidebar-open');
                syncToggle();
            }
        });

        if (localStorage.getItem('ttw:sidebar-collapsed') === '1' && !window.matchMedia('(max-width: 991.98px)').matches) {
            appShell.classList.add('sidebar-collapsed');
        }
        window.addEventListener('resize', () => {
            if (!window.matchMedia('(max-width: 991.98px)').matches) {
                appShell.classList.remove('sidebar-open');
            }
            syncToggle();
        });
        syncToggle();
    })();
</script>
<?php if ($authUser): ?>
    <script>
        window.E2E_APP = {
            userId: <?= (int) $authUser['id'] ?>,
            savePublicKeyUrl: '<?= e(route_url('crypto/savePublicKey')) ?>',
            currentRecipientUrl: '<?= e(route_url('crypto/currentRecipient')) ?>',
            projectRecipientsUrl: '<?= e(route_url('crypto/projectRecipients')) ?>'
        };
    </script>
    <script src="assets/js/e2e-crypto.js?v=20260702e2e"></script>
<?php endif; ?>
</body>
</html>

