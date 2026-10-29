# DESIGN_SYSTEM.md — SIM-MBG
 
Sistem Informasi Manajemen Makan Bergizi Gratis (terintegrasi dengan **Laravel 13**, Blade Views di `resources/views/`, dan stylesheet/script modular di `public/css/` serta `public/js/`).
Dokumen ini adalah hasil audit file Figma sebagai acuan utama (*source of truth*) sistem desain. Tidak ada nilai desain yang dikarang.
 
- File Figma: `AyeVi6PzuIt0uSUzJeoZZ6` (SIM-MBG)
- Tanggal audit: 9 Oktober 2026
- Status implementasi: Terintegrasi ke dalam aplikasi Laravel (lihat `README.md` dan `AGENTS.md` untuk pemetaan Blade view & rute).
## 0. Cara membaca dokumen
 
Setiap fakta diberi label:
 
| Label | Arti |
|---|---|
| **[V]** | Terverifikasi: dibaca langsung dari file Figma (properti node, variable, atau screenshot). |
| **[I]** | Inferensi: kesimpulan wajar dari data terverifikasi, tetapi tidak tertulis eksplisit di Figma. |
| **[?]** | Belum diketahui: tidak ada di file atau tidak bisa diakses. Tim perlu memutuskan. |
 
Bagian "Keputusan terbuka" (bagian 12) berisi hal yang harus disepakati tim sebelum coding supaya CSS tidak bercabang.
 
---
 
## 1. Cakupan audit dan progres
 
### 1.1 Akses
 
| Item | Status |
|---|---|
| Halaman `ASSET` (0:1), 18 node | Terbaca penuh [V] |
| Halaman `frame` (13:13), 19 node level atas (18 frame desain + LOGO + catatan) | Semua terbaca [V] |
| Frame/halaman yang tidak dapat diakses | **Tidak ada** yang ditolak akses [V] |
| Variable lokal | 1 koleksi, 5 variable warna [V] |
| Text style / paint style / effect style lokal | **0 / 0 / 0** [V] |
 
### 1.2 Keterbatasan (dilaporkan eksplisit)
 
1. Dump metadata halaman `frame` terpotong oleh batas ukuran tool (sekitar 638 ribu karakter). Karena itu audit dilakukan bertahap dengan skrip baca-saja per frame (statistik fill, tipografi, radius, shadow, spacing, komponen, variable binding, dan semua teks). Tidak ada perubahan yang ditulis ke file Figma.
2. Screenshot visual hanya diambil untuk 4 dari 18 frame: `landing page`, `Pilih Menu Siswa`, `ADMIN - DASHBOARD`, `SPPG - Dashboard`. Frame lain diaudit dari properti node dan teks, bukan dari tampilan visual. Detail visual frame itu berlabel [V] hanya jika berasal dari properti node.
3. Struktur hierarki detail `Profil Saya` (107:6395) tidak sempat terbaca sebelum pemotongan; yang terverifikasi untuknya adalah statistik gaya dan seluruh teks.
4. Nama variable untuk `ADMIN - DASHBOARD` berhasil dibaca lewat `get_variable_defs`. Frame lain hanya memuat 3 variable lokal (`bg`, `primary`, `sec`).
5. Interaksi prototype hanya dihitung dari properti `reactions`. Durasi/easing transisi tidak dibaca [?].
6. State hover/pressed/focus/disabled: tidak ditemukan sebagai varian komponen di file [?].
---
 
## 2. Peta file
 
### 2.1 Halaman `ASSET` (0:1)
 
| Node ID | Nama | Tipe | Ukuran | Isi |
|---|---|---|---|---|
| 5:22 | color pallete | Group | 1600×1193 | Swatch palet (gambar) + label PRIMARY/SECONDARY/ACCENT/BACKGROUND/TEXT [V] |
| 5:23–5:29 | PLUS JAKARTA SANS | Text ×7 | 128px | Sampel bobot ExtraBold, Bold, SemiBold, Medium, Regular, Light, ExtraLight [V] |
| 5:17 | Frame 1 | Frame | 783×100 | Sampel heading "LOREM IPSUM…" ExtraBold 48, warna #39393A [V] |
| 34:552 | Frame 2 | Component | 160×49 | Tombol primer "Get Started" [V] |
| 34:568 | Frame 5 | Component | 185×44 | Tombol sekunder "Hubungi Kami" + ikon WhatsApp [V] |
| 28:30 | Navigation-D | Component | 121×40 | Tombol/tab berlatar primary "LOREM" [V] |
| 34:619 | Frame 6 | Component | 201×60 | Chip menu makanan (foto + nama) [V] |
| 67:806 / 67:807 | Frame 8 / Frame 9 | Component / Instance | 155×63 | Item putih "LOREM" ExtraBold 12 [V]; fungsi sebenarnya [?] |
| 5:20 | Frame 3 | Frame | 176×88 | Tombol merah "Delete" [V]; draf, bukan bagian sistem (lihat bagian 11) |
| 33:337 | LOGO | Component | 1064×970 | Logo (dipakai 78×72 di navbar dan 80×80 container) [V] |
| 12:12 | (teks sitemap) | Text | — | Sitemap, lihat bagian 3 [V] |
 
### 2.2 Halaman `frame` (13:13)
 
**Entrance (ekstrak dari label bagian "entrance", 108:3182)**
 
| Node ID | Nama frame | Ukuran |
|---|---|---|
| 27:2 | landing page | 1440×1024 |
| 34:655 | login | 1440×1024 |
| 190:4077 | login (judul "SPPG") | 1440×1024 |
| 46:427 | register (Sekolah/NIS/Kelas, tombol "Lanjut") | 1440×1024 |
| 69:808 | register (Nama Pengguna/Email/Kata Sandi/Konfirmasi) | 1440×1024 |
 
**Siswa (label 108:3183)**
 
| Node ID | Nama frame | Ukuran |
|---|---|---|
| 97:1000 | Dashbord Siswa | 1440×1073 |
| 101:3408 | Pilih Menu Siswa | 1440×1600 |
| 101:4971 | Riwayat & Ulasan | 1440×1344 |
| 107:6395 | Profil Saya | 1440×1344 |
 
