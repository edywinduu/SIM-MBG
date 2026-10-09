# AGENTS.md

## SIM-MBG Frontend — Team Development Guidelines

Dokumen ini menjadi aturan kerja bersama seluruh anggota tim dalam mengembangkan frontend SIM-MBG.

Tujuan:
- Menjaga struktur project tetap rapi.
- Menjaga desain Figma tetap konsisten.
- Menghindari konflik kode antaranggota.
- Memudahkan integrasi pekerjaan 4 anggota.
- Menjaga UI/UX tetap konsisten.

---

## 1. PROJECT OVERVIEW

**Project:** SIM-MBG (Sistem Informasi Manajemen Makan Bergizi Gratis)

**Tech Stack:**
- HTML5
- CSS3
- JavaScript (Vanilla JS)

Untuk tahap frontend:
- Tidak menggunakan React, Vue, atau framework frontend lain.
- Tidak menggunakan Tailwind atau Bootstrap.
- Tidak menambahkan library UI tanpa persetujuan tim.
- Backend/Laravel belum menjadi bagian tahap ini.
- Gunakan mock data/local state untuk simulasi.

---

## 2. TEAM RESPONSIBILITY

### Member 1 — Public & Authentication

Pages:
- Landing Page
- Login
- Register

Tanggung jawab:
- Navbar public
- Hero section
- CTA
- Login form
- Register form
- Pemilihan role/sekolah jika ada pada desain
- Validasi form frontend

### Member 2 — Siswa

Pages:
- Dashboard Siswa
- Pilih Menu
- Riwayat
- Review

Tanggung jawab:
- Student dashboard
- Informasi menu
- Pemilihan paket makanan
- Status pilihan
- Riwayat pemilihan menu
- Review makanan

### Member 3 — SPPG

Pages:
- Dashboard SPPG
- Menu
- Pilihan Siswa
- Review

Tanggung jawab:
- SPPG dashboard
- Manajemen menu
- Informasi pilihan siswa
- Rekap pilihan makanan
- Review dari siswa

### Member 4 — Admin

Pages:
- Dashboard Admin
- Siswa
- Sekolah
- SPPG
- Pengguna

Tanggung jawab:
- Admin dashboard
- Data siswa
- Data sekolah
- Data SPPG
- Data pengguna
- Tabel/list data
- CRUD interface frontend

---

## 3. SHARED UI

Komponen berikut harus konsisten di seluruh website:
- Navbar
- Sidebar
- Button
- Input
- Select
- Search
- Card
- Table
- Modal
- Badge
- Alert
- Empty state
- Loading state
- Pagination
- Typography
- Spacing
- Border radius
- Shadow
- Color

Jika komponen sudah tersedia, gunakan kembali. Jangan membuat versi baru tanpa alasan.

---=

---

## 5. CSS RULES

### 5.1 Hindari inline CSS

Jangan:

```html
<div style="color: red;">
```

Gunakan class:

```html
<div class="alert alert-danger">
```

### 5.2 Gunakan class yang deskriptif

Gunakan:

```css
.dashboard-card {}
.menu-card {}
.student-table {}
.review-card {}
```

Hindari:

```css
.box1 {}
.box2 {}
.bagus {}
.test {}
```

### 5.3 Jangan menggunakan ID untuk styling

Gunakan class untuk styling. ID digunakan terutama untuk kebutuhan JavaScript.

### 5.4 Hindari selector terlalu spesifik

Jangan:

```css
.dashboard .container .content .card div span {}
```

Lebih baik:

```css
.card-title {}
```

---

## 6. CSS FILE STRUCTURE

```text
css/
├── reset.css
├── variables.css
├── global.css
├── components.css
├── layout.css
└── pages/
    ├── landing.css
    ├── auth.css
    ├── siswa.css
    ├── sppg.css
    └── admin.css
```

### `reset.css`
Reset/default browser style.

### `variables.css`
Design tokens seperti warna, radius, spacing, dan typography.

