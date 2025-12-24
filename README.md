Terminal Pintar
===============

Platform pembelajaran berbasis Laravel 12 + Inertia/Vue 3 untuk peran admin, guru, dan orang tua. Proyek ini mencakup manajemen siswa, perpustakaan, jadwal/materi guru, dan kehadiran.

Ringkasan Fitur
---------------
- **Admin**: kelola siswa, buku, jadwal, berita, dan pengguna/role (Spatie Permission).
- **Guru**: kelola jadwal, unggah materi (PDF/PNG/JPEG, maks 10 MB), catat kehadiran.
- **Orang Tua**: akses jadwal/kemajuan siswa (via rute orang tua).
- **Perpustakaan**: katalog buku dengan stok, status, dan log aktivitas.

Tech Stack
----------
- Backend: Laravel 12, PHP 8.x, Spatie Permission.
- Frontend: Inertia + Vue 3, Vite build.
- DB: MySQL.
- UI: Tailwind/CSS utility (lihat folder resources/css) + Heroicons.

Struktur Direktori Utama
-----------------------
- [app/Http/Controllers](app/Http/Controllers): logika per peran (Admin, Guru, OrangTua, Public).
- [app/Http/Requests](app/Http/Requests): validasi (mis. siswa di Admin/Student; materi di Guru/StoreMaterialRequest).
- [app/Models](app/Models): model Eloquent (Student, User, Book, Material, Loan, dll.).
- [routes](routes): rute per peran ([web.php](routes/web.php), [guru.php](routes/guru.php), [orangtua.php](routes/orangtua.php), [console.php](routes/console.php)).
- [resources/js/Pages](resources/js/Pages): halaman Inertia (Admin, Guru, Orangtua, Public).
- [resources/js/Components](resources/js/Components): komponen UI bersama.
- [resources/css](resources/css): gaya global.
- [public/build](public/build): aset hasil Vite build.
- [database/migrations](database/migrations): skema; [database/seeders](database/seeders): seeder; [database/factories](database/factories): factory.

Prasyarat
---------
- PHP 8.x dengan ekstensi umum (fileinfo, pdo, mbstring, dsb.).
- Composer
- Node.js 18+ dan npm
- MySQL/MariaDB

Konfigurasi Lingkungan
----------------------
1. Salin .env.example menjadi .env dan setel variabel DB, APP_URL, MAIL, dsb.
2. Kunci aplikasi: `php artisan key:generate`.
3. Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis; buat symlink storage bila perlu: `php artisan storage:link`.

Instalasi
---------
1. Backend deps: `composer install`
2. Frontend deps: `npm install`
3. Migrasi + seed (membuat admin `admin@example.com` / `pass1234`): `php artisan migrate --seed`

