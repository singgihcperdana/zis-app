<?php

$title = $title ?? 'Kelola User';

ob_start();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Kelola User</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Pengaturan</li>
                    <li class="breadcrumb-item active">Kelola User</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success d-none" id="alertSuccess"></div>
                <div class="alert alert-danger d-none" id="alertError"></div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar User (kecuali Admin)</h3>
                <div class="card-tools">
                    <a class="btn btn-primary btn-sm" href="/user/add">Tambah User</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped" id="userTable">
                    <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Active</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
    (function () {
        const alertSuccess = document.getElementById('alertSuccess');
        const alertError = document.getElementById('alertError');
        const tbody = document.querySelector('#userTable tbody');

        function showError(msg) {
            alertSuccess.classList.add('d-none');
            alertError.classList.remove('d-none');
            alertError.textContent = msg || 'Terjadi kesalahan';
        }

        function showSuccess(msg) {
            alertError.classList.add('d-none');
            alertSuccess.classList.remove('d-none');
            alertSuccess.textContent = msg || 'Berhasil';
            window.setTimeout(function () {
                alertSuccess.classList.add('d-none');
            }, 3000);
        }

        async function loadUsers() {
            try {
                const response = await fetch('/api/users', { headers: { Accept: 'application/json' } });
                if (!response.ok) {
                    const payload = await response.json().catch(function () { return {}; });
                    throw new Error(payload.message || 'Gagal memuat users');
                }
                const list = await response.json();
                render(list.filter(function (u) {
                    return u.role !== 'ADMIN' && u.active === true;
                }));
            } catch (error) {
                showError(String(error.message || error));
            }
        }

        function render(list) {
            tbody.innerHTML = '';
            list.forEach(function (u) {
                const tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + u.username + '</td>' +
                    '<td>' + u.email + '</td>' +
                    '<td>' + u.role + '</td>' +
                    '<td>' + (u.active ? 'Yes' : 'No') + '</td>' +
                    '<td></td>';

                const actions = tr.querySelector('td:last-child');
                const btnEdit = document.createElement('a');
                btnEdit.className = 'btn btn-sm btn-outline-primary mr-1';
                btnEdit.href = '/user/edit/' + encodeURIComponent(u.id);
                btnEdit.textContent = 'Edit';

                const btnDelete = document.createElement('button');
                btnDelete.className = 'btn btn-sm btn-outline-danger';
                btnDelete.type = 'button';
                btnDelete.textContent = 'Hapus';
                btnDelete.addEventListener('click', function () {
                    removeUser(u.id);
                });

                actions.appendChild(btnEdit);
                actions.appendChild(btnDelete);
                tbody.appendChild(tr);
            });
        }

        async function removeUser(id) {
            if (!window.confirm('Hapus user ini?')) {
                return;
            }
            try {
                const response = await fetch('/api/users/' + encodeURIComponent(id), { method: 'DELETE' });
                if (!response.ok && response.status !== 204) {
                    const payload = await response.json().catch(function () { return {}; });
                    throw new Error(payload.message || 'Gagal menghapus user');
                }
                showSuccess('User dinonaktifkan');
                await loadUsers();
            } catch (error) {
                showError(String(error.message || error));
            }
        }

        loadUsers();
    })();
</script>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/admin.php');
