<?php

$title = $title ?? 'Login';

ob_start();
?>
<main class="login-shell">
    <section class="login-showcase">
        <div class="showcase-art" aria-hidden="true">
            <div class="showcase-card">
                <div class="crescent"></div>
                <div class="sparkles">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="charity-scene">
                    <div class="coin"></div>
                    <div class="donation-box"></div>
                    <div class="mosque">
                        <div class="minaret"></div>
                        <div class="mosque-dome"></div>
                        <div class="mosque-body"></div>
                    </div>
                    <div class="mosque-base"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="login-panel">
        <div class="login-card">
            <div class="login-brand">
                <div class="login-brand-copy">
                    <small>ZIS App</small>
                    <h2>Masuk ke aplikasi</h2>
                </div>
            </div>

            <?php if (is_string($error) && $error !== ''): ?>
                <div class="alert alert-danger"><?= e($error); ?></div>
            <?php endif; ?>

            <?php if (is_string($success ?? null) && $success !== ''): ?>
                <div class="alert alert-success"><?= e($success); ?></div>
            <?php endif; ?>

            <form method="post" action="/login">
                <input type="hidden" name="_csrf" value="<?= e($csrfToken); ?>">

                <div class="input-with-icon">
                    <label class="sr-only" for="username">Username</label>
                    <i class="fa fa-user"></i>
                    <input
                        id="username"
                        class="form-control"
                        type="text"
                        name="username"
                        value="<?= e((string) $oldLogin); ?>"
                        placeholder="Username atau Email"
                        required
                    >
                </div>

                <div class="input-with-icon">
                    <label class="sr-only" for="password">Password</label>
                    <i class="fa fa-lock"></i>
                    <input
                        id="password"
                        class="form-control"
                        type="password"
                        name="password"
                        placeholder="Password"
                        required
                    >
                </div>

                <button class="btn btn-block btn-login" type="submit">Masuk Sekarang</button>
            </form>

            <a class="login-secondary-link" href="/public/dashboard" target="_blank" rel="noopener">
                <i class="fas fa-tv"></i>
                <span>Lihat Dashboard Publik</span>
            </a>
        </div>
    </section>
</main>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/auth.php');
