-- ==========================================================================
-- QUERIES - SIM-MBG (MySQL 8 / MariaDB)
-- Tiap query diberi nama fitur di frontend yang dilayaninya.
-- Nilai seperti 'alya' atau 1 adalah contoh; di backend diganti parameter (?).
-- ==========================================================================
USE sim_mbg;

-- ##########################################################################
-- 1. AUTH (login.html / register.html)
-- ##########################################################################

-- 1.1 Login: ambil user lalu bandingkan password_hash di backend (bcrypt.compare)
SELECT id, username, role, password_hash
FROM users
WHERE username = 'alya' OR email = 'alya' LIMIT 1;

-- 1.2 Register: simpan akun + data siswa (jalankan dalam satu transaksi)
START TRANSACTION;
INSERT INTO users (username, email, password_hash, role)
VALUES ('budi', 'budi@sekolah.id', '$2b$10$hashbudi', 'siswa');
INSERT INTO students (user_id, nisn, nama, school_id, kelas)
VALUES (LAST_INSERT_ID(), '0087612499', 'Budi Santoso', 1, 'VII-A');
COMMIT;

-- ##########################################################################
-- 2. SPPG - BERANDA (dashboard.html)
-- ##########################################################################

-- 2.1 KPI: total porsi hari ini
SELECT COALESCE(SUM(jumlah_porsi), 0) AS total_porsi
FROM distributions WHERE tanggal = CURDATE();

-- 2.2 KPI: sekolah penerima aktif
SELECT COUNT(DISTINCT school_id) AS sekolah_aktif
FROM distributions WHERE tanggal = CURDATE();

-- 2.3 KPI: menu terverifikasi (menu aktif)
SELECT COUNT(*) AS menu_aktif FROM menus WHERE is_active = 1;

-- 2.4 KPI: catatan evaluasi yang belum ditanggapi
SELECT COUNT(*) AS catatan_baru
FROM school_feedbacks
WHERE status = 'perlu_evaluasi' AND tanggapan_dapur IS NULL;

-- 2.5 Pengumuman terbaru
SELECT judul, isi, created_at FROM announcements ORDER BY created_at DESC LIMIT 1;

-- 2.6 Aktivitas distribusi & verifikasi (4 terbaru)
SELECT judul, deskripsi, status, created_at
FROM activity_logs ORDER BY created_at DESC LIMIT 4;

-- 2.7 Menu hari ini
SELECT m.nama, m.deskripsi, m.kalori, m.protein, m.serat
FROM menu_schedules ms
JOIN menus m ON m.id = ms.menu_id
WHERE ms.tanggal = CURDATE() LIMIT 1;

-- 2.8 Tabel distribusi sekolah: search + pagination (5 per halaman, halaman 1)
SELECT s.nama, s.npsn, s.nama_pic,
       CONCAT(s.kabupaten_kota, ' / ', s.kecamatan) AS wilayah,
       d.jumlah_porsi,
       CONCAT(TIME_FORMAT(d.eta_mulai,'%H:%i'), ' - ', TIME_FORMAT(d.eta_selesai,'%H:%i')) AS estimasi_tiba,
       d.status
FROM distributions d
JOIN schools s ON s.id = d.school_id
WHERE d.tanggal = CURDATE()
  AND (s.nama LIKE '%denpasar%' OR s.kecamatan LIKE '%denpasar%' OR s.kabupaten_kota LIKE '%denpasar%')
ORDER BY s.nama
LIMIT 5 OFFSET 0;

-- 2.9 Total baris untuk teks "Menampilkan 1-5 dari N" dan jumlah halaman
SELECT COUNT(*) AS total
FROM distributions d JOIN schools s ON s.id = d.school_id
WHERE d.tanggal = CURDATE()
  AND (s.nama LIKE '%denpasar%' OR s.kecamatan LIKE '%denpasar%' OR s.kabupaten_kota LIKE '%denpasar%');

-- 2.10 Tombol "Sinkron" / update status pengiriman
UPDATE distributions SET status = 'terkirim'
WHERE school_id = 2 AND tanggal = CURDATE();

-- ##########################################################################
-- 3. SPPG - MENU (menu.html)
-- ##########################################################################

-- 3.1 KPI menu (semua dihitung dari data, jadi ikut berubah saat menu ditambah)
SELECT COUNT(*)                                  AS total_menu_aktif,
       ROUND(AVG(kalori))                        AS rata_rata_kalori,
       SUM(kategori = 'khusus')                  AS menu_alergen_khusus
FROM menus WHERE is_active = 1;

-- 3.2 Angka pada tab filter (Semua / Reguler / Khusus / Cadangan)
SELECT kategori, COUNT(*) AS jumlah FROM menus GROUP BY kategori;

-- 3.3 Kartu menu + daftar alergen, dengan filter tab & pencarian
SELECT m.id, m.nama, m.deskripsi, m.kategori, m.kalori, m.karbo, m.protein, m.lemak, m.is_active,
       GROUP_CONCAT(CONCAT(IF(ma.tipe='bebas','Bebas ','Mengandung '), a.nama)
                    ORDER BY ma.tipe SEPARATOR ', ') AS label_alergen