**Admin (label 108:3184)**
 
| Node ID | Nama frame | Ukuran |
|---|---|---|
| 130:3456 | ADMIN - DASHBOARD | 1440×1704 |
| 130:3865 | ADMIN - SISWA | 1440×1170 |
| 140:4557 | ADMIN - SEKOLAH | 1440×1441 |
| 143:5035 | ADMIN - SPPG | 1440×950 |
| 147:5503 | ADMIN - Pengguna | 1440×950 |
 
**SPPG (label 152:3128)**
 
| Node ID | Nama frame | Ukuran |
|---|---|---|
| 166:5032 | SPPG - Dashboard | **1280**×1798 |
| 166:5500 | SPPG - Menu | **1280**×1903 |
| 166:4402 | SPPG - Pilihan Siswa | **1280**×3178 |
| 166:6092 | SPPG - Review | **1280**×1178 |
 
**Node non-desain di halaman yang sama [V]:** `LOGO` instance (67:609), catatan "POP UP" (173:2727), catatan desainer (173:2728), "REAL MBG" (198:3700), dan 6 persegi bergambar `image 6`–`image 11` (198:3683, 3686, 3689, 3692, 3695, 3698) berukuran sangat besar (1600–3700 px) yang tampaknya referensi foto [I].
 
Catatan desainer di 173:2728 [V]: *"Mungkin bisa gunakan dropdown isi menu dgn tombol lihat detail. Keseimbangan gizi perlu dibuat lebih friendly."* Ini masukan yang belum terselesaikan (Pilih Menu Siswa), bukan keputusan final.
 
---
 
## 3. Peran pengguna dan struktur navigasi
 
Sitemap (teks di ASSET 12:12) [V]:
 
```
SIM-MBG
├── Login
├── Register
│   ├── Siswa:  Dashboard, Pilih Menu, Riwayat, Review
│   ├── SPPG:   Dashboard, Menu, Pilihan Siswa, Review
│   └── Admin:  Dashboard, Siswa, Sekolah, SPPG, Pengguna
```
 
| Peran | Frame di Figma | Navigasi yang tergambar | Status |
|---|---|---|---|
| Publik / tamu | landing page | Header: logo + tombol "Masuk" | [V] |
| Siswa | 4 frame Siswa | Navbar atas: Dashbord, Pilih Menu, Riwayat & Ulasan, Profil Saya; chip pengguna "Alya MBG" | [V] |
| SPPG | 4 frame SPPG | Header atas: Beranda, Menu, Pilihan Pengguna, Review; pengguna "Pgw Alya / Pegawai SPPG" | [V] (dari screenshot Dashboard) |
| Admin | 5 frame Admin | Navbar atas: Dashboard, Siswa, Sekolah, SPPG, Pengguna; pengguna "Atmind" | [V] (dari screenshot Dashboard) |
 
Catatan:
- Halaman login hanya ada untuk dua judul: "Masuk" (34:655) dan "SPPG" (190:4077). **Login Admin tidak ada** [V]. Apakah satu login dipakai bersama atau ada login Admin terpisah: [?].
- Yang pertama diasumsikan login siswa karena judulnya generik "Masuk" [I].
- Register hanya memuat field siswa (Sekolah, NIS, Kelas). Sitemap menaruh Register sebelum cabang peran, tetapi tidak ada register untuk SPPG/Admin [V]. Alur 2 langkah register (46:427 lalu 69:808) adalah inferensi dari urutan posisi canvas dan teks tombol "Lanjut" [I].
- Pengguna di sampel data admin memuat peran "Admin", "Petugas SPPG", "Siswa" [V].
---
 
## 4. Design tokens
 
### 4.1 Token yang benar-benar terdefinisi di Figma [V]
 
Satu koleksi `Variable collection`, satu mode `Mode 1`, 5 variable warna:
 
| Nama variable di Figma | Hex (dihitung dari nilai RGB) | Label di palet ASSET |
|---|---|---|
| `bg` | `#FAFAF9` | BACKGROUND |
| `text` | `#39393A` | TEXT |
| `accent` | `#FF8552` | ACCENT |
| `primary` | `#297373` | PRIMARY |
| `sec` | `#85FFC7` | SECONDARY |
 
Hal yang **tidak** ada sebagai token [V]:
- Skala tipografi, spacing, radius, shadow, border: tidak ada variable maupun style. Semua nilai ditulis langsung di node.
- Hanya sebagian kecil fill terikat ke 5 token di atas (contoh: landing 31 dari 292 node berfill; student dashboard 21 dari 405). Sisanya hex literal.
### 4.2 Token impor dari luar (hanya ADMIN - DASHBOARD) [V]
 
`ADMIN - DASHBOARD` terikat ke puluhan variable yang tidak ada di koleksi lokal, bernama gaya Tailwind (`color/slate-900`, `text/sm`, `radius/lg`, `font/weight-bold`, dst.) dan gaya hasil ekspor (`sim-mbg-admintemaja.ai.studio/Ebony`, `.../Mystic`, dst.).
 
Artinya halaman itu berasal dari ekspor eksternal, kemungkinan Google AI Studio [I], bukan dari 5 token tim. Nilai-nilai ini adalah acuan **kandidat** untuk skala admin, bukan token yang disepakati.
 
| Kelompok | Nilai yang terbaca [V] |
|---|---|
| Radius | md = 6, lg = 8, xl = 12, 2xl = 16; "corner radius/33554400" (pill) |
| Ukuran teks | 10, 11, 14 (sm), 18 (lg), 20 (xl), 24 (2xl) |
| Bobot | normal 400, bold 700 |
| Line-height | 14.67, 16.5, 17.5, 22.5, 22.75, 32 |
| Letter-spacing | −0.6, 0.6 |
| Stroke | 1, 1.17, 1.33, 2, 2.2 |
| Lebar | 280 (`width/280`), container/3xl = 768 |
| Slate | 50 `#F8FAFC`, 100 `#F1F5F9`, 200 `#E2E8F0`, 400 `#90A1B9`, 500 `#62748E`, 600 `#45556C`, 700 `#314158`, 800 `#1D293D`, 900 `#0F172B` |
| Emerald | 50 `#ECFDF5`, 100 `#D0FAE5`, 500 `#00BC7D`, 700 `#007A55` |
| Teal | 50 `#F0FDFA`, 100 `#CBFBF1`, 600 `#009689`, 700 `#00786F`, "cyan/26" `#0F766E` |
| Amber | 100 `#FEF3C6`, 500 `#FE9A00`, 700 `#BB4D00` |
| Biru / ungu | blue-50 `#EFF6FF`, blue-100 `#DBEAFE`, blue-700 `#1447E6`; purple-50 `#FAF5FF`, purple-100 `#F3E8FF` |
 
