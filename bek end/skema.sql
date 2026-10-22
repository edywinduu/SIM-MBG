-- ==========================================================================
-- SCHEMA DATABASE - SIM-MBG (MySQL 8 / MariaDB)
-- Jalankan file ini dulu, baru queries.sql
-- ==========================================================================
DROP DATABASE IF EXISTS sim_mbg;
CREATE DATABASE sim_mbg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sim_mbg;

-- ---------- A. AKUN & MASTER DATA ----------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,          -- simpan hash (bcrypt), bukan password asli
  role ENUM('siswa','sppg') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE sppg_units (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  wilayah VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE schools (
  id INT AUTO_INCREMENT PRIMARY KEY,
  npsn VARCHAR(12) NOT NULL UNIQUE,
  nama VARCHAR(120) NOT NULL,
  jenjang ENUM('SD','SMP','SMA') NOT NULL,
  kecamatan VARCHAR(80) NOT NULL,
  kabupaten_kota VARCHAR(80) NOT NULL,
  nama_pic VARCHAR(100),
  sppg_id INT NOT NULL,
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id)
) ENGINE=InnoDB;

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNIQUE,
  nisn VARCHAR(12) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  school_id INT NOT NULL,
  kelas VARCHAR(20) NOT NULL,
  status_penerima ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  foto_profil VARCHAR(255),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB;

CREATE TABLE staff_sppg (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  jabatan VARCHAR(80) NOT NULL,
  sppg_id INT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id)
) ENGINE=InnoDB;

-- ---------- B. ALERGI ----------
CREATE TABLE allergens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE student_allergies (                -- jembatan siswa <-> alergen (many-to-many)
  student_id INT NOT NULL,
  allergen_id INT NOT NULL,
  catatan_medis VARCHAR(255),
  status_verifikasi ENUM('aktif','review_medis') NOT NULL DEFAULT 'review_medis',
  verified_by INT NULL,
  PRIMARY KEY (student_id, allergen_id),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (allergen_id) REFERENCES allergens(id),
  FOREIGN KEY (verified_by) REFERENCES staff_sppg(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- C. MENU ----------
CREATE TABLE menus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  deskripsi TEXT,
  kategori ENUM('reguler','khusus','cadangan') NOT NULL DEFAULT 'reguler',
  kalori INT NOT NULL,
  karbo INT NOT NULL DEFAULT 0,
  protein INT NOT NULL DEFAULT 0,
  lemak INT NOT NULL DEFAULT 0,
  serat DECIMAL(4,1) NOT NULL DEFAULT 0,
  gambar VARCHAR(255),
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE menu_allergens (                   -- label "Bebas Kacang" / "Mengandung Susu Sapi"
  menu_id INT NOT NULL,
  allergen_id INT NOT NULL,
  tipe ENUM('mengandung','bebas') NOT NULL,
  PRIMARY KEY (menu_id, allergen_id),
  FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
  FOREIGN KEY (allergen_id) REFERENCES allergens(id)
) ENGINE=InnoDB;

CREATE TABLE menu_schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  menu_id INT NOT NULL,
  sppg_id INT NOT NULL,
  tanggal DATE NOT NULL,
  siklus VARCHAR(40),
  cutoff_time TIME NOT NULL DEFAULT '20:00:00',
  FOREIGN KEY (menu_id) REFERENCES menus(id),
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id)
) ENGINE=InnoDB;

-- ---------- D. PILIHAN & PENGAMBILAN ----------
CREATE TABLE menu_selections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  schedule_id INT NOT NULL,
  menu_id INT NOT NULL,
  status_konsumsi ENUM('aktif','review_medis') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (student_id, schedule_id),             -- 1 siswa hanya 1 pilihan per jadwal
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (schedule_id) REFERENCES menu_schedules(id),
  FOREIGN KEY (menu_id) REFERENCES menus(id)
) ENGINE=InnoDB;

CREATE TABLE pickups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  tanggal DATE NOT NULL,
  status ENUM('belum','sudah') NOT NULL DEFAULT 'belum',
  waktu_ambil DATETIME NULL,
  UNIQUE (student_id, tanggal),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- E. DISTRIBUSI ----------
