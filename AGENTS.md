<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>

---

# SIM-MBG — Team & Agent Development Guidelines (Laravel Integration)

Dokumen ini menjadi panduan kerja bersama seluruh anggota tim dan AI Agent dalam mengembangkan **SIM-MBG (Sistem Informasi Manajemen Makan Bergizi Gratis)** yang kini telah terintegrasi penuh dengan **Laravel 13**.

---

## 1. PROJECT OVERVIEW

**Project:** SIM-MBG (Sistem Informasi Manajemen Makan Bergizi Gratis)

**Tech Stack:**
- **Backend:** PHP 8.5, Laravel 13
- **Database:** Relational Database (MySQL / MariaDB / SQLite) dengan Laravel Migrations & Seeders
- **Views:** Laravel Blade (`resources/views/**/*.blade.php`)
- **Styling & Scripting:** Custom CSS3 (`public/css/`) & Vanilla JavaScript (`public/js/`) mengacu pada `DESIGN_SYSTEM.md`

Prinsip Utama:
- Seluruh halaman utama dirender melalui **Laravel Route -> Controller -> Blade View**.
- Gunakan helper Laravel seperti `route('...')`, `asset('...')`, `@csrf`, dan `@auth` di dalam file Blade.
- Jangan menambahkan framework CSS eksternal (seperti Bootstrap/Tailwind CDN) yang menimpa token desain di `public/css/` tanpa persetujuan tim.

---

## 2. TEAM RESPONSIBILITY & MODULE OWNERSHIP

### Member 1 — Public & Authentication
- **Controller:** `App\Http\Controllers\AuthController`, `App\Http\Controllers\DashboardController`
- **Views:** `resources/views/index.blade.php`, `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`
- **Assets:** `public/css/pages/landing.css`, `public/css/pages/login.css`, `public/js/pages/landing.js`, `public/js/pages/login.js`, `public/js/pages/register.js`
- **Tanggung jawab:** Landing page, autentikasi multi-identitas (username/email/NISN), registrasi siswa, session management, dan redirect otomatis berdasarkan role (`/dashboard`).

### Member 2 — Modul Siswa (Student)
- **Controller:** `App\Http\Controllers\StudentDashboardController`
- **Views:** `resources/views/student/dashboard.blade.php`, `menu.blade.php`, `history.blade.php`, `profile.blade.php`, `review.blade.php`
- **Assets:** `public/css/pages/siswa.css`, `public/js/pages/siswa.js`
- **Routes:** Prefix `/student/*` dengan nama rute `student.*` (middleware `auth`, `role:student,siswa`)
- **Tanggung jawab:** Dashboard siswa, pemilihan paket makanan harian, pengecekan alergi siswa, riwayat pengambilan, profil alergi, dan ulasan makanan.

### Member 3 — Modul SPPG
- **Controller:** `App\Http\Controllers\SPPGDashboardController`
- **Views:** `resources/views/sppg/dashboard.blade.php`, `menu.blade.php`, `selections.blade.php`, `review.blade.php`
- **Assets:** `public/css/pages/sppg.css`, `public/js/pages/sppg.js`
- **Routes:** Prefix `/sppg/*` dengan nama rute `sppg.*` (middleware `auth`, `role:sppg,guru`)
- **Tanggung jawab:** Dashboard operasional SPPG, manajemen siklus menu & makronutrisi, rekap pilihan/alergi siswa, dan evaluasi ulasan/audit gizi.

### Member 4 — Modul Admin
- **Controller:** `App\Http\Controllers\AdminDashboardController`
- **Views:** `resources/views/admin/dashboard.blade.php`, `students.blade.php`, `schools.blade.php`, `sppg.blade.php`, `users.blade.php`
- **Assets:** `public/css/pages/admin.css`, `public/js/pages/admin.js`
- **Routes:** Prefix `/admin/*` dengan nama rute `admin.*` (middleware `auth`, `role:admin`)
- **Tanggung jawab:** Dashboard eksekutif admin, manajemen master data siswa, sekolah, unit SPPG, dan akun pengguna.

---

## 3. STRUKTUR DIREKTORI LARAVEL SIM-MBG

```text
SIM-MBG/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminDashboardController.php
│   │   │   ├── AuthController.php
│   │   │   ├── Controller.php
│   │   │   ├── DashboardController.php
│   │   │   ├── SPPGDashboardController.php
│   │   │   └── StudentDashboardController.php
│   │   └── Middleware/
│   │       └── CheckRole.php
│   └── Models/
│       └── User.php
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── AdminSeeder.php
│       ├── SppgSeeder.php
│       ├── UserSeeder.php
│       ├── SppgUnitSeeder.php
│       ├── SchoolSeeder.php
│       ├── StudentSeeder.php
│       ├── AllergenSeeder.php
│       ├── MenuSeeder.php
│       ├── MenuAllergenSeeder.php
│       ├── MenuScheduleSeeder.php
│       ├── MenuSelectionSeeder.php
│       ├── StudentAllergySeeder.php
│       ├── DistributionSeeder.php
│       ├── PickupSeeder.php
│       ├── StudentReviewSeeder.php
│       ├── SchoolFeedbackSeeder.php
│       ├── KitchenAuditSeeder.php
│       ├── AnnouncementSeeder.php
│       └── ActivityLogSeeder.php
├── public/
│   ├── assets/
│   │   ├── icons/
│   │   └── images/
│   ├── css/
│   │   ├── reset.css
│   │   ├── variables.css
│   │   ├── global.css
│   │   ├── components.css
│   │   ├── layout.css
│   │   └── pages/
│   │       ├── landing.css
│   │       ├── login.css
│   │       ├── siswa.css
│   │       ├── sppg.css
│   │       └── admin.css
│   └── js/
│       ├── main.js
│       └── pages/
│           ├── landing.js
│           ├── login.js
│           ├── register.js
│           ├── siswa.js
│           ├── sppg.js
│           └── admin.js
├── resources/
│   └── views/
│       ├── index.blade.php
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── student/
│       │   ├── dashboard.blade.php
│       │   ├── menu.blade.php
│       │   ├── history.blade.php
│       │   ├── profile.blade.php
│       │   └── review.blade.php
│       ├── sppg/
│       │   ├── dashboard.blade.php
│       │   ├── menu.blade.php
│       │   ├── selections.blade.php
│       │   └── review.blade.php
│       └── admin/
│           ├── dashboard.blade.php
│           ├── students.blade.php
│           ├── schools.blade.php
│           ├── sppg.blade.php
│           └── users.blade.php
└── routes/
    └── web.php
```

