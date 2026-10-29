# SIM-MBG (Sistem Informasi Manajemen Makan Bergizi Gratis)

Aplikasi web berbasis **Laravel 13 (PHP 8.5)** untuk pengelolaan distribusi, pemilihan menu harian, pemantauan alergi siswa, dan evaluasi program **Makan Bergizi Gratis (MBG)** yang menghubungkan **Siswa**, **Satuan Pelayanan Pemenuhan Gizi (SPPG)**, dan **Administrator**.

---

## Fitur Utama Berdasarkan Peran

### 1. Publik & Autentikasi
- **Landing Page (`/`)**: Informasi program MBG, alur pemesanan 3 langkah, dan katalog paket menu bergizi.
- **Autentikasi Terpadu (`/login`, `/register`, `/logout`)**: Mendukung login menggunakan `username`, `email`, atau `NISN` siswa, dilengkapi pengalihan otomatis (*single gateway redirect* `/dashboard`) sesuai peran pengguna.

### 2. Siswa (`/student/*`)
- **Dashboard Siswa (`/student/dashboard`)**: Ringkasan jadwal distribusi makan siang hari ini, status pengambilan, pengumuman SPPG, dan detail gizi menu harian.
- **Pilih Menu (`/student/menu`)**: Pemilihan paket menu harian (Paket A/B/C) dengan indikator batas waktu (*cut-off*), peringatan alergen otomatis sesuai profil siswa, dan ringkasan keseimbangan gizi mingguan.
- **Riwayat & Ulasan (`/student/history`, `/student/review`)**: Riwayat pengambilan makanan dan pemberian rating/ulasan terhadap menu yang telah diterima.
- **Profil Saya (`/student/profile`)**: Pengelolaan data diri serta pengaturan alergi dan pantangan makanan.

### 3. Petugas SPPG (`/sppg/*`)
- **Dashboard SPPG (`/sppg/dashboard`)**: Ringkasan total porsi harian, jumlah sekolah mitra, aktivitas distribusi & verifikasi, serta rekap pengiriman per sekolah.
- **Manajemen Siklus Menu (`/sppg/menu`)**: Pengelolaan daftar menu reguler, menu khusus alergi, dan menu cadangan beserta kandungan makronutrisi.
- **Preferensi & Pilihan Siswa (`/sppg/selections`)**: Rekapitulasi pilihan menu siswa per sekolah dan pemetaan kebutuhan diet khusus/alergi.
- **Review & Evaluasi (`/sppg/review`)**: Pemantauan tingkat kepuasan siswa, umpan balik sekolah, dan audit gizi dapur.

### 4. Administrator (`/admin/*`)
- **Dashboard Admin (`/admin/dashboard`)**: Ikhtisar KPI seluruh ekosistem (total siswa, sekolah, unit SPPG, staf), log aktivitas terbaru, dan data ringkas sekolah.
- **Manajemen Siswa (`/admin/students`)**: Data penerima MBG, status menu reguler vs khusus, dan catatan alergi.
- **Manajemen Sekolah (`/admin/schools`)**: Daftar sekolah penerima manfaat, kuota siswa, dan pemetaan unit SPPG.
- **Manajemen SPPG (`/admin/sppg`)**: Data unit dapur SPPG, kapasitas produksi harian, dan cakupan wilayah.
- **Manajemen Pengguna (`/admin/users`)**: Pengelolaan akun seluruh peran (`admin`, `sppg`, `student`).

---

## Teknologi yang Digunakan

- **Backend**: PHP 8.5, Laravel 13
- **Frontend**: Blade Templates, CSS3 (Custom Design System sesuai audit Figma), Vanilla JavaScript
- **Database**: MySQL / MariaDB / SQLite (melalui Laravel Migrations & Seeders)
- **Tooling**: Composer, Vite, Laravel Pint, PHPUnit, Laravel Boost

---

## Struktur Direktori Utama

