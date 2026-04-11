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

## Fitur Awal

- halaman login
- login dari tabel `users` MySQL
- redirect sukses ke dashboard
- konfigurasi koneksi MySQL berbasis PDO

## Konfigurasi

Salin `.env.example` menjadi `.env`, lalu sesuaikan nilai berikut:

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

Aplikasi akan mencari user dari tabel MySQL `users`.

Migration SQL sudah tersedia di:

- [001_create_users_table.sql](/Users/singgihperdana/data/source_code/projects/php/zis-app/database/migrations/001_create_users_table.sql:1)
- [002_seed_admin_user.sql](/Users/singgihperdana/data/source_code/projects/php/zis-app/database/migrations/002_seed_admin_user.sql:1)

Kalau mau eksekusi manual, pakai urutan ini:

```bash
mysql -u root -p zis_app < database/migrations/001_create_users_table.sql
mysql -u root -p zis_app < database/migrations/002_seed_admin_user.sql
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
    "002_seed_admin_user.sql"
  ],
  "skipped": []
}
```

Isi tabel yang dipakai app:

```sql
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Password harus disimpan dalam bentuk hash `password_hash(...)`.

Cara cepat membuat hash password:

```bash
php tools/hash-password.php "password-kamu"
```

Seed admin bawaan:

```sql
INSERT INTO users (name, email, password)
VALUES (
    'Administrator',
    'admin@example.com',
    '$2y$10$wxPdHiYXPz8L2zDG.eVqCuwOcUwM9G3gJv2GlUdwEUCI2TK5c4tX6'
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