### 4.3 Nama token CSS (usulan penamaan saja, bukan nilai baru)
 
Nilainya hanya dari 4.1. Penamaan ini usulan tim [I]:
 
| Token Figma | Nama usulan |
|---|---|
| bg | `--color-bg` |
| text | `--color-text` |
| primary | `--color-primary` |
| sec | `--color-secondary` |
| accent | `--color-accent` |
 
Nama untuk skala tipografi/spacing/radius belum ada karena nilainya belum disepakati (bagian 12).
 
---
 
## 5. Warna
 
### 5.1 Warna inti (token) dan penggunaan terverifikasi
 
| Warna | Pemakaian yang terlihat | Label |
|---|---|---|
| `#297373` primary | Tombol utama, nav aktif, ikon KPI admin, badge, tab aktif "Rabu" di Pilih Menu | [V] |
| `#FAFAF9` bg | Latar halaman, teks pada tombol primary | [V] |
| `#39393A` text | Teks di komponen "Hubungi Kami" dan landing | [V] |
| `#FF8552` accent | Tombol "Lihat" admin, ikon langkah aktif landing, garis fokus | [V] |
| `#85FFC7` sec | Ellipse latar landing (opacity 0.49), badge sekunder di Pilih Menu | [V] |
 
Ellipse latar landing: `#85FFC7` @ 0.49 dan `#FF8552` @ 0.18, dengan layer blur 1000 [V] (kesesuaian ke ellipse 108:3788/108:3789 adalah [I]).
 
### 5.2 Warna per domain (tidak seragam; lihat bagian 11)
 
**Landing + Auth**
`#297373`, `#FAFAF9`, `#FFFFFF`, teks `#39393A`, `#000000`, tautan `#001DAE` (hanya di login/register, di luar palet) [V].
 
**Siswa** (sebagian besar hex literal, bukan token) [V]
- Permukaan: `#FFFFFF`, `#EFF4FF` (paling sering), `#E5EEFF`, `#DCE9FF`, `#C9E6FF`
- Teks: `#0B1C30` (kuat), `#3E4A3D` (sekunder), `#297373` (aksen)
- Garis: `#BDCABA` (1px); input Profil `#767676`
- Amber (cut-off/peringatan): `#855300`, `#FFDDB8`, `#FEA619`, `#653E00`, `#2A1700`
- Hijau (aman/sukses): `#7FFC97`, `#006B2C`, `#005320`, `#002109`
- Merah (Profil): `#FFDAD6`, `#93000A`
**Admin** (skala Tailwind, lihat 4.2) [V]
Teks `#0F172B`, `#45556C`, `#62748E`, `#90A1B9`; garis `#E2E8F0`, `#F1F5F9`; badge sukses `#ECFDF5` / `#007A55` / `#A4F4CF`; aksen tombol "Lihat" `#FF8552`.
 
**SPPG Dashboard / Menu / Pilihan Siswa** [V]
Teks `#121C2A`, `#6B7280`; primer gelap `#00594D`; mint `#96F6C7`, `#006C4A`; permukaan `#EFF3FF`, `#E6EEFF`, `#DEE9FD`, `#F9F9FF`; peach `#FFDBCC`, `#883400`, `#A84B19`, `#7B2F00`.
 
**SPPG Review** (variasi ketiga) [V]
`#1C7365`, `#F3F4F6`, `#F9FAFB`, teks `#374151`, `#6B7280`, mint `#8CEBBD`, oranye `#FC8B54`, garis `#E5E7EB`.
 
### 5.3 Rasio kontras (dihitung WCAG, pasangan dari file)
 
| Teks / Latar | Rasio | Hasil (AA teks normal 4.5) |
|---|---|---|
| `#FAFAF9` di `#297373` | 5.30 | Lulus |
| `#39393A` di `#FAFAF9` | 11.05 | Lulus |
| `#39393A` di `#FF8552` | 4.80 | Lulus |
| `#FFFFFF` di `#FF8552` | 2.41 | **Gagal** |
| `#39393A` di `#85FFC7` | 9.41 | Lulus |
| `#FFFFFF` di `#85FFC7` | 1.23 | **Gagal** |
| `#62748E` di `#FFFFFF` | 4.76 | Lulus (tipis) |
| `#90A1B9` di `#FFFFFF` | 2.63 | **Gagal** (dipakai 17× sebagai teks admin) |
| `#6B7280` di `#FFFFFF` | 4.83 | Lulus |
| `#3E4A3D` di `#EFF4FF` | 8.46 | Lulus |
| `#855300` di `#FFDDB8` | 5.04 | Lulus |
| `#FFFFFF` di `#0F766E` | 5.47 | Lulus |
| `#FFFFFF` di `#00594D` | 8.28 | Lulus |
| `#001DAE` di `#FAFAF9` | 11.25 | Lulus |
 
Teks putih di atas `accent` atau `sec` tidak boleh dipakai. Pasangan yang gagal hanya angka; apakah `#90A1B9` di admin dipakai sebagai placeholder/penanda non-esensial: [?].
 
---
 
## 6. Tipografi
 
### 6.1 Font
 
- Keluarga utama: **Plus Jakarta Sans** [V]. Bobot tersampel di ASSET: ExtraBold, Bold, SemiBold, Medium, Regular, Light, ExtraLight. Bobot yang terlihat dipakai di frame: Regular, Medium, SemiBold, Bold, ExtraBold, dan Italic (ulasan) [V]. Light/ExtraLight belum terlihat dipakai [?].
- Font di luar sistem (lihat bagian 11): **Segoe UI / Segoe UI Semibold** (ADMIN - SISWA, 4 teks) dan **Poppins SemiBold 32** (komponen "Delete" di ASSET) [V].
### 6.2 Ukuran yang muncul (union seluruh file) [V]
 