---

## 4. ATURAN BLADE, ROUTING, & ASSET

### 4.1 Pemanggilan Asset di Blade
Selalu gunakan helper `asset()` agar path tidak rusak di sub-route:
```blade
<link rel="stylesheet" href="{{ asset('css/variables.css') }}">
<link rel="stylesheet" href="{{ asset('css/pages/siswa.css') }}">
<script src="{{ asset('js/pages/siswa.js') }}" defer></script>
<img src="{{ asset('assets/icons/logo.svg') }}" alt="Logo SIM-MBG">
```

### 4.2 Navigasi & Named Routes
Utamakan penggunaan `route()` dibandingkan URL hardcoded atau link `.html` lama:
```blade
<a href="{{ route('student.dashboard') }}">Dashboard</a>
<a href="{{ route('student.menu') }}">Pilih Menu</a>
```
*(Catatan: `routes/web.php` tetap menyediakan rute redirect kompatibilitas untuk URL `.html` lama agar transisi berjalan mulus).*

### 4.3 Keamanan Form & Request
- Setiap form `POST`/`PUT`/`DELETE` di Blade wajib menyertakan `@csrf`.
- Gunakan middleware `auth` dan `role:...` (`App\Http\Middleware\CheckRole`) untuk membatasi akses antar peran.

---

## 5. DATABASE, MIGRASI, & SEEDER

- Jangan mengubah tabel secara manual di database lokal tanpa membuat atau memperbarui file **Migration** di `database/migrations/`.
- Gunakan `php artisan migrate:fresh --seed` untuk mereset dan mengisi ulang database dengan data sampel yang terhubung secara relasional.
- Urutan eksekusi seeder diatur di `DatabaseSeeder.php` mengikuti hierarki foreign key:
  1. Master Induk (`AdminSeeder`, `SppgSeeder`, `UserSeeder`, `SppgUnitSeeder`, `AllergenSeeder`, `MenuSeeder`)
  2. Entitas Tingkat 1 (`SchoolSeeder`, `SppgStaffSeeder`)
  3. Entitas Tingkat 2 (`StudentSeeder`, `MenuAllergenSeeder`, `MenuScheduleSeeder`, `DistributionSeeder`, `ActivityLogSeeder`, `AnnouncementSeeder`, `SchoolFeedbackSeeder`, `KitchenAuditSeeder`)
  4. Entitas Tingkat 3 (`StudentAllergySeeder`, `MenuSelectionSeeder`, `PickupSeeder`, `StudentReviewSeeder`)

---

## 6. FRONTEND & DESIGN SYSTEM CONVENTIONS

1. **Source of Truth Desain**: Mengacu pada [`DESIGN_SYSTEM.md`](DESIGN_SYSTEM.md) (hasil audit Figma SIM-MBG).
2. **CSS Rules**:
   - Hindari inline style (`style="..."`); gunakan class dengan penamaan `kebab-case` yang deskriptif.
   - Simpan token warna (`--color-primary: #297373`, `--color-bg: #FAFAF9`, `--color-accent: #FF8552`, `--color-secondary: #85FFC7`, `--color-text: #39393A`) di `public/css/variables.css`.
   - File bersama (`variables.css`, `global.css`, `components.css`, `layout.css`, `main.js`) hanya boleh diubah dengan koordinasi tim.
3. **JavaScript Rules**:
   - Gunakan `camelCase` untuk variabel dan fungsi.
   - Selalu cek keberadaan elemen DOM (`if (element) { ... }`) sebelum memasang event listener.

---

## 7. GIT WORKFLOW & CHECKLIST SEBELUM PUSH

1. Gunakan branch fitur/pengembangan (`dev`, `feature/siswa`, `feature/sppg`, `feature/admin`, dll.) sebelum merge ke `main`.
2. Gunakan format commit konvensional: `feat:`, `fix:`, `style:`, `refactor:`, `docs:`, `chore:`.
3. Jalankan `vendor/bin/pint --dirty` setelah memodifikasi file PHP.
4. Pastikan sebelum push:
   - `[ ]` `php artisan migrate:fresh --seed` berjalan tanpa error.
   - `[ ]` Rute halaman terkait dapat diakses (`php artisan route:list`).
   - `[ ]` Tidak ada error PHP/Blade maupun error di browser console.
   - `[ ]` Asset CSS, JS, dan gambar tampil dengan benar melalui `asset()`.