CREATE TABLE distributions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sppg_id INT NOT NULL,
  school_id INT NOT NULL,
  tanggal DATE NOT NULL,
  jumlah_porsi INT NOT NULL,
  eta_mulai TIME,
  eta_selesai TIME,
  status ENUM('menunggu','dikirim','terkirim') NOT NULL DEFAULT 'menunggu',
  armada VARCHAR(30),
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id),
  FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sppg_id INT NOT NULL,
  judul VARCHAR(200) NOT NULL,
  deskripsi VARCHAR(255),
  status VARCHAR(30) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id)
) ENGINE=InnoDB;

CREATE TABLE announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sppg_id INT NOT NULL,
  judul VARCHAR(150) NOT NULL,
  isi TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id)
) ENGINE=InnoDB;

-- ---------- F. REVIEW & AUDIT ----------
CREATE TABLE student_reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  menu_id INT NOT NULL,
  rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  komentar TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (menu_id) REFERENCES menus(id)
) ENGINE=InnoDB;

CREATE TABLE school_feedbacks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  nama_pengirim VARCHAR(100) NOT NULL,
  jabatan VARCHAR(100),
  skor DECIMAL(2,1) NOT NULL CHECK (skor BETWEEN 1 AND 5),
  isi TEXT NOT NULL,
  suhu_wadah VARCHAR(40),
  sisa_porsi_persen DECIMAL(4,1) DEFAULT 0,
  status ENUM('sangat_puas','perlu_evaluasi') NOT NULL,
  terverifikasi TINYINT(1) NOT NULL DEFAULT 0,
  tanggapan_dapur TEXT NULL,                    -- terisi saat tombol "Tanggapi Tim Dapur"
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB;

CREATE TABLE kitchen_audits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sppg_id INT NOT NULL,
  tanggal DATE NOT NULL,
  jenis ENUM('higienitas','gizi') NOT NULL,
  hasil VARCHAR(100),
  file_sertifikat VARCHAR(255),
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id)
) ENGINE=InnoDB;

-- ==========================================================================
-- DATA CONTOH (supaya queries.sql langsung menghasilkan data)
-- ==========================================================================
INSERT INTO users (username, email, password_hash, role) VALUES
 ('alya',  'alya@sekolah.id',  '$2b$10$contohhashalya',  'siswa'),
 ('aditya','aditya@sekolah.id','$2b$10$contohhashadit',  'siswa'),
 ('gita',  'gita@sekolah.id',  '$2b$10$contohhashgita',  'siswa'),
 ('pgw.alya','alya@sppg.go.id','$2b$10$contohhashsppg',  'sppg');

INSERT INTO sppg_units (nama, wilayah) VALUES ('SPPG Denpasar Selatan', 'Denpasar & Badung');

INSERT INTO schools (npsn, nama, jenjang, kecamatan, kabupaten_kota, nama_pic, sppg_id) VALUES
 ('50103120','SMP Negeri 1 Denpasar','SMP','Denpasar Barat','Kota Denpasar','I Nyoman Sukra',1),
 ('50103121','SMP Negeri 2 Denpasar','SMP','Denpasar Timur','Kota Denpasar','Ni Ketut Suartini',1),
 ('50102044','SMP Negeri 1 Gianyar','SMP','Kota','Kabupaten Gianyar','Wayan Sudarta',1),
 ('50101980','SMP Negeri 1 Klungkung','SMP','Semarapura','Kabupaten Klungkung','Luh Putu Aryani',1),
 ('50101567','SMP Negeri 1 Kuta','SMP','Kuta Selatan','Kabupaten Badung','Gede Wira Pratama',1);

INSERT INTO students (user_id, nisn, nama, school_id, kelas) VALUES
 (1,'0087612451','Alya MBG',1,'VII-A'),
 (2,'0087612453','Komang Aditya',1,'VIII-C'),
 (3,'0087612452','Ni Putu Gita Dewi',2,'VII-B');

INSERT INTO staff_sppg (user_id, nama, jabatan, sppg_id) VALUES (4,'Pgw Alya','Pegawai SPPG',1);

INSERT INTO allergens (nama) VALUES ('Kacang'),('Seafood'),('Telur'),('Laktosa'),('Gluten');

INSERT INTO student_allergies (student_id, allergen_id, catatan_medis, status_verifikasi, verified_by) VALUES
 (2, 3, 'Alergi telur ayam (gatal)', 'aktif', 1),
 (3, 4, 'Alergi susu sapi & olahan', 'review_medis', NULL);