10, 11, 12, 13, 14, 16, 18, 20, 24, 28, 30, 32, 36, dan 82 (hanya judul hero landing). Tidak ada text style Figma.
 
### 6.3 Pola per domain (yang paling sering)
 
| Domain | Peran | Spesifikasi |
|---|---|---|
| Landing | Hero "SIM-MBG" | ExtraBold 82 / lh 106% [V] |
| Landing | Judul seksi "Alur Pemesanan" | ExtraBold 36 / lh 106% [V] |
| Landing | Tagline | ExtraBold 24; paragraf Regular 24 [V] |
| Landing | Label chip menu | Bold 20 / lh 106% [V] |
| Auth | Judul kartu (Masuk/Daftar/SPPG) | Bold 32 / lh 20px / ls −0.4 [V] |
| Auth | Label field | Bold 20 / lh 106% [V] |
| Siswa | Sapaan H1 | Bold 28 / lh 36px / ls −0.7 (Dashboard) [V] |
| Siswa | Judul seksi | Bold 20 / lh 28px / ls −0.2 [V] |
| Siswa | Link navbar | Bold 24 / lh 24px [V] |
| Siswa | Judul kartu | Bold 18 / lh 26px; Bold 16 / lh 24px [V] |
| Siswa | Body | Regular 14 / lh 20px; Regular 16 / lh 24px [V] |
| Siswa | Label kecil kapital | Bold 11 / lh 14px / ls 0.44 [V] |
| Siswa | Badge / pil | SemiBold atau Bold 12 / lh 16px / ls 0.24 [V] |
| Admin | Judul halaman | Bold 36 / lh 36px / ls −0.75 [V] |
| Admin | Angka KPI | Bold 24 / lh 32px / ls −0.6 [V] |
| Admin | Body / sel tabel | Bold & Regular 14 / lh 20px; 12 / lh 16px [V] |
| Admin | Header tabel | Bold 12 / lh 16px / ls 0.6 [V] |
| SPPG | Judul halaman / KPI | ExtraBold 32 / lh 40px / ls −0.64 s.d. −0.8 [V] |
| SPPG | Body | Regular 13 / lh 20px [V] |
| SPPG | Label | Medium/SemiBold 11 / lh 14px / ls 0.33 [V] |
| SPPG Review | Angka KPI | ExtraBold 30 / lh 36px [V] |
 
Catatan desain tipografi [V]: line-height 20px untuk font 32 (judul auth, brand SIM-MBG di navbar) lebih kecil dari ukuran font; begitu juga Regular 20 / lh 17.5px dan Regular 14 / lh 17.5px. Ini kemungkinan artefak, bukan keputusan [I].
 
Ukuran navbar: teks link 24px terlalu besar dibanding semua teks UI lain (terbesar kedua setelah judul); admin dan SPPG memakai skala yang lebih kecil. [V] untuk nilainya, kesengajaan [?].
 
---
 
## 7. Spacing, radius, shadow, border
 
### 7.1 Spacing (auto-layout) [V]
 
| Jenis | Nilai yang berulang |
|---|---|
| Gap | 2, 4, 6, 8, 10, 12, 16, 24 |
| Padding tombol | 12 / 24 (Frame 2, Frame 5); 20 / 32 (Navigation-D); 8 / 16 (chip menu) |
| Padding kartu | 16 semua sisi; 20 semua sisi (admin, SPPG); 24 semua sisi (kartu Siswa) |
| Padding badge | 2/8, 4/8, 4/10, 4/12 |
 
Banyak nilai pecahan seperti 3.8 / 11.8, 17.5, 20.4, 20.8, 21.59, 3.99 muncul di admin dan SPPG [V]. Itu hasil ekspor; bukan skala spasi [I].
 
### 7.2 Radius [V]
 
| Nilai | Dipakai untuk |
|---|---|
| 4 | Badge kecil, tag (admin SPPG, Riwayat) |
| 6 | Chip menu landing, tombol kecil |
| 8 | Tombol, input, sel (nilai paling umum lintas domain) |
| 12 | Kartu (Siswa, admin) |
| 16 | Kartu besar admin/SPPG |
| 9999 | Pil / badge |
| Aneh: 33554400, 26843500 | Pil di admin (sama efeknya dengan 9999; artefak ekspor) [I] |
| Aneh: 17, 22, 90, 102, 14, 2.5 | 17: kartu auth; 22/90/102: dekorasi landing; 14: kartu admin SPPG; 2.5: checkbox Profil [V] untuk nilai, [I] untuk fungsi |
 
### 7.3 Shadow / efek [V]
 
| Efek | Nilai | Dipakai di |
|---|---|---|
| Shadow kartu tipis | 0 1 2 0, `#000` @ 5% | Kartu Siswa (≥8×), SPPG (≥15×) |
| Shadow kartu dalam | 0 2 4 −2, @10% dan 0 4 6 −1, @10% | Kartu Siswa |
| Shadow kartu admin | 0 1 3 0 @3%/@10%; 0 1 2 −1 @10% | Admin |
| Shadow tombol chip | 0 0 6.3, @43% | Chip menu landing (10×) |
| Shadow tombol sekunder | 0 0 4, @25% | "Hubungi Kami" (komponen) |
| Shadow kartu auth | 0 0 21.6, @63% | Kartu login/register (1×) |
| Glow input | 0 0 3.8 spread 1, `#297373` @44% | Setiap field login/register |
| Glow primary | 0 −8 24.3 spread 19, `#297373` @22% | Tepat 1× di hampir setiap frame Siswa/Admin; kemungkinan bar navbar [I] |
| Blur latar | Background blur 12, 8, 4 | Badge di atas foto kartu menu, panel semi-transparan |
| Layer blur 1000 | | 2 ellipse latar landing |
 
### 7.4 Border [V]
 
