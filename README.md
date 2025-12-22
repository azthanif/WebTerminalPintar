# Terminal Pintar — Struktur Proyek

Ringkasan anatomi repo agar mudah dinavigasi dan dikembangkan.

## Tech Stack
- Laravel 10 + PHP 8.x
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