INSERT INTO menus (nama, deskripsi, kategori, kalori, karbo, protein, lemak, serat, is_active) VALUES
 ('Paket Ayam Panggang Madu & Capcay Bakso','Nasi merah, ayam panggang madu, capcay, semangka','reguler',630,75,26,16,6.8,1),
 ('Paket Semur Daging Sapi & Tumis Buncis Tempe','Nasi putih, semur daging, tumis buncis tempe','khusus',620,78,28,14,6.2,1),
 ('Paket Ikan Fillet Kunyit & Sayur Bening Bayam','Nasi putih, fillet ikan kunyit, sayur bening','reguler',595,72,27,12,5.9,1),
 ('Paket Sate Ayam Lilit & Sayur Daun Kelor','Nasi jagung, sate ayam lilit, sup kelor','khusus',605,70,25,15,7.1,1),
 ('Paket Siaga Cadangan: Ayam Opor Suwir','Nasi pulen, ayam opor, bacem tahu tempe','cadangan',610,76,24,15,5.5,1);

INSERT INTO menu_allergens (menu_id, allergen_id, tipe) VALUES
 (1,1,'bebas'),(1,4,'mengandung'),(2,2,'bebas'),(2,4,'bebas'),
 (3,2,'mengandung'),(3,3,'bebas'),(4,5,'bebas'),(5,3,'bebas');

INSERT INTO menu_schedules (menu_id, sppg_id, tanggal, siklus) VALUES
 (1,1,CURDATE(),'Oktober 2026 - Minggu 1'),
 (3,1,DATE_ADD(CURDATE(), INTERVAL 1 DAY),'Oktober 2026 - Minggu 1');

INSERT INTO menu_selections (student_id, schedule_id, menu_id, status_konsumsi) VALUES
 (1,1,1,'aktif'),(2,1,2,'aktif'),(3,1,2,'review_medis');

INSERT INTO pickups (student_id, tanggal, status, waktu_ambil) VALUES
 (1,CURDATE(),'belum',NULL),
 (1,DATE_SUB(CURDATE(), INTERVAL 1 DAY),'sudah',NOW()),
 (2,CURDATE(),'sudah',NOW());

INSERT INTO distributions (sppg_id, school_id, tanggal, jumlah_porsi, eta_mulai, eta_selesai, status, armada) VALUES
 (1,1,CURDATE(),542,'09:00','09:30','terkirim','B-01'),
 (1,2,CURDATE(),765,'09:15','09:45','dikirim','B-02'),
 (1,3,CURDATE(),480,'09:30','10:00','dikirim','B-04'),
 (1,4,CURDATE(),415,'10:00','10:30','menunggu','B-05'),
 (1,5,CURDATE(),738,'09:00','09:30','terkirim','B-03');

INSERT INTO activity_logs (sppg_id, judul, deskripsi, status) VALUES
 (1,'Penerimaan batch selesai - SMP Negeri 1 Denpasar','240 porsi diserahkan kepada Petugas UKS','Terkirim'),
 (1,'Armada kurir menuju lokasi - SMP Negeri 2 Gianyar','180 porsi dalam box termal','Diproses');

INSERT INTO announcements (sppg_id, judul, isi) VALUES
 (1,'Jadwal Distribusi Menu MBG','Periode Oktober 2026 sedang berjalan aktif sesuai standar AKG Nasional.');

INSERT INTO student_reviews (student_id, menu_id, rating, komentar) VALUES
 (1,1,5,'Enak banget, porsinya pas'),(1,3,4,'Ikannya lembut'),
 (2,2,5,'Aman buat alergi saya'),(3,2,3,'Agak hambar');

INSERT INTO school_feedbacks (school_id, nama_pengirim, jabatan, skor, isi, suhu_wadah, sisa_porsi_persen, status, terverifikasi) VALUES
 (1,'I Nyoman Sukra','Koordinator UKS & Gizi',5.0,'Anak-anak sangat antusias, kemasan higienis, suhu masih hangat.','Hangat (58 C)',0.8,'sangat_puas',1),
 (3,'Ni Ketut Suartini','Pengawas Distribusi',4.8,'Label alergen sangat jelas sehingga tidak tertukar.','Hangat (56 C)',1.5,'sangat_puas',1),
 (4,'Luh Putu Aryani','Komite Sekolah & Tim UKS',3.8,'Armada tiba mendekati jam istirahat, mohon dimajukan 15 menit.','Hangat (54 C)',6.2,'perlu_evaluasi',0);

INSERT INTO kitchen_audits (sppg_id, tanggal, jenis, hasil, file_sertifikat) VALUES
 (1,CURDATE(),'higienitas','Laik Higiene Sanitasi','sertifikat-lab.pdf');