### `global.css`
Style umum seperti body, heading, link, dan utility class.

### `components.css`
Komponen reusable seperti button, card, input, modal, table, dan badge.

### `layout.css`
Navbar, sidebar, container, grid, flex layout, dan struktur halaman.

### `pages/`
Style yang benar-benar spesifik terhadap halaman.

---

## 7. HTML STRUCTURE

Gunakan semantic HTML jika memungkinkan:

```html
<header>
<nav>
<main>
<section>
<article>
<footer>
```

Hindari menggunakan `<div>` untuk semua hal.

Contoh:

```html
<main class="dashboard">
    <section class="dashboard-header">
        ...
    </section>

    <section class="dashboard-content">
        ...
    </section>
</main>
```

---

## 8. JAVASCRIPT RULES

Pisahkan JavaScript dari HTML.

Hindari:

```html
<button onclick="openMenu()">
```

Lebih baik:

```html
<button id="menuButton">
```

Kemudian:

```js
const menuButton = document.querySelector("#menuButton");

menuButton.addEventListener("click", openMenu);
```

Pastikan element tersedia sebelum digunakan:

```js
const menu = document.querySelector("#menu");

if (menu) {
    menu.addEventListener("click", openMenu);
}
```

---

## 9. JAVASCRIPT FILE STRUCTURE

```text
js/
├── main.js
├── components/
│   ├── navbar.js
│   ├── modal.js
│   └── notification.js
└── pages/
    ├── landing.js
    ├── auth.js
    ├── siswa.js
    ├── sppg.js
    └── admin.js
```

`main.js` untuk logic umum.

`components/` untuk logic reusable.

`pages/` untuk logic khusus halaman.

---

## 10. MOCK DATA

Karena backend belum dibuat, gunakan mock data yang konsisten.

Contoh:

```js
const students = [
    {
        id: 1,
        name: "Andi",
        nis: "123456",
        school: "SMA Negeri 1 Denpasar",
        class: "XI IPA 1"
    }
];
```

Struktur entity harus disepakati bersama.

### Siswa

```text
Siswa
├── ID
├── Nama
├── NIS
├── Sekolah
└── Kelas
```

### Menu

```text
Menu
├── ID
├── Nama Paket
├── Deskripsi
├── Tanggal
└── Status
```

### Review

```text
Review
├── ID
├── Siswa
├── Menu
├── Rating
├── Komentar
└── Tanggal
```

Jangan membuat struktur data yang berbeda untuk entity yang sama.

---

## 11. NAMING CONVENTION

### JavaScript

Gunakan `camelCase`:

```js
const selectedMenu = null;
const studentData = {};

function loadMenuData() {}
```

### CSS

Gunakan `kebab-case`:

```css
.menu-card {}
.menu-card-title {}
.menu-card-description {}
```

### File

Gunakan nama yang deskriptif:

```text
student-dashboard.html
menu-selection.html
review.html
```

Hindari:

```text
DashboardFinal.html
dashboard2.html
coba.html
test.html
baru.html
```

---

## 12. FOLDER STRUCTURE

```text
SIM-MBG/
│
├── index.html
│
├── pages/
│   ├── auth/
│   │   ├── login.html
│   │   └── register.html
│   │
│   ├── siswa/
│   │   ├── dashboard.html
│   │   ├── menu.html
│   │   ├── history.html
│   │   └── review.html
│   │
│   ├── sppg/
│   │   ├── dashboard.html
│   │   ├── menu.html
│   │   ├── students.html
│   │   └── review.html
│   │
│   └── admin/
│       ├── dashboard.html
│       ├── students.html
│       ├── schools.html
│       ├── sppg.html
│       └── users.html
│
├── css/
├── js/
├── assets/
│   ├── images/
│   ├── icons/
│   └── logo/
│
└── AGENTS.md
```

Perubahan struktur besar harus didiskusikan terlebih dahulu.

---

## 13. ASSET RULES

Semua asset disimpan di:

```text
assets/
├── images/
├── icons/
└── logo/
```

Gunakan nama file yang jelas:

```text
logo-sim-mbg.svg
hero-food.jpg
menu-paket-a.jpg
menu-paket-b.jpg
icon-dashboard.svg
icon-history.svg
```

Hindari:

```text
IMG_1234.png
Screenshot1.png
finalfinal.png
gambarbaru.png
```

Jangan menyimpan gambar di dalam folder halaman.

---

## 14. SVG ICON RULE

Gunakan SVG untuk icon dari desain.

Jangan mengganti icon dengan emoji kecuali memang bagian dari desain.

Jika icon sudah tersedia di:

```text
assets/icons/
```

gunakan asset tersebut daripada membuat ulang.

---

## 15. RESPONSIVE DESIGN

Minimal lakukan pengujian pada:
- Desktop 1440px
- Desktop 1920px
- Tablet
- Mobile

Hindari fixed width yang tidak diperlukan.

Jangan:

```css
width: 1200px;
```

Lebih baik:

```css
width: 100%;
max-width: 1200px;
margin: 0 auto;
```

---

## 16. FIGMA → CODE

Figma merupakan source of truth untuk visual design.

Perhatikan:
- Layout
- Spacing
- Typography
- Color
- Border radius
- Icon
- Component hierarchy

Namun jangan menerjemahkan semua ukuran Figma secara literal jika menyebabkan layout tidak responsive.

Prioritas:

1. Struktur/layout
2. Hierarki visual
3. Typography
4. Spacing
5. Color
6. Detail visual

---

## 17. REUSABLE COMPONENTS

Jika komponen muncul di lebih dari satu halaman, usahakan reusable.

Contoh:

```text
Navbar
Sidebar
Button
Input
Card
Modal
Table
Badge
```

Jangan membuat:

```text
student-button
sppg-button
admin-button
```

jika sebenarnya memiliki fungsi dan visual yang sama.

Gunakan:

```html
<button class="btn btn-primary">
```

---

## 18. NAVBAR & SIDEBAR

Navbar/sidebar harus memiliki struktur dan visual yang konsisten.

### Siswa

```text
Dashboard
Pilih Menu
Riwayat
Review
Profile
Logout
```

### SPPG

```text
Dashboard
Menu
Pilihan Siswa
Review
Profile
Logout
```

### Admin

```text
Dashboard
Siswa
Sekolah
SPPG
Pengguna
Profile
Logout
```

Perbedaan menu tidak berarti membuat desain navbar baru.

---

## 19. PAGE STATE

Halaman yang memiliki data harus mempertimbangkan:

### Normal State
Data tersedia.

### Empty State
Tidak ada data.

Contoh:

```text
Belum ada riwayat pemilihan menu.
```

### Error State
Terjadi masalah saat mengambil data.

### Loading State
Data sedang diproses.

Pada tahap frontend-only, state dapat disimulasikan menggunakan mock data.

---

## 20. FORM VALIDATION

Form minimal harus memiliki validasi frontend.

Contoh:
- Required field
- Format email
- Minimum password
- Confirm password
- NIS tidak kosong
- Sekolah harus dipilih

Pesan error harus jelas.

Jangan hanya:

```text
Error!
```

Gunakan:

```text
NIS wajib diisi.
```

---

## 21. GIT WORKFLOW

Setiap anggota WAJIB menggunakan branch sendiri.

Jangan langsung bekerja di `main`.

Branch utama:

```text
main
```

Contoh branch:

```text
feature/landing-auth
feature/siswa
feature/sppg
feature/admin
```

`main` hanya berisi versi yang sudah stabil.

---

## 22. COMMIT CONVENTION

Gunakan:

```text
type: description
```

Type yang digunakan:

```text
feat
fix
style
refactor
docs
chore
```

Contoh:

```text
feat: add student dashboard
feat: add menu selection page
style: update dashboard spacing
fix: fix mobile navbar
refactor: simplify menu rendering
docs: update project documentation
```