- Siswa: `#BDCABA` 1px (jarang, 1× per frame).
- Admin: `#E2E8F0` 1px (kartu, tabel), `#F1F5F9` (pemisah baris), `#FF8552` 1px (tombol Lihat), warna lain per badge (`#A4F4CF`, `#CBFBF1`).
- SPPG: `#EFF3FF` (baris Pilihan Siswa), `#E5E7EB` (Review), `#00594D` (1×).
- Input Profil `#767676` 1px.
---
 
## 8. Layout
 
| Aspek | Nilai | Label |
|---|---|---|
| Lebar frame desktop (landing, auth, Siswa, Admin) | 1440 | [V] |
| Lebar frame SPPG | 1280 | [V] |
| Tinggi navbar/header (landing, Siswa, Admin) | 103 | [V] |
| Tinggi header SPPG | 64 | [V] |
| Grid layout Figma | Landing + login: 12 kolom, lebar kolom 72, gutter 24, rata tengah. Admin Dashboard + Siswa: 5 kolom, offset 112, gutter 24, stretch. Frame Siswa & SPPG: tanpa grid | [V] |
| Container konten Siswa | x=53, lebar 1335 (margin kiri/kanan ≈ 53) | [V] |
| Container konten Admin | x=112, lebar 1216 | [V] |
| Container konten SPPG | padding 32, lebar 1216 | [V] |
| Kartu auth | 600 lebar, di x=420 (tengah); tinggi 804 (login, register langkah 2) atau 536 (register langkah 1); baris field 568 lebar (padding 16), input 568×36 | [V] |
| Breakpoint responsif | Hanya desktop yang digambar. Mobile/tablet tidak ada. | [V] untuk absen; perilaku responsif [?] |
 
Struktur navbar Siswa/Admin [V]: logo 80×80 (container 263×103, x=54) + judul "SIM-MBG" 32; blok Nav 717 lebar (x=407) dengan link tinggi 49 di y=27; kanan chip pengguna 172×63 (avatar 49).
 
---
 
## 9. Komponen berulang
 
### 9.1 Komponen resmi di ASSET [V]
 
| Komponen (nama di Figma) | Fungsi | Spesifikasi |
|---|---|---|
| Frame 2 (34:552) | Tombol primer | 160×49, auto-layout horizontal, padding 12/24, gap 10, radius 8, fill `primary`; label Regular 20, warna `bg` |
| Frame 5 (34:568) | Tombol sekunder + ikon WhatsApp | 185×44, padding 12/24, radius 8, fill `bg`, shadow 0 0 4 @25%; label Regular 16 warna `text`; ikon 19×19 |
| Navigation-D (28:30) | Tab/tombol nav | 121×40, padding 20/32, radius 8, fill `primary`; label SemiBold 16, warna `bg` |
| Frame 6 (34:619) | Chip menu makanan (landing, 10 instance) | 201×60, padding 8/16, gap 11, radius 6, fill `primary`, shadow 0 0 6.3 @43%; foto bulat 40 (radius 22) + gambar 92×52 + nama Bold 20 putih |
| Frame 8 / Frame 9 (67:806 / 67:807) | Item putih | 155×63, padding 24/56, fill #FFFFFF, "LOREM" ExtraBold 12 hitam. Fungsi [?] |
| LOGO (33:337) | Logo | Dipakai di setiap halaman; ukuran pakai 78×72 di dalam bingkai 80×80 |
 
Semua komponen bernama generik ("Frame 2"). Tidak ada deskripsi komponen dan tidak ada varian state [V].
 
### 9.2 Pola berulang yang BUKAN komponen di Figma [V]
 
Sama-sama diulang dengan frame biasa (bukan instance), jadi harus dijadikan komponen CSS/HTML oleh tim:
 
| Pola | Muncul di | Catatan |
|---|---|---|
| Navbar Siswa | 4 frame Siswa | Duplikasi frame; variasi lebar link lihat bagian 11 |
| Navbar Admin | 5 frame Admin | Sama |
| Header SPPG | 4 frame SPPG | Gaya berbeda (64 tinggi, nav kecil) |
| Kartu auth + baris field | 4 frame auth | Baris "row" 568 lebar |
| Kartu KPI | Admin (289×142, 4 per baris), SPPG (4 kolom, tinggi 152), Siswa (434×88 ×3), Riwayat (pita 1335×206) | Bentuk berbeda per domain |
| Badge/pil status | Semua domain | Warna beda per domain |
| Tabel data | Admin ×4, SPPG Dashboard & Pilihan Siswa | Kolom NO, nama, status, AKSI; paginasi "Menampilkan 1–5 dari 68 sekolah" |
| Kartu menu Paket A/B/C | Pilih Menu Siswa | 429×636; header foto 224; badge status; macro pill (kkal, Protein, Serat); detail isi piring; kotak alergi; tombol aksi 381×40 |
| Tab hari | Pilih Menu Siswa | 5 tombol: Senin (Past), Selasa (Today), Rabu (Active Selected), Kamis (Open), Jumat (Open); ukuran berbeda per state [V] |
| Chip filter + pencarian | Riwayat, admin, SPPG | Chip tinggi 36; input pencarian 224×32 (Riwayat) |
| Kartu riwayat | Riwayat & Ulasan | 1335×272, 3 item; badge status "Sudah Diambil (12.10 WITA)", bintang 5, kutipan ulasan, aksi Update & Delete |
 
### 9.3 State yang tergambar secara eksplisit [V]
 
- Tab hari: Past (abu), Today (badge "Hari Ini"), Active Selected (primary, badge "Aktif"), Open (badge "Buka"), serta status "Belum Dipilih" / "Menu Sudah Dipilih" / "Tersedia 3 Menu".
- Kartu menu: Terpilih (A), Peringatan Alergi (B), Belum Dipilih (C) lewat nama layer.
- Tombol "Menu Terpilih", "Ubah Menu".
- Status admin: Aktif (hijau), Proses (kuning).
- Status distribusi SPPG: Terkirim, Diproses, Evaluasi Diterima, Sedang Dikirim, Menunggu.
- Hover/pressed/focus/disabled/error input: tidak ada [?].
---
 
## 10. Prototype dan interaksi
 
Total hanya **2 reaction** di seluruh file [V]:
 
