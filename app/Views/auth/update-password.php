<?php

$title = $title ?? 'Update Password';
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

ob_start();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Update Password</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Update Password</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (is_string($successMessage) && $successMessage !== ''): ?>
            <div class="alert alert-success"><?= e($successMessage); ?></div>
        <?php endif; ?>
        <?php if (is_string($errorMessage) && $errorMessage !== ''): ?>
            <div class="alert alert-danger"><?= e($errorMessage); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8 col-lg-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Form Update Password</h3>
                    </div>
                    <form method="post" action="/update-password">
                        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken); ?>">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="current_password">Password Lama</label>
                                <input class="form-control" id="current_password" name="current_password" type="password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Password Baru</label>
                                <input class="form-control" id="new_password" name="new_password" type="password" required>
                                <small class="form-text text-muted">Minimal 6 karakter.</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Konfirmasi Password Baru</label>
                                <input class="form-control" id="confirm_password" name="confirm_password" type="password" required>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-primary" type="submit">Simpan</button>
                            <a class="btn btn-secondary ml-2" href="/dashboard">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/admin.php');