Hindari:

```text
update
fix
done
final
final2
fix lagi
punya saya
```

---

## 23. PULL REQUEST / MERGE

Sebelum merge ke `main`:

1. Pastikan halaman dapat dibuka.
2. Tidak terdapat error di console.
3. Tidak merusak halaman anggota lain.
4. Asset path benar.
5. Responsive.
6. Tidak ada file tidak diperlukan.
7. Sinkronkan branch dengan branch terbaru jika diperlukan.
8. Minimal satu anggota melakukan review.

Jangan merge langsung tanpa pengecekan.

---

## 24. BEFORE PUSH

Checklist:

```text
[ ] HTML tidak error
[ ] CSS tidak error
[ ] JavaScript tidak menghasilkan console error
[ ] Semua gambar/icon muncul
[ ] Semua link halaman bekerja
[ ] Tidak ada file test yang tertinggal
[ ] Tidak ada perubahan anggota lain yang tidak disengaja
[ ] Responsive sudah dicek
```

---

## 25. SHARED FILE WARNING

File yang berpotensi sering mengalami conflict:

```text
variables.css
global.css
components.css
layout.css
main.js
```

Jika ingin mengubah file tersebut secara signifikan:

1. Informasikan di group.
2. Jelaskan perubahan.
3. Pastikan tidak sedang dikerjakan anggota lain.
4. Informasikan setelah selesai.

Hindari dua orang mengubah bagian yang sama secara bersamaan.

---

## 26. DO NOT OVERWRITE OTHER MEMBER'S WORK

Jangan menjalankan:

```bash
git checkout .
git reset --hard
```

atau perintah lain yang berpotensi menghapus pekerjaan anggota lain tanpa koordinasi.

Jika terjadi conflict, jangan asal memilih `ours` atau `theirs`. Periksa perubahan terlebih dahulu.

---

## 27. PAGE OWNERSHIP

Setiap anggota bertanggung jawab terhadap halaman yang telah ditentukan.

Shared component merupakan tanggung jawab bersama.

Contoh Member 2 boleh mengubah:

```text
pages/siswa/
css/pages/siswa.css
js/pages/siswa/
```

Tetapi perubahan terhadap:

```text
components.css
layout.css
variables.css
```

harus dikomunikasikan.

---

## 28. DO NOT DUPLICATE CODE

Jika menemukan kode yang sama di beberapa halaman, jangan membuat implementasi berbeda tanpa alasan.

Jika visual dan fungsi dasarnya sama, gunakan satu component/style.

Jangan membuat:

```html
<div class="custom-card-sppg">...</div>
<div class="custom-card-admin">...</div>
```

jika sebenarnya struktur dan fungsinya sama.

Gunakan:

```html
<div class="custom-card">...</div>
```

---

## 29. ACCESSIBILITY

Minimal perhatikan:
- Gunakan `<label>` pada input.
- Gunakan `alt` pada gambar.
- Button harus memiliki fungsi yang jelas.
- Jangan hanya menggunakan warna untuk menunjukkan status.
- Pastikan teks memiliki kontras yang cukup.
- Gunakan heading secara berurutan.

Contoh:

```html
<label for="nis">NIS</label>
<input id="nis" type="text">
```

---

## 30. SECURITY AWARENESS

Meskipun masih frontend:

Jangan menyimpan:
- Password asli
- Token asli
- API key
- Credential
- Data sensitif

di source code atau repository.

Login pada tahap ini hanya berupa simulasi. Authentication sebenarnya akan dilakukan setelah backend tersedia.

---

## 31. NO PREMATURE BACKEND

Jangan membuat:

```text
Laravel
PHP
API
MySQL
```

untuk tahap frontend ini kecuali telah disepakati seluruh tim.

Gunakan mock data dan local state.

---

## 32. NO RANDOM LIBRARIES

Jangan menambahkan dependency/library hanya untuk menyelesaikan masalah kecil.

Prioritaskan:

```text
HTML
CSS
JavaScript
```