```text
SIM-MBG/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── StudentDashboardController.php
│   │   │   ├── SPPGDashboardController.php
│   │   │   └── AdminDashboardController.php
│   │   └── Middleware/
│   │       └── CheckRole.php
│   └── Models/
│       └── User.php
├── database/
│   ├── migrations/          # Skema tabel SIM-MBG (users, schools, students, menus, sppg_units, dll.)
│   └── seeders/             # Data awal lengkap untuk demo seluruh role
├── public/
│   ├── assets/              # Ikon SVG dan gambar ilustrasi/menu
│   ├── css/                 # Stylesheet modular (reset, variables, global, components, layout, pages/*)
│   └── js/                  # Script interaktivitas per halaman (main.js, pages/*)
├── resources/
│   └── views/
│       ├── index.blade.php  # Landing page publik
│       ├── auth/            # Halaman login & register
│       ├── student/         # Halaman modul Siswa
│       ├── sppg/            # Halaman modul SPPG
│       └── admin/           # Halaman modul Admin
├── routes/
│   └── web.php              # Definisi rute web, middleware role, dan redirect kompatibilitas
├── AGENTS.md                # Panduan pengembangan tim & AI Agent (Laravel + Frontend)
├── CLAUDE.md                # Aturan Laravel Boost & konteks arsitektur proyek
└── DESIGN_SYSTEM.md         # Dokumentasi hasil audit desain Figma SIM-MBG
```

---

## Panduan Instalasi & Menjalankan Proyek

### 1. Prasyarat
- PHP >= 8.3 (direkomendasikan PHP 8.5)
- Composer
- Database (MySQL / MariaDB atau SQLite)
- Node.js & NPM (opsional, untuk asset bundling Vite)

### 2. Langkah Setup

```bash
# 1. Install dependensi PHP
composer install

# 2. Salin konfigurasi environment & buat application key
cp .env.example .env
php artisan key:generate

# 3. Sesuaikan konfigurasi database di file .env, lalu jalankan migrasi beserta seeder
php artisan migrate --seed

# 4. Jalankan server pengembangan Laravel
php artisan serve
```

Aplikasi dapat diakses di `http://localhost:8000`.

---

## Akun Demo Default (Hasil Seeder)

Setelah menjalankan `php artisan db:seed` (atau `php artisan migrate:fresh --seed`), Anda dapat masuk menggunakan akun berikut:

| Peran | Username / Email | Password | Keterangan |
|---|---|---|---|
| **Admin** | `admin` / `admin@mbg.go.id` | `password` | Mengakses `/admin/dashboard` (atau sesuai variabel `.env` `ADMIN_USERNAME` & `ADMIN_PASSWORD`) |
| **Petugas SPPG** | `pgw.alya` / `sppg.dps01@mbg.go.id` | `embegeh` | SPPG Denpasar Barat 01 (`/sppg/dashboard`) |
| **Siswa** | `alya` / `alya@sekolah.id` | `password` | Siswa SMA Negeri 1 Denpasar (`/student/dashboard`) |
| **Siswa** | `aditya` / `aditya@sekolah.id` | `password` | Siswa SMP Negeri 1 Denpasar (`/student/dashboard`) |
| **Siswa** | `gita` / `gita@sekolah.id` | `password` | Siswa SMP Negeri 2 Denpasar (`/student/dashboard`) |

---

## Daftar Rute Utama

| Grup | URL | Nama Rute | Controller / View |
|---|---|---|---|
| **Publik** | `/` | `home` | `index.blade.php` |
| **Auth** | `/login`, `/register`, `/logout` | `login`, `register`, `logout` | `AuthController` |
| **Gateway** | `/dashboard` | `dashboard` | `DashboardController@index` (Auto-redirect per role) |
| **Siswa** | `/student/dashboard`, `/student/menu`, `/student/history`, `/student/profile`, `/student/review` | `student.*` | `StudentDashboardController` |
| **SPPG** | `/sppg/dashboard`, `/sppg/menu`, `/sppg/selections`, `/sppg/review` | `sppg.*` | `SPPGDashboardController` |
| **Admin** | `/admin/dashboard`, `/admin/students`, `/admin/schools`, `/admin/sppg`, `/admin/users` | `admin.*` | `AdminDashboardController` |

---

## Dokumentasi Terkait
- [`AGENTS.md`](AGENTS.md) — Standar kode, pembagian modul tim, struktur Laravel + Blade + CSS/JS, dan aturan kolaborasi Git.
- [`DESIGN_SYSTEM.md`](DESIGN_SYSTEM.md) — Spesifikasi token warna, tipografi, komponen, dan hasil audit file Figma SIM-MBG.
- [`CLAUDE.md`](CLAUDE.md) — Panduan Laravel Boost untuk AI Coding Assistant.