| Dari | Pemicu | Tujuan | Transisi |
|---|---|---|---|
| ADMIN - DASHBOARD, layer `Trigger` 78×34 (130:4547), hotspot transparan di atas link "Siswa" | On click | ADMIN - SISWA (130:3865), Navigate | Push |
| ADMIN - SISWA, layer `Trigger` 136×34 (130:4549) | On click | ADMIN - DASHBOARD (130:3456), Navigate | Push |
 
Fakta lain:
- Tidak ada prototype untuk landing, auth, Siswa, SPPG, dan halaman admin lain [V].
- Durasi/easing transisi: [?].
- Alur yang diniatkan dapat dibaca dari nama layer, bukan dari prototype [I]: kartu Siswa menyebut "Create", "Update & Delete", "Actions (Update & Delete)"; ini menunjukkan kebutuhan CRUD pilihan menu dan ulasan.
- Alur Login → Dashboard per peran: [?] (tidak digambar).
---
 
## 11. Spesifikasi per halaman
 
Format: **nama frame (node ID)**, tujuan, struktur layout, komponen, catatan. Hanya detail visual dengan label [V] yang bisa dipakai langsung.
 
### 11.1 Entrance
 
**landing page (27:2)**, 1440×1024
- Tujuan [I]: halaman muka publik dengan ajakan "Mulai" dan penjelasan alur (Daftar → Pilih → Konfirmasi).
- Struktur [V]: header 1440×103 (logo + judul + tombol "Masuk" instance Frame 31 97×44). Hero kiri: badge "sistem manajemen gizi", judul "SIM-MBG" 82, tagline "Siap memilih menu hari ini? 👋", paragraf, dua tombol ("Hubungi Kami" instance Frame 5 185×44; "Mulai" Frame 2 hug 111×44). Hero kanan: "Alur Pemesanan" + stepper 3 langkah (ikon bars/notes/confirm, 56×56 masing-masing), carousel kartu (Frame 7/29 kecil, Frame 6/28 utama 422×253) dengan panah prev/next 31×37 dan caption "Tekan Menu Makanan". Dasar: strip "list-menu section" 1440×128 berisi 10 chip menu (Frame 6) yang meluber keluar frame (x −470 s.d. 1709).
- Latar [V]: 2 ellipse 1338×1336 blur.
- Catatan [V]: Frame 28 (108:3185) dan Frame 6 (34:578) menempati posisi dan ukuran yang sama (422×253 di 891.5,664.5): tumpang tindih; peran masing-masing [?] (kandidat state carousel). Strip chip diduga marquee [I] (frame meng-clip konten).
**login (34:655)**, **login SPPG (190:4077)**, 1440×1024
- Tujuan: autentikasi. Struktur [V]: kartu 600×804 di tengah; header (logo 80 + judul 32: "Masuk" / "SPPG"); baris Nama Pengguna, baris Kata Sandi + tautan "Lupa Kata Sandi?"; tombol (instance Frame 2, 109×49); tautan "Belum Ada Akun? Daftar Sekarang!".
- Kedua frame identik selain judul dan nama frame ganda "login" [V].
- Catatan [I]: tautan "Daftar Sekarang" ada di login SPPG padahal register hanya memuat field siswa.
**register langkah 1 (46:427)**, 1440×1024
- Struktur [V]: kartu 600×536; field Sekolah (dropdown, ikon `gridicons:dropdown` 24), NIS, Kelas; tombol "Lanjut" (106×49).
**register langkah 2 (69:808)**, 1440×1024
- Struktur [V]: kartu 600×804; field Nama Pengguna, Email, Kata Sandi, Konfirmasi Kata Sandi; tombol "Daftar" (108×49 [I] label); tautan "Sudah Punya Akun? Masuk Sekarang!".
- Catatan [V]: label dan input tidak seragam (lihat bagian 12).
### 11.2 Siswa
 
**Dashbord Siswa (97:1000)**, 1440×1073
- Tujuan [V dari teks]: ringkasan hari ini: jadwal pengambilan, menu hari ini, pengumuman SPPG, pemilihan menu besok.
- Struktur [V]: navbar; container 1335 dimulai y=153. Sapaan + tanggal + sekolah; dua kolom: kiri 768 lebar (kartu "Distribusi Makan Siang Hari Ini" dengan waktu 11:30–13:00 WITA dan lokasi, tombol status "Aktif: Belum Diambil" 61 tinggi; kartu "Pemberitahuan SPPG" + banner "Pemilihan Menu Rabu, 8 Okt" + link "Pilih Menu"); kanan 542 lebar (kartu "MENU hari INI" foto 208 tinggi + gradient + badge, nama paket, komposisi dengan chip, bar makro 3 segmen, 4 sel gizi, badge "Ramah Lingkungan"); bawah 3 kartu statistik 434×88 (gap 16).
- Komponen: navbar, kartu 12-radius, pil status, bar makro.
**Pilih Menu Siswa (101:3408)**, 1440×1600
- Tujuan [V]: memilih menu harian dengan peringatan alergi dan batas waktu.
- Struktur [V]: navbar; header + "Profil Alergi Siswa" (407×86); banner Cut-off (1335×138, hitung mundur "03:44:18", batas 20:00 WITA); tab hari ×5; judul "Daftar Pilihan Menu: Rabu, 8 Oktober 2026"; grid 3 kartu 429 lebar (gap 24); panel "Keseimbangan Gizi Pilihanmu Minggu Ini" (3 kartu: rata-rata kalori 625/650, asupan protein 29/30 g, kesesuaian alergi 100% Aman).
- Catatan [V]: Kartu C bernama "Belum Dipilih (Create Pilihan Baru)" tetapi CTA terbaca "Ubah Menu" (sama dengan B). Lihat bagian 12.
- Catatan desainer di file: lihat bagian 2.2.
**Riwayat & Ulasan (101:4971)**, 1440×1344
- Struktur [V]: navbar; pita ringkasan 1335×206 (Tingkat Kehadiran 100%, Ulasan Diserahkan 13, Rata-rata 4.8); chip filter Semua/Sudah Diulas/Belum Diulas + pencarian "Cari nama menu…" + "Urutkan: Terkini"; feed 3 kartu @272 (nomor, jadwal, status pengambilan, judul menu, pil makro, rating bintang, kutipan ulasan, aksi update/delete).
**Profil Saya (107:6395)**, 1440×1344
- Tujuan [V dari teks]: profil siswa dan pengaturan alergi/pantangan.
- Struktur terverifikasi hanya dari teks dan gaya: header profil (NIS, kelas, sekolah, tombol "Edit Profile"), seksi "Alergi & Pantangan Makanan" dengan 5 kategori (Kacang, Seafood, Telur, Laktosa, Vegetarian), catatan medis, pesan sukses, tombol "Batalkan Perubahan" dan "Perbarui Data Alergi". Posisi persis: [?].
### 11.3 Admin
 