Jika membutuhkan library baru, diskusikan terlebih dahulu.

---

## 33. DEFINITION OF DONE

Sebuah halaman dianggap SELESAI apabila:

```text
[ ] Sesuai desain Figma
[ ] Struktur HTML rapi
[ ] CSS terorganisir
[ ] JavaScript berfungsi
[ ] Tidak ada console error
[ ] Asset tampil dengan benar
[ ] Link/navigation berfungsi
[ ] Responsive
[ ] Empty state tersedia jika diperlukan
[ ] Form validation tersedia jika diperlukan
[ ] Tidak merusak halaman lain
[ ] Sudah di-commit
[ ] Sudah di-review
```

---

## 34. DAILY TEAM CHECKPOINT

Setiap hari lakukan checkpoint singkat.

Masing-masing anggota menjawab:

### DONE
Apa yang sudah selesai?

### DOING
Apa yang sedang dikerjakan?

### BLOCKED
Apa yang sedang menghambat?

Contoh:

```text
Member 2

DONE:
- Dashboard siswa
- Card menu

DOING:
- Halaman pilih menu

BLOCKED:
- Menunggu asset icon dari desain
```

Tujuannya adalah mendeteksi blocker lebih awal.

---

## 35. PRIORITY

Jika waktu terbatas, prioritaskan:

### Priority 1 — Core Flow

```text
Landing
↓
Login
↓
Dashboard
↓
Pilih Menu
↓
Review
```

### Priority 2 — SPPG

```text
SPPG Dashboard
↓
Menu
↓
Pilihan Siswa
↓
Review
```

### Priority 3 — Admin

```text
Dashboard
↓
Siswa
↓
Sekolah
↓
SPPG
↓
Pengguna
```

### Priority 4 — Polish

- Animation
- Hover effect
- Micro interaction
- Fine spacing
- Additional responsive improvements

Jangan mengorbankan core functionality hanya untuk mengejar animasi atau detail visual.

---

## 36. COMMUNICATION RULE

Jika menemukan masalah yang mempengaruhi anggota lain, jangan langsung mengubah kode mereka.

Diskusikan terlebih dahulu.

Contoh:

> "Navbar di halaman siswa membutuhkan perubahan struktur agar responsive. Apakah boleh saya ubah component navbar?"

Lebih baik koordinasi 2 menit daripada memperbaiki conflict selama 30 menit.

---

## 37. FINAL INTEGRATION

Sebelum presentasi/demo, semua halaman harus dapat diakses dari satu project.

Target flow:

```text
Landing
   │
   ├── Login
   │     │
   │     ├── Siswa
   │     │     ├── Dashboard
   │     │     ├── Pilih Menu
   │     │     ├── Riwayat
   │     │     └── Review
   │     │
   │     ├── SPPG
   │     │     ├── Dashboard
   │     │     ├── Menu
   │     │     ├── Pilihan Siswa
   │     │     └── Review
   │     │
   │     └── Admin
   │           ├── Dashboard
   │           ├── Siswa
   │           ├── Sekolah
   │           ├── SPPG
   │           └── Pengguna
   │
   └── Register
```

Semua navigasi utama harus dapat digunakan tanpa membuka file HTML secara manual.

---

## 38. GOLDEN RULE

Sebelum membuat sesuatu, tanyakan:

1. Apakah komponen ini sudah ada?
2. Apakah style ini sudah ada?
3. Apakah data structure-nya sudah disepakati?
4. Apakah perubahan ini mempengaruhi anggota lain?
5. Apakah implementasi ini sesuai Figma?
6. Apakah implementasi ini responsive?

Jika jawabannya jelas, baru implementasikan.

---

# FINAL PRINCIPLE

**CONSISTENCY > SPEED > COMPLEXITY**

Lebih baik memiliki website dengan fitur lebih sedikit tetapi konsisten, stabil, dan terintegrasi daripada banyak fitur yang memiliki style dan struktur berbeda-beda.