FROM menus m
LEFT JOIN menu_allergens ma ON ma.menu_id = m.id
LEFT JOIN allergens a ON a.id = ma.allergen_id
WHERE (m.kategori = 'reguler' OR 'reguler' = 'all')          -- ganti sesuai tab aktif
  AND (m.nama LIKE '%ayam%' OR m.deskripsi LIKE '%ayam%')     -- kotak pencarian
GROUP BY m.id
ORDER BY m.id
LIMIT 6 OFFSET 0;

-- 3.4 Tambah Menu Baru (modal)
INSERT INTO menus (nama, deskripsi, kategori, kalori, karbo, protein, lemak)
VALUES ('Paket Ayam Bakar & Sayur Asem', 'Nasi, ayam bakar, sayur asem', 'reguler', 620, 72, 26, 15);

-- 3.5 Edit menu
UPDATE menus
SET nama = 'Paket Ayam Bakar & Sayur Asem', kalori = 630, kategori = 'reguler'
WHERE id = 1;

-- 3.6 Switch Aktif/Nonaktif
UPDATE menus SET is_active = 0 WHERE id = 1;

-- ##########################################################################
-- 4. SPPG - PILIHAN PENGGUNA (pilihan.html)
-- ##########################################################################

-- 4.1 KPI: total siswa, paket reguler, diet khusus, sekolah terhubung
SELECT (SELECT COUNT(*) FROM students WHERE status_penerima = 'aktif')       AS total_siswa,
       (SELECT COUNT(*) FROM menu_selections ms JOIN menus m ON m.id = ms.menu_id
         WHERE m.kategori = 'reguler')                                       AS paket_reguler,
       (SELECT COUNT(DISTINCT student_id) FROM student_allergies)            AS diet_alergi_khusus,
       (SELECT COUNT(DISTINCT school_id) FROM students)                      AS sekolah_terhubung;

-- 4.2 Tabel siswa + filter (sekolah, kelas, kategori, status) + search + pagination
SELECT st.nisn, st.nama, sc.nama AS sekolah, sc.kabupaten_kota, st.kelas,
       m.nama AS pilihan_menu, m.kategori,
       COALESCE(GROUP_CONCAT(al.catatan_medis SEPARATOR '; '), '') AS catatan_alergi,
       COALESCE(ms.status_konsumsi, 'aktif') AS status_konsumsi
FROM students st
JOIN schools sc ON sc.id = st.school_id
LEFT JOIN menu_schedules sch ON sch.tanggal = CURDATE()
LEFT JOIN menu_selections ms ON ms.student_id = st.id AND ms.schedule_id = sch.id
LEFT JOIN menus m ON m.id = ms.menu_id
LEFT JOIN student_allergies al ON al.student_id = st.id
WHERE (st.nama LIKE '%a%' OR st.nisn LIKE '%a%')              -- pencarian
  AND (sc.id = 1 OR 1 = 0)                                    -- filter sekolah (hapus bila "Semua")
  AND (st.kelas = 'VII-A' OR 1 = 0)                           -- filter kelas
GROUP BY st.id, sc.nama, sc.kabupaten_kota, m.nama, m.kategori, ms.status_konsumsi
ORDER BY st.nama
LIMIT 10 OFFSET 0;

-- 4.3 Tombol "Ubah": ganti pilihan menu siswa
UPDATE menu_selections SET menu_id = 3
WHERE student_id = 1 AND schedule_id = 1;

-- 4.4 Tombol "Verifikasi": Review Medis -> Aktif
UPDATE student_allergies
SET status_verifikasi = 'aktif', verified_by = 1
WHERE student_id = 3 AND allergen_id = 4;

UPDATE menu_selections SET status_konsumsi = 'aktif'
WHERE student_id = 3 AND schedule_id = 1;

-- 4.5 Input Preferensi Siswa baru
INSERT INTO student_allergies (student_id, allergen_id, catatan_medis, status_verifikasi)
VALUES (1, 1, 'Alergi kacang tanah', 'review_medis');

-- 4.6 Cek NISN duplikat sebelum menambah siswa
SELECT COUNT(*) AS sudah_ada FROM students WHERE nisn = '0087612451';

-- ##########################################################################
-- 5. SPPG - REVIEW (review.html)
-- ##########################################################################

-- 5.1 KPI kepuasan
SELECT ROUND(AVG(skor), 2)                          AS skor_kepuasan,
       COUNT(*)                                     AS ulasan_diterima,
       COUNT(DISTINCT school_id)                    AS sekolah_mengisi,
       ROUND(AVG(sisa_porsi_persen), 1)             AS waste_persen,
       ROUND(100 - AVG(sisa_porsi_persen), 1)       AS tingkat_konsumsi
FROM school_feedbacks;

-- 5.2 Ketepatan distribusi (porsi yang sudah terkirim dibanding total)
SELECT ROUND(100 * SUM(status = 'terkirim') / COUNT(*), 1) AS persen_terkirim
FROM distributions WHERE tanggal = CURDATE();

