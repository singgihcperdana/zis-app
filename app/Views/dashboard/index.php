<?php

$title = $title ?? 'Dashboard';

ob_start();
?>
<main class="page">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px;">
        <div>
            <p class="muted" style="margin:0 0 8px;">Login berhasil</p>
            <h1 style="margin:0;font-size:34px;">Dashboard</h1>
        </div>

        <form method="post" action="/logout" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken); ?>">
            <button class="button button-secondary" type="submit">Logout</button>
        </form>
    </div>

    <div class="grid grid-2">
        <section class="card" style="padding:28px;">
            <h2 style="margin-top:0;">Halo, <?= e((string) ($user['name'] ?? 'User')); ?></h2>
            <p class="muted" style="margin-top:8px;">
                Redirect sukses sudah berjalan. User aktif saat ini disimpan di session.
            </p>

            <div style="margin-top:24px;display:grid;gap:14px;">
                <div>
                    <div class="muted">Nama</div>
                    <strong><?= e((string) ($user['name'] ?? '-')); ?></strong>
                </div>
                <div>
                    <div class="muted">Email</div>
                    <strong><?= e((string) ($user['email'] ?? '-')); ?></strong>
                </div>
                <div>
                    <div class="muted">Sumber login</div>
                    <strong><?= e((string) ($user['source'] ?? '-')); ?></strong>
                </div>
            </div>
        </section>

        <aside class="card" style="padding:28px;background:var(--surface-alt);">
            <h2 style="margin-top:0;">Config MySQL</h2>
            <p class="muted" style="margin-top:8px;">
                Nilai berikut dibaca dari `.env` melalui `config/database.php`.
            </p>

            <div style="margin-top:22px;display:grid;gap:14px;">
                <div>
                    <div class="muted">Host</div>
                    <strong><?= e((string) ($dbConfig['host'] ?? '-')); ?></strong>
                </div>
                <div>
                    <div class="muted">Port</div>
                    <strong><?= e((string) ($dbConfig['port'] ?? '-')); ?></strong>
                </div>
                <div>
                    <div class="muted">Database</div>
                    <strong><?= e((string) ($dbConfig['database'] ?? '-')); ?></strong>
                </div>
                <div>
                    <div class="muted">Username</div>
                    <strong><?= e((string) ($dbConfig['username'] ?? '-')); ?></strong>
                </div>
            </div>
        </aside>
    </div>
</main>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/main.php');