Struktur umum [V]: navbar (Dashboard, Siswa, Sekolah, SPPG, Pengguna), container 1216 dimulai x=112, kartu putih berborder `#E2E8F0`.
 
| Frame | Isi terverifikasi |
|---|---|
| ADMIN - DASHBOARD (130:3456) | Sapaan; banner "Pembaruan Data Periodik" + "Periode Data Aktif"; 4 kartu KPI 289×142 (Total Siswa 12.482, Sekolah 68, SPPG 12, Admin & Staf 24, masing-masing dengan delta); "Aktivitas Terbaru" 5 baris; tabel "Data Ringkas Sekolah" 5 baris + paginasi; banner "Informasi Admin" + "Lihat Panduan Penggunaan". Dikerjakan dengan auto-layout dan variable eksternal. |
| ADMIN - SISWA (130:3865) | Judul 36; 3 kartu rekap (Total 12.482, Reguler 12.110 (97%), Menu Khusus 372 (3%)); toolbar pencarian + 3 filter + "Tambah Siswa"; tabel kolom NO/NISN/NAMA/SEKOLAH/KELAS/JENIS MENU/CATATAN ALERGI/STATUS/AKSI |
| ADMIN - SEKOLAH (140:4557) | Tabel NO/NAMA SEKOLAH/KABUPATEN-KOTA/TOTAL SISWA/SPPG/STATUS/AKSI + "Tambah Sekolah"; status Aktif/Proses |
| ADMIN - SPPG (143:5035) | Tabel NO/NAMA/KABUPATEN/ALAMAT/KAPASITAS (porsi/hari)/JUMLAH SEKOLAH/STATUS/AKSI; 2 filter + "Tambah SPPG" |
| ADMIN - Pengguna (147:5503) | Tabel NO/NAMA/USERNAME/ROLE/INSTANSI/STATUS/AKSI; filter Role dan Status; "Tambah Pengguna" |
 
### 11.4 SPPG (frame 1280)
 
| Frame | Isi terverifikasi |
|---|---|
| SPPG - Dashboard (166:5032) | Header 64 (nav: Beranda, Menu, Pilihan Pengguna, Review); sapaan + tanggal + filter periode + "Sinkron"; banner "PENGUMUMAN SPPG"; 4 kartu KPI 152 (Total Porsi 3.450, Sekolah 18, Menu 12, Evaluasi 4); grid 2 kolom: "Aktivitas Distribusi & Verifikasi" dan "Menu Hari Ini" (foto, komposisi, 3 sel gizi); tabel "Data Ringkasan Distribusi Sekolah" 5 baris; catatan; footer 68 |
| SPPG - Menu (166:5500) | Judul "Manajemen Siklus Menu Makan Bergizi Gratis (SPPG)"; "Export Rekap (.XLSX)" + "Tambah Menu Baru"; 4 KPI (24 menu, 615 kkal, 8 alergen khusus, 98.5%); tab Semua/Reguler/Alergi Khusus/Cadangan; pencarian; kartu menu per hari |
| SPPG - Pilihan Siswa (166:4402) | Judul "Preferensi & Pilihan Menu Siswa Penerima MBG"; "Cetak Label Kemasan" + "Input Preferensi Siswa"; 4 KPI (12.482 terdata, 11.230 reguler (90%), 1.252 diet khusus (10%), 68 sekolah); filter; daftar panjang pilihan siswa (3178 tinggi) |
| SPPG - Review (166:6092) | Judul "Review & Evaluasi Kepuasan Menu MBG"; "Unduh Laporan Audit Gizi"; 4 KPI (4.82, 98.4%, 96.1%, 342); filter Terbaru/Semua/Sangat Puas/Perlu Evaluasi; kartu umpan balik sekolah |
 
---
 
## 12. Temuan konsistensi
 
Setiap temuan diklasifikasikan: **Disengaja** (variasi yang masuk akal), **Kesalahan** (perlu diperbaiki), atau **Perlu keputusan**.
 
### 12.1 Kesalahan (verifikasi kuat)
 
