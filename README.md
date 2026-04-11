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
```

Aplikasi akan mencari user dari tabel MySQL berikut:

```sql
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);
```

Password harus disimpan dalam bentuk hash `password_hash(...)`.

Cara cepat membuat hash password:

```bash
php tools/hash-password.php "password-kamu"
```

Contoh insert user:

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
