<?php

$title = $title ?? 'User';
$formUser = is_array($formUser ?? null) ? $formUser : [];
$formTitle = $formTitle ?? 'Tambah User';
$formAction = $formAction ?? '/user/add';
$errorMessage = \App\Core\Session::pullFlash('error');

ob_start();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><?= e($formTitle === 'Tambah User' ? 'User Management' : 'User Management'); ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active"><?= e($formTitle); ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (is_string($successMessage ?? null) && $successMessage !== ''): ?>
            <div class="alert alert-success"><?= e($successMessage); ?></div>
        <?php endif; ?>
        <?php if (is_string($errorMessage ?? null) && $errorMessage !== ''): ?>
            <div class="alert alert-danger"><?= e($errorMessage); ?></div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><?= e($formTitle); ?></h3>
                    </div>
                    <form role="form" method="post" action="<?= e($formAction); ?>">
                        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken); ?>">
                        <input type="hidden" name="id" value="<?= e((string) ($formUser['id'] ?? '')); ?>">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="email">Email address</label>
                                <input class="form-control" id="email" placeholder="Enter email"
                                       type="email" name="email" value="<?= e((string) ($formUser['email'] ?? '')); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input class="form-control" id="username" placeholder="Enter username"
                                       type="text" name="username" value="<?= e((string) ($formUser['username'] ?? '')); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password <?php if (($formUser['id'] ?? '') !== ''): ?><span class="text-muted">(kosong = tidak diubah)</span><?php endif; ?></label>
                                <input class="form-control" id="password" placeholder="Enter Password"
                                       type="password" name="password" <?= ($formUser['id'] ?? '') === '' ? 'required' : ''; ?>>
                            </div>
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="" disabled <?= (($formUser['role'] ?? '') === '') ? 'selected' : ''; ?>>Pilih role...</option>
                                    <option value="OPERATOR" <?= (($formUser['role'] ?? '') === 'OPERATOR') ? 'selected' : ''; ?>>OPERATOR</option>
                                    <option value="VIEWER" <?= (($formUser['role'] ?? '') === 'VIEWER') ? 'selected' : ''; ?>>VIEWER</option>
                                </select>
                            </div>
                            <?php if (($formUser['id'] ?? '') !== ''): ?>
                                <div class="form-group">
                                    <label for="active">Active</label>
                                    <select class="form-control" id="active" name="active">
                                        <option value="true" <?= !empty($formUser['active']) ? 'selected' : ''; ?>>Active</option>
                                        <option value="false" <?= empty($formUser['active']) ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer">
                            <button class="btn btn-primary" type="submit">Simpan</button>
                            <a class="btn btn-secondary ml-2" href="/settings/users">Batal</a>
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