-- 5.3 Daftar umpan balik + filter chip (Semua / Sangat Puas / Perlu Evaluasi)
SELECT f.id, s.nama AS sekolah, f.nama_pengirim, f.jabatan, f.skor, f.isi,
       f.suhu_wadah, f.sisa_porsi_persen, f.status, f.terverifikasi,
       f.tanggapan_dapur, f.created_at
FROM school_feedbacks f
JOIN schools s ON s.id = f.school_id
WHERE (f.status = 'perlu_evaluasi' OR 'all' = 'all')          -- ganti sesuai chip aktif
ORDER BY f.created_at DESC;

-- 5.4 Tombol "Tanggapi Tim Dapur"
UPDATE school_feedbacks
SET tanggapan_dapur = 'Jadwal armada jalur Klungkung dimajukan 15 menit.'
WHERE id = 3;

-- 5.5 Sebaran rating (1-5 bintang) dalam persen
SELECT rating, COUNT(*) AS jumlah,
       ROUND(100 * COUNT(*) / SUM(COUNT(*)) OVER (), 0) AS persen
FROM student_reviews
GROUP BY rating
ORDER BY rating DESC;

-- 5.6 Menu paling disukai siswa (persen rating 4-5)
SELECT m.nama, m.kategori,
       ROUND(100 * SUM(r.rating >= 4) / COUNT(*), 1) AS persen_suka
FROM student_reviews r
JOIN menus m ON m.id = r.menu_id
GROUP BY m.id
ORDER BY persen_suka DESC, COUNT(*) DESC
LIMIT 3;

-- 5.7 Kartu audit higienitas terbaru
SELECT hasil, tanggal, file_sertifikat
FROM kitchen_audits WHERE jenis = 'higienitas'
ORDER BY tanggal DESC LIMIT 1;

-- ##########################################################################
-- 6. SISWA (dashboard, pilih menu, riwayat, profil)
-- ##########################################################################

-- 6.1 Dashboard siswa: status pengambilan hari ini
SELECT status, waktu_ambil FROM pickups
WHERE student_id = 1 AND tanggal = CURDATE();

-- 6.2 Tombol "Ambil makan": tandai sudah diambil
UPDATE pickups SET status = 'sudah', waktu_ambil = NOW()
WHERE student_id = 1 AND tanggal = CURDATE();

-- 6.3 KPI siswa: tingkat kehadiran, jumlah ulasan, rata-rata rating
SELECT (SELECT ROUND(100 * SUM(status = 'sudah') / COUNT(*)) FROM pickups WHERE student_id = 1) AS kehadiran_persen,
       (SELECT COUNT(*) FROM student_reviews WHERE student_id = 1)                              AS jumlah_ulasan,
       (SELECT ROUND(AVG(rating), 1) FROM student_reviews WHERE student_id = 1)                 AS rata_rata_rating;

-- 6.4 Halaman Pilih Menu: menu yang tersedia di jadwal tertentu,
--     disembunyikan bila mengandung alergen siswa
SELECT m.id, m.nama, m.kalori, m.protein
FROM menus m
WHERE m.is_active = 1
  AND NOT EXISTS (
        SELECT 1
        FROM student_allergies sa
        JOIN menu_allergens ma ON ma.allergen_id = sa.allergen_id
        WHERE sa.student_id = 2 AND ma.menu_id = m.id AND ma.tipe = 'mengandung');

-- 6.5 Simpan / ubah pilihan menu (hanya sebelum cut-off 20:00 hari sebelumnya)
INSERT INTO menu_selections (student_id, schedule_id, menu_id)
SELECT 1, ms.id, 3
FROM menu_schedules ms
WHERE ms.id = 2
  AND NOW() < TIMESTAMP(DATE_SUB(ms.tanggal, INTERVAL 1 DAY), ms.cutoff_time)
ON DUPLICATE KEY UPDATE menu_id = VALUES(menu_id);

-- 6.6 Riwayat & ulasan: daftar ulasan siswa (+ filter sudah/belum diulas via LEFT JOIN)
SELECT m.nama AS menu, r.rating, r.komentar, r.created_at
FROM student_reviews r
JOIN menus m ON m.id = r.menu_id
WHERE r.student_id = 1
ORDER BY r.created_at DESC;

-- 6.7 Tambah / edit / hapus ulasan
INSERT INTO student_reviews (student_id, menu_id, rating, komentar) VALUES (1, 2, 5, 'Mantap');
UPDATE student_reviews SET rating = 4, komentar = 'Lumayan' WHERE id = 1 AND student_id = 1;
DELETE FROM student_reviews WHERE id = 1 AND student_id = 1;

-- 6.8 Profil: daftar alergi siswa & simpan perubahan
SELECT a.nama, sa.catatan_medis, sa.status_verifikasi
FROM student_allergies sa JOIN allergens a ON a.id = sa.allergen_id
WHERE sa.student_id = 2;

DELETE FROM student_allergies WHERE student_id = 1;           -- hapus lama
INSERT INTO student_allergies (student_id, allergen_id) VALUES (1, 1), (1, 3);  -- isi ulang dari checkbox