| # | Temuan | Bukti | Sifat |
|---|---|---|---|
| E1 | Tipo "Dashbord" di navbar Pilih Menu, Riwayat, Profil, dan nama frame `Dashbord Siswa`; Dashboard Siswa sendiri menulis "Dashboard" di navbar | Teks node | Kesalahan [V] |
| E2 | Persona siswa tidak konsisten: sapaan "Selamat Pagi, **Satria**!" sementara chip pengguna "**Alya** MBG" | Teks node | Kesalahan data contoh [V] |
| E3 | Hari dan tanggal tidak cocok kalender 2026: "Selasa, 7 Oktober 2026" (7 Okt 2026 jatuh pada Rabu); tab hari "Senin, 6 Okt … Jumat, 10 Okt" (6 Okt 2026 = Selasa, 10 Okt = Sabtu); SPPG "Selasa, 15 Oktober 2026" (Kamis); Riwayat "Senin, 6 Oktober 2026" | Kalender dihitung | Kesalahan [V]. Pola hari cocok dengan tahun 2025 untuk 6/7 Okt [I] |
| E4 | Statistik Riwayat: "Ulasan Diserahkan 13 Kali, Tersisa 3" vs chip "Sudah Diulas 16, Belum Diulas 2" (total 18) | Teks node | Kesalahan data [V] |
| E5 | Zona waktu: Siswa/Admin memakai WITA; SPPG Dashboard memakai WIB (07:30 WIB, 09:12 WIB); SPPG Review "09.25 WITA". Konteks Bali = WITA | Teks node | Kesalahan [V] |
| E6 | Data tidak konsisten lintas peran: Siswa khusus/alergi 372 (admin, 3%) vs 1.252 (SPPG Pilihan Siswa, 10%); SPPG Dashboard "18 sekolah, SD & SMP" vs SPPG Pilihan Siswa "68 sekolah" vs admin 68 sekolah; "68" diberi label "Unit SPPG" padahal admin mencatat 12 SPPG | Teks node | Kesalahan data contoh [V] |
| E7 | Font di luar sistem: Segoe UI / Segoe UI Semibold (4 teks, ADMIN - SISWA) dan Poppins SemiBold 32 (komponen "Delete" di ASSET) | Properti font | Kesalahan [V] |
| E8 | Komponen "Delete" (5:20): merah `#FF0000`, Poppins 32, radius 12, tanpa token | Properti node | Draf/kesalahan [V]; belum ada tombol destruktif resmi |
| E9 | CTA Kartu C (status "Belum Dipilih (Create Pilihan Baru)") terbaca "Ubah Menu", sama dengan Kartu B (update) | Nama layer + screenshot | Kemungkinan kesalahan [I] |
| E10 | Nav SPPG berlabel "Pilihan Pengguna", sedangkan frame dan judul halaman adalah "Pilihan Siswa" | Teks node | Kemungkinan kesalahan [V] |
| E11 | Frame login dua kali bernama `login` dan register dua kali bernama `register` | Nama layer | Kesalahan penamaan [V] |
| E12 | Tautan "Daftar Sekarang" ada di login SPPG padahal tidak ada register SPPG | Teks node | Kemungkinan kesalahan [I] |
| E13 | Line-height lebih kecil dari ukuran font: judul auth Bold 32 / lh 20px; Regular 20 / lh 17.5px | Properti teks | Kemungkinan artefak [V] untuk nilai, [I] untuk sebabnya |
| E14 | Teks hitam murni `#000000` untuk label auth/landing, sedangkan token `text` = `#39393A`; tautan biru `#001DAE` di luar palet | Properti fill | Kesalahan [V] |
| E15 | Input auth: jarak label→input tidak seragam: login 34px, register langkah 1 ≈ 29px, langkah 2 antara 14 dan 33px; tinggi kotak label beragam (h 10 / 40 / 29 / 21) | Metadata posisi | Kesalahan [V] |
| E16 | Posisi kartu auth: baris field mulai y=230 (login) vs 153 (register langkah 1) vs 169.7 (langkah 2) | Metadata | Perlu keputusan |
| E17 | Kontras gagal AA: `#90A1B9` di putih 2.63 (admin), putih di `accent` 2.41, putih di `sec` 1.23 | Perhitungan | Kesalahan jika dipakai untuk teks esensial [V] |
| E18 | Warna daftar teks terkecil: admin 10/11 px di beberapa sel; SPPG Review 11 px | Properti teks | Perlu keputusan keterbacaan |
 
### 12.2 Variasi yang tampak disengaja (jangan "diperbaiki")
 
| # | Variasi | Alasan |
|---|---|---|
| D1 | Tab hari Pilih Menu memiliki lebar berbeda (Past 144, Today 179, Active 679, Open 163/169) | Representasi state, bukan lebar tetap [V] |
| D2 | Alur login siswa vs login SPPG hanya beda judul | Satu pola, dua peran [I] |
| D3 | Admin menampilkan tabel padat, Siswa menampilkan kartu | Beda kebutuhan peran [I] |
| D4 | Dua ellipse blur di landing | Dekorasi latar [I] |
| D5 | Strip chip menu landing meluber keluar frame | Kemungkinan marquee; frame meng-clip [I] |
| D6 | Warna status (hijau aman, amber peringatan, merah alergi) | Semantik status [I] |
 
### 12.3 Perlu keputusan tim (varian saling bersaing)
 
| # | Masalah | Opsi |
|---|---|---|
| K1 | **Lima sistem warna paralel**: (a) token inti, (b) Siswa (`#EFF4FF`, `#0B1C30`, `#3E4A3D`, `#BDCABA`), (c) Admin Tailwind slate/emerald, (d) SPPG (`#00594D`, `#121C2A`, `#96F6C7`), (e) SPPG Review (`#1C7365`, `#374151`, `#8CEBBD`) | Pilih satu primer dan satu skala netral untuk semua peran, atau tetapkan bahwa tiap peran punya tema dan beri nama |
| K2 | **Lebar frame SPPG 1280 vs 1440** | Satukan ke 1440 atau tetapkan max-width |
| K3 | **Primary**: `#297373` (token) vs `#0F766E`, `#00786F`, `#0D6E64`, `#1C7365`, `#00594D` | Tetapkan `#297373` sebagai satu-satunya primer, atau perluas token |
| K4 | **Navbar Siswa/Admin (103 tinggi, link 24px) vs header SPPG (64 tinggi, link kecil)** | Pilih satu pola navigasi |
| K5 | **Margin container**: Siswa x=53 (lebar 1335), Admin x=112 (lebar 1216), SPPG padding 32 (lebar 1216), landing 12-kolom 1128 | Tetapkan satu container |
| K6 | **Radius/shadow admin versus Siswa**: kartu Siswa radius 12 dengan shadow 5%; admin 12–16 dengan shadow 3–10% | Pilih satu preset kartu |
| K7 | **Label field auth**: Bold 20 hitam vs label admin/SPPG 12 | Pilih satu spesifikasi form |
| K8 | Skala tipografi tidak punya token; ukuran 10–13 bergantian | Tetapkan skala |
| K9 | Mode gelap, mobile, tablet | Belum digambar [?]; putuskan apakah wajib |
| K10 | Catatan desainer 173:2728: dropdown isi menu + tombol "lihat detail", keseimbangan gizi "lebih friendly" | Belum diadopsi dalam frame |
| K11 | Login Admin tidak ada; alur role-based redirect belum tergambar | Gambar atau jelaskan |
| K12 | Nama komponen generik ("Frame 2", "Frame 5", "Frame 6") dan duplikasi navbar sebagai frame biasa | Ganti nama dan jadikan komponen (nama baru tetap usulan tim) |
 
---