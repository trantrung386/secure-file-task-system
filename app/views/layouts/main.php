<?php
$flash = consume_flash();
$authUser = current_user();
$currentRoute = (string) ($_GET['route'] ?? ($authUser ? 'dashboard/index' : 'home/index'));
$isPublicPage = !$authUser && str_starts_with($currentRoute, 'home/');
$isLoginPage = !$authUser && str_starts_with($currentRoute, 'auth/');
$unreadNotifCount = 0;
if ($authUser) {
    try {
        $unreadNotifCount = (new \App\Models\Notification())->unreadCount((int) $authUser['id']);
    } catch (\Throwable $e) {
        $unreadNotifCount = 0;
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= e($title) ?> - <?= e(APP_NAME) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="assets/images/logo-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css?v=20261025v6" rel="stylesheet">
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
                    <span class="brand-public-mark">
                        <img src="assets/images/logo-icon.png" alt="TruTo Work">
                    </span>
                    <span>
                        <strong>TruTo <span class="text-primary fw-semibold">Work</span></strong>
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
                <a class="sidebar-brand text-decoration-none" href="<?= e(route_url('dashboard/index')) ?>">
                    <div class="brand-icon">
                        <img src="assets/images/logo-icon.png" alt="TruTo Work">
                    </div>
                    <div class="brand-title">TruTo <span class="brand-title-accent">Work</span></div>
                </a>
                <button type="button" class="sidebar-close-btn d-lg-none" data-sidebar-close aria-label="Đóng menu">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <nav class="sidebar-menu">
                <a class="menu-link <?= e(active_menu('dashboard/')) ?>" href="<?= e(route_url('dashboard/index')) ?>" data-short-label="TG" title="Tổng quan">
                    <span class="menu-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </span>
                    <span class="menu-text">Tổng quan</span>
                </a>
                <a class="menu-link <?= e(active_menu('cong_viec/')) ?>" href="<?= e(route_url('cong_viec/index')) ?>" data-short-label="CV" title="Quản lý công việc">
                    <span class="menu-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
                    </span>
                    <span class="menu-text">Quản lý công việc</span>
                </a>
                <a class="menu-link <?= e(active_menu('kpi/')) ?>" href="<?= e(route_url('kpi/index')) ?>" data-short-label="KPI" title="Báo cáo KPI">
                    <span class="menu-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><rect width="4" height="7" x="7" y="10" rx="1"/><rect width="4" height="12" x="15" y="5" rx="1"/></svg>
                    </span>
                    <span class="menu-text">Báo cáo KPI</span>
                </a>

                <a class="menu-link <?= e(active_menu('du_an/')) ?>" href="<?= e(route_url('du_an/index')) ?>" data-short-label="DA" title="Quản lý dự án">
                    <span class="menu-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
                    </span>
                    <span class="menu-text">Quản lý dự án</span>
                </a>

                <?php if (in_array($authUser['role'], ['manager', 'admin'], true)): ?>
                    <a class="menu-link <?= e(active_menu('files/') ?: active_menu('tai_lieu/')) ?>" href="<?= e(route_url('files/index')) ?>" data-short-label="TL" title="Quản lý tài liệu">
                        <span class="menu-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                        </span>
                        <span class="menu-text">Quản lý tài liệu</span>
                    </a>
                <?php endif; ?>

                <?php $notifMenuTitle = in_array($authUser['role'], ['manager', 'admin'], true) ? 'Quản lý thông báo' : 'Xem thông báo'; ?>
                <a class="menu-link <?= e(active_menu('thong_bao/')) ?>"
                   href="<?= e(route_url('thong_bao/index')) ?>"
                   data-short-label="TB"
                   title="<?= e($notifMenuTitle) ?>">
                    <span class="menu-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    </span>
                    <span class="menu-text flex-grow-1"><?= e($notifMenuTitle) ?></span>
                    <?php if ($unreadNotifCount > 0): ?>
                        <span class="badge rounded-pill text-bg-danger menu-badge"><?= e((string) $unreadNotifCount) ?></span>
                    <?php endif; ?>
                </a>

                <?php if ($authUser['role'] === 'admin'): ?>
                    <a class="menu-link <?= e(active_menu('tai_khoan/')) ?>" href="<?= e(route_url('tai_khoan/index')) ?>" data-short-label="TK" title="Quản lý tài khoản">
                        <span class="menu-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <span class="menu-text">Quản lý tài khoản</span>
                    </a>
                    <a class="menu-link <?= e(active_menu('nhat_ky/')) ?>" href="<?= e(route_url('nhat_ky/index')) ?>" data-short-label="NK" title="Nhật ký truy cập">
                        <span class="menu-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                        </span>
                        <span class="menu-text">Nhật ký truy cập</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-footer-text">Nền tảng quản lý công việc theo dự án.</div>
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
                    <a class="btn btn-light border position-relative p-0 d-inline-flex align-items-center justify-content-center rounded-circle"
                       style="width: 34px; height: 34px; min-height: 34px;"
                       href="<?= e(route_url('thong_bao/index', ['tab' => 'inbox'])) ?>"
                       title="Thông báo của bạn"
                       aria-label="Thông báo của bạn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="text-secondary" viewBox="0 0 16 16" aria-hidden="true">
                            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
                        </svg>
                        <?php if ($unreadNotifCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger"
                                  style="font-size: 0.62rem; padding: 0.18em 0.4em;">
                                <?= e($unreadNotifCount > 99 ? '99+' : (string) $unreadNotifCount) ?>
                            </span>
                        <?php endif; ?>
                    </a>
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
