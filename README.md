# ZIS App

ZIS App sekarang memakai struktur PHP native yang ringan dan cocok untuk shared hosting atau cPanel tanpa SSH.

## Struktur

- root project sebagai web entry point (`index.php` dan `.htaccess`)
- `app/Core/` komponen dasar seperti bootstrap, router, session, response, config, dan koneksi database
- `app/Controllers/` controller request
- `app/Services/` service aplikasi
- `app/Repositories/` akses data
- `app/Views/` template halaman
- `app/Core/routes.php` definisi route web
- `config/` konfigurasi app dan MySQL
- `database/migrations/` file migration SQL
- `docs/domain-model.md` baseline domain model ZIS

## Fitur Awal

- halaman login
- login dari tabel `users` MySQL
- redirect sukses ke dashboard
- konfigurasi koneksi MySQL berbasis PDO

## Konfigurasi

Salin `.env.example` menjadi `.env`, lalu sesuaikan nilai berikut:

Contoh untuk local:

```env
APP_NAME="ZIS App"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zis_app
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
MIGRATION_TOKEN=change-this-to-a-long-random-secret
```

Contoh untuk hosting production/cPanel:

```env
APP_NAME="ZIS App"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://zis.masjidaladil.com
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=masc2518_zis_app
DB_USERNAME=masc2518_zis_app
DB_PASSWORD=your-db-password
DB_CHARSET=utf8mb4
MIGRATION_TOKEN=your-long-random-secret
```

Aplikasi akan mencari user dari tabel MySQL `users`.

Migration SQL sudah tersedia di:

- [001_create_users_table.sql](/Users/singgihperdana/data/source_code/projects/php/zis-app/database/migrations/001_create_users_table.sql:1)
- [002_seed_admin_user.sql](/Users/singgihperdana/data/source_code/projects/php/zis-app/database/migrations/002_seed_admin_user.sql:1)
- [003_align_users_and_create_zis_core_tables.sql](/Users/singgihperdana/data/source_code/projects/php/zis-app/database/migrations/003_align_users_and_create_zis_core_tables.sql:1)
- [004_bootstrap_reference_data.sql](/Users/singgihperdana/data/source_code/projects/php/zis-app/database/migrations/004_bootstrap_reference_data.sql:1)

Kalau mau eksekusi manual, pakai urutan ini:

```bash
mysql -u root -p zis_app < database/migrations/001_create_users_table.sql
mysql -u root -p zis_app < database/migrations/002_seed_admin_user.sql
mysql -u root -p zis_app < database/migrations/003_align_users_and_create_zis_core_tables.sql
mysql -u root -p zis_app < database/migrations/004_bootstrap_reference_data.sql
```

Atau jalankan migration otomatis lewat endpoint internal:

```bash
curl -X POST http://127.0.0.1:8000/internal/migrate \
  -H "X-Migration-Token: change-this-to-a-long-random-secret"
```

Endpoint ini akan:

- membuat tabel `schema_migrations` jika belum ada
- menjalankan file SQL yang belum pernah dieksekusi
- mencatat nama file migration yang sudah sukses

Response sukses contoh:

```json
{
  "success": true,
  "executed": [
    "001_create_users_table.sql",
    "002_seed_admin_user.sql",
    "003_align_users_and_create_zis_core_tables.sql",
    "004_bootstrap_reference_data.sql"
  ],
  "skipped": []
}
```

Isi tabel yang dipakai app:

```sql
CREATE TABLE users (
    id CHAR(36) NOT NULL PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT uk_users_username UNIQUE (username),
    CONSTRAINT uk_users_email UNIQUE (email)
);
```

Password harus disimpan dalam bentuk hash `password_hash(...)`.

Hash password aplikasi sekarang ditangani terpusat melalui `App\Core\Password`.

Seed user bawaan:

```sql
INSERT INTO users (id, username, email, password, role, active)
VALUES
(
    '00000000-0000-0000-0000-000000000001',
    'admin',
    'admin@yopmail.com',
    '$2y$10$XxcRBCSgj23oeiDlORkJ8ebqo7ucDudva/p3ImEHAeaY11BLkwFli',
    'ADMIN',
    1
),
(
    '00000000-0000-0000-0000-000000000002',
    'operator',
    'operator@yopmail.com',
    '$2y$10$2x11Tm8yo4O1.d.elWcsO.Kqp.Hf/RPMeORG2mmSWfjTEgzG2k9Mq',
    'OPERATOR',
    1
),
(
    '00000000-0000-0000-0000-000000000003',
    'viewer',
    'viewer@yopmail.com',
    '$2y$10$r9iWDXZ6DWHU/IQm.OAPAeO9nOypASTS4Xgby0GqKjCE/tCcm4wD6',
    'VIEWER',
    1
);
```

## Menjalankan Lokal

Kalau mau menjalankan lokal dengan built-in server PHP:

```bash
php -S 127.0.0.1:8000
```

Lalu buka `http://127.0.0.1:8000/login`.

## Deploy

Deploy ke cPanel bisa dilakukan lewat GitHub Actions FTP menggunakan workflow di:

`/.github/workflows/deploy-cpanel.yml`

Secrets yang diperlukan:

- `FTP_SERVER`
- `FTP_USER`
- `FTP_PASSWORD`
- `FTP_PORT`
- `FTP_SERVER_DIR`
- `MIGRATION_URL`
- `MIGRATION_TOKEN`

Contoh:

- `MIGRATION_URL=https://zis.masjidaladil.com/internal/migrate`
- `MIGRATION_TOKEN` harus sama dengan `MIGRATION_TOKEN` di `.env` server

## Domain Model

Baseline domain model untuk rewrite modul ZIS ada di:

- [domain-model.md](/Users/singgihperdana/data/source_code/projects/php/zis-app/docs/domain-model.md:1)
