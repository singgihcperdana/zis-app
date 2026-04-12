<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'ZIS App') . ' | ' . config_value('app.name', 'ZIS App')); ?></title>
    <link rel="stylesheet" href="/assets/adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="/assets/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="/assets/adminlte/plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="/assets/adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <?php $currentUserName = (string) ($user['username'] ?? $user['name'] ?? 'User'); ?>
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown user-menu show">
                    <a aria-expanded="true" class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                        <span class="user-image img-circle elevation-2" aria-hidden="true"
                              style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;background:transparent;color:currentColor;">
                            <i class="fas fa-user" aria-hidden="true" style="font-size:18px;"></i>
                        </span>
                        <span class="d-none d-md-inline"><?= e($currentUserName); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <li class="user-header bg-primary">
                            <div style="display:flex;align-items:center;justify-content:center;width:80px;height:80px;margin:0 auto 10px;border-radius:50%;background:rgba(255,255,255,0.08);">
                                <i class="fas fa-user fa-2x" aria-hidden="true" style="color:#fff;"></i>
                            </div>
                            <p>
                                <?= e($currentUserName); ?>
                                <span><?= isset($user['title']) ? ' - ' . e((string) $user['title']) : ''; ?></span>
                            </p>
                        </li>
                        <li class="user-footer">
                            <form id="logout-form" method="post" action="/logout" style="display:none;">
                                <input type="hidden" name="_csrf" value="<?= e((string) ($csrfToken ?? '')); ?>">
                            </form>
                            <a class="btn btn-default btn-flat float-right" href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sign out</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a class="brand-link" href="/">
                <img alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
                     src="/assets/adminlte/dist/img/AdminLTELogo.png"
                     style="opacity: .8">
                <span class="brand-text font-weight-light">ZIS App</span>
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a class="nav-link" href="/" data-nav-prefix="/,/dashboard">
                                <i class="nav-icon fas fa-th"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="/public/dashboard" target="_blank" rel="noopener">
                                <i class="nav-icon fas fa-tv"></i>
                                <p>Dashboard Publik</p>
                            </a>
                        </li>

                        <li class="nav-item has-treeview">
                            <a class="nav-link" href="#">
                                <i class="nav-icon fas fa-hand-holding-heart"></i>
                                <p>
                                    Zakat
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a class="nav-link" href="/zakat-payments/new" data-nav-prefix="/zakat-payments/new">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Input Payment</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/zakat-payments/list" data-nav-prefix="/zakat-payments/list,/zakat-payments/" data-nav-exclude="/zakat-payments/new">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Riwayat Payment</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item has-treeview">
                            <a class="nav-link" href="#">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>
                                    Report
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a class="nav-link" href="/reports/rekap" data-nav-prefix="/reports/rekap">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Rekap ZIS</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/reports/muzakki-detail" data-nav-prefix="/reports/muzakki-detail">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Data Muzakki</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <?php if (($user['role'] ?? '') === 'ADMIN'): ?>
                            <li class="nav-item has-treeview">
                                <a class="nav-link" href="#">
                                    <i class="nav-icon fas fa-cog"></i>
                                    <p>
                                        Pengaturan
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a class="nav-link" href="/settings/institution-profile" data-nav-prefix="/settings/institution-profile">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Profile Instansi/Masjid</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/settings/zakat-qualities" data-nav-prefix="/settings/zakat-qualities">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Kelola Zakat Quality</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/settings/users" data-nav-prefix="/settings/users">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Kelola User</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            <?= $content ?? ''; ?>
        </div>
    </div>

    <script src="/assets/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="/assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/adminlte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="/assets/adminlte/plugins/moment/moment.min.js"></script>
    <script src="/assets/adminlte/plugins/moment/locale/id.js"></script>
    <script src="/assets/adminlte/plugins/daterangepicker/daterangepicker.js"></script>
    <script src="/assets/adminlte/dist/js/adminlte.min.js"></script>
    <script>
        (function () {
            const path = window.location.pathname || "/";
            const links = Array.from(document.querySelectorAll(".nav-sidebar a.nav-link[href]"));

            function isMatch(link) {
                const rawPrefixes = link.dataset.navPrefix || link.getAttribute("href") || "";
                const prefixes = rawPrefixes.split(",").map(v => v.trim()).filter(Boolean);
                const rawExcludes = link.dataset.navExclude || "";
                const excludes = rawExcludes.split(",").map(v => v.trim()).filter(Boolean);

                if (excludes.some(prefix => path.startsWith(prefix))) {
                    return false;
                }

                return prefixes.some(prefix => prefix === "/" ? path === "/" : path.startsWith(prefix));
            }

            let activeLink = null;
            let bestScore = -1;

            links.forEach(link => {
                if (!isMatch(link)) {
                    return;
                }

                const rawPrefixes = (link.dataset.navPrefix || link.getAttribute("href") || "")
                    .split(",")
                    .map(v => v.trim())
                    .filter(Boolean);
                const score = Math.max(...rawPrefixes.map(prefix => prefix.length), 0);

                if (score > bestScore) {
                    bestScore = score;
                    activeLink = link;
                }
            });

            if (!activeLink) {
                return;
            }

            activeLink.classList.add("active");
            let parent = activeLink.closest(".nav-item");

            while (parent) {
                const parentLink = parent.querySelector(":scope > .nav-link");
                if (parentLink) {
                    parentLink.classList.add("active");
                }
                if (parent.classList.contains("has-treeview")) {
                    parent.classList.add("menu-open");
                }
                parent = parent.parentElement ? parent.parentElement.closest(".nav-item") : null;
            }
        })();
    </script>
</body>
</html>
