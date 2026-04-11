<?php

$title = $title ?? 'Login';

ob_start();
?>
<main class="page" style="display:grid;place-items:center;min-height:100vh;">
    <section class="card" style="width:min(100%, 480px);padding:32px;">
        <div style="margin-bottom:24px;">
            <p class="muted" style="margin:0 0 8px;">ZIS App Native PHP</p>
            <h1 style="margin:0;font-size:32px;">Login</h1>
            <p class="muted" style="margin:10px 0 0;">
                Login menggunakan data user dari database MySQL.
            </p>
        </div>

        <?php if (is_string($error) && $error !== ''): ?>
            <div class="alert"><?= e($error); ?></div>
        <?php endif; ?>

        <form method="post" action="/login">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken); ?>">

            <div class="field">
                <label for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="<?= e((string) $oldEmail); ?>"
                    placeholder="admin@example.com"
                    required
                >
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >
            </div>

            <button class="button" type="submit" style="width:100%;">Masuk</button>
        </form>

        <div
            style="margin-top:20px;padding:16px;border-radius:16px;background:#f7f1e3;border:1px solid #eadfca;"
        >
            <strong style="display:block;margin-bottom:6px;">Mode login database</strong>
            <div class="muted">Gunakan email dan password dari tabel <code>users</code>.</div>
        </div>
    </section>
</main>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/main.php');