Menjalankan secara Lokal
------------------------
- Laravel server: `php artisan serve`
- Vite dev server: `npm run dev`
- Akses via APP_URL (default http://127.0.0.1:80)

Build Produksi
--------------
- Jalankan: `npm run build`
- Deploy hasil di `public/build` (sertakan manifest). Pastikan cache browser/CDN di-refresh setelah deploy.

Catatan Upload Materi (Guru)
----------------------------
- Validasi server: PDF/PNG/JPEG, ukuran maks 10 MB (lihat [app/Http/Requests/Guru/StoreMaterialRequest.php](app/Http/Requests/Guru/StoreMaterialRequest.php)).
- Jika server menolak file <10 MB dengan 422 “The file failed to upload”, pastikan limit server cukup besar:
  - PHP: `upload_max_filesize`, `post_max_size` > 10M; `memory_limit` memadai.
  - Web server: `client_max_body_size` (Nginx) atau `LimitRequestBody` (Apache/cPanel).
  - Pastikan ruang disk dan izin tulis pada `storage` dan direktori tmp.

Testing
-------
- Jalankan PHPUnit: `phpunit` atau `./vendor/bin/phpunit`

Peran & Otentikasi
------------------
- Role: admin, guru, ortu (Spatie Permission).
- Admin awal dari seeder: email `admin@example.com`, password `pass1234`.

Referensi Cepat
---------------
- Buku perpustakaan: controller [app/Http/Controllers/Admin/BookController.php](app/Http/Controllers/Admin/BookController.php), UI [resources/js/Pages/Admin/Books](resources/js/Pages/Admin/Books).
- Siswa: controller [app/Http/Controllers/Admin/StudentController.php](app/Http/Controllers/Admin/StudentController.php); UI [resources/js/Pages/Admin/Students](resources/js/Pages/Admin/Students).
- Jadwal & materi guru: UI [resources/js/Pages/Guru/Jadwal.vue](resources/js/Pages/Guru/Jadwal.vue); API rute di [routes/guru.php](routes/guru.php).

Troubleshooting Singkat
-----------------------
- Masalah upload > batas: cek limit PHP/web server (lihat catatan di atas).
- Aset lama muncul di production: rebuild `npm run build`, unggah `public/build`, lalu hard refresh/bersihkan cache CDN.# Terminal Pintar — Struktur Proyek

Ringkasan anatomi repo agar mudah dinavigasi dan dikembangkan.

## Tech Stack
- Laravel 12 + PHP 8.x
- Inertia + Vue 3 (Vite)
- Tailwind/CSS utility di [resources/css](resources/css)
- Spatie Permission (peran admin, guru, ortu)

## Struktur Direktori
- [app](app): kode backend Laravel
	- [Http/Controllers](app/Http/Controllers): controller admin/guru/orangtua; modul siswa di [app/Http/Controllers/Admin/StudentController.php](app/Http/Controllers/Admin/StudentController.php)
	- [Http/Requests](app/Http/Requests): validasi request (mis. siswa di [app/Http/Requests/Admin/Student](app/Http/Requests/Admin/Student))
	- [Models](app/Models): model Eloquent (Student, User, Book, Loan, dll.)
	- [Policies](app/Policies): kebijakan akses
	- [Repositories](app/Repositories): lapisan data (Admin/Public/Orangtua)
	- [Services](app/Services): logika domain terpisah per modul
	- [Providers](app/Providers): service providers & binding
- [bootstrap](bootstrap): bootstrap Laravel & cache konfigurasi
- [config](config): konfigurasi aplikasi (app, auth, queue, permission, dll.)
- [database](database): migrasi, seeder, factory
	- [migrations](database/migrations): skema tabel (users, students, books, loans, permissions, dll.)
	- [seeders](database/seeders): seeder data; `DatabaseSeeder` membuat admin `admin@example.com`
	- [factories](database/factories): factory Book, Student, News, User
- [public](public): entry `index.php`, aset build Vite di [public/build](public/build)
- [resources](resources): sumber front-end
	- [js/Pages](resources/js/Pages): halaman Inertia (Admin, Guru, Orangtua, Public)
	- [js/Components](resources/js/Components): komponen UI bersama
	- [css](resources/css): gaya global
	- [views](resources/views): blade minimal untuk Inertia root
- [routes](routes): rute per peran ([web.php](routes/web.php), [guru.php](routes/guru.php), [orangtua.php](routes/orangtua.php), [console.php](routes/console.php))
- [storage](storage): log, cache, upload (butuh izin tulis)
- [tests](tests): kerangka pengujian
- [vite.config.js](vite.config.js): konfigurasi Vite + alias
- [package.json](package.json) & [composer.json](composer.json): dependensi FE/BE

## Modul Utama
- Siswa: controller di [app/Http/Controllers/Admin/StudentController.php](app/Http/Controllers/Admin/StudentController.php); UI di [resources/js/Pages/Admin/Students](resources/js/Pages/Admin/Students)
- Buku/Perpustakaan: controller di [app/Http/Controllers/Admin/BookController.php](app/Http/Controllers/Admin/BookController.php); UI di [resources/js/Pages/Admin/Books](resources/js/Pages/Admin/Books)
- Kehadiran Guru: helper di [app/Http/Controllers/Guru/Concerns/BuildsAttendanceData.php](app/Http/Controllers/Guru/Concerns/BuildsAttendanceData.php); UI di [resources/js/Pages/Guru](resources/js/Pages/Guru)
- Orang Tua: rute di [routes/orangtua.php](routes/orangtua.php); UI di [resources/js/Pages/Orangtua](resources/js/Pages/Orangtua)

## Peran & Seeder
- Role: admin, guru, ortu (Spatie)
- Seeder awal: [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php) membuat admin `admin@example.com` (password `pass1234`). Buka komentar seeder lain jika diperlukan.

## Build & Pengembangan
- Install deps: `composer install` dan `npm install`
- Jalankan dev server: `php artisan serve` + `npm run dev`
- Build produksi: `npm run build`
- Migrasi + seed: `php artisan migrate --seed` atau `php artisan migrate:fresh --seed`

## Catatan
- Aset hasil build berada di [public/build](public/build)
- Pastikan `storage` dapat ditulis (`php artisan storage:link` jika perlu)
