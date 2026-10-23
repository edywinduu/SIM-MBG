-- ==========================================================================
-- DATABASE SCHEMA - SIM-MBG (MySQL 8 / MariaDB)
-- Run this file first to initialize database and tables, then run queries.sql
-- ==========================================================================
DROP DATABASE IF EXISTS sim_mbg;
CREATE DATABASE sim_mbg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sim_mbg;

-- ---------- A. USER ACCOUNTS & MASTER DATA ----------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  role ENUM('admin', 'sppg', 'student') NOT NULL,
  institution VARCHAR(150) NULL,
  status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE sppg_units (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  region VARCHAR(100) NOT NULL,
  address TEXT NULL,
  capacity INT NOT NULL DEFAULT 0,
  contact_person VARCHAR(100) NULL,
  phone VARCHAR(20) NULL,
  status ENUM('active', 'maintenance', 'inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB;

CREATE TABLE schools (
  id INT AUTO_INCREMENT PRIMARY KEY,
  npsn VARCHAR(12) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  education_level ENUM('SD', 'SMP', 'SMA') NOT NULL,
  district VARCHAR(80) NOT NULL,
  city VARCHAR(80) NOT NULL,
  address TEXT NULL,
  pic_name VARCHAR(100) NULL,
  sppg_id INT NOT NULL,
  status ENUM('active', 'pending', 'inactive') NOT NULL DEFAULT 'active',
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id)
) ENGINE=InnoDB;

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNIQUE NULL,
  nisn VARCHAR(12) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  school_id INT NOT NULL,
  class_name VARCHAR(20) NOT NULL,
  recipient_status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  profile_picture VARCHAR(255) NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB;

CREATE TABLE sppg_staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  position VARCHAR(80) NOT NULL,
  sppg_id INT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id)
) ENGINE=InnoDB;

-- ---------- B. ALLERGIES & DIETARY RESTRICTIONS ----------
CREATE TABLE allergens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE student_allergies (
  student_id INT NOT NULL,
  allergen_id INT NOT NULL,
  medical_notes VARCHAR(255) NULL,
  verification_status ENUM('active', 'medical_review') NOT NULL DEFAULT 'medical_review',
  verified_by INT NULL,
  PRIMARY KEY (student_id, allergen_id),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (allergen_id) REFERENCES allergens(id),
  FOREIGN KEY (verified_by) REFERENCES sppg_staff(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- C. MENUS & NUTRITION ----------
CREATE TABLE menus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  category ENUM('regular', 'special', 'backup') NOT NULL DEFAULT 'regular',
  calories INT NOT NULL,
  carbs INT NOT NULL DEFAULT 0,
  protein INT NOT NULL DEFAULT 0,
  fat INT NOT NULL DEFAULT 0,
  fiber DECIMAL(4,1) NOT NULL DEFAULT 0,
  image VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE menu_allergens (
  menu_id INT NOT NULL,
  allergen_id INT NOT NULL,
  type ENUM('contains', 'free_from') NOT NULL,
  PRIMARY KEY (menu_id, allergen_id),
  FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
  FOREIGN KEY (allergen_id) REFERENCES allergens(id)
) ENGINE=InnoDB;

CREATE TABLE menu_schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  menu_id INT NOT NULL,
  sppg_id INT NOT NULL,
  date DATE NOT NULL,
  cycle VARCHAR(40) NULL,
  cutoff_time TIME NOT NULL DEFAULT '20:00:00',
  FOREIGN KEY (menu_id) REFERENCES menus(id),
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id)
) ENGINE=InnoDB;

-- ---------- D. SELECTION & DAILY PICKUP ----------
CREATE TABLE menu_selections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  schedule_id INT NOT NULL,
  menu_id INT NOT NULL,
  consumption_status ENUM('active', 'medical_review') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (student_id, schedule_id),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (schedule_id) REFERENCES menu_schedules(id),
  FOREIGN KEY (menu_id) REFERENCES menus(id)
) ENGINE=InnoDB;

CREATE TABLE pickups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  date DATE NOT NULL,
  status ENUM('pending', 'picked_up') NOT NULL DEFAULT 'pending',
  pickup_time DATETIME NULL,
  UNIQUE (student_id, date),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- E. LOGISTICS & DISTRIBUTION ----------
CREATE TABLE distributions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sppg_id INT NOT NULL,
  school_id INT NOT NULL,
  date DATE NOT NULL,
  portion_count INT NOT NULL,
  eta_start TIME NULL,
  eta_end TIME NULL,
  status ENUM('pending', 'in_transit', 'delivered') NOT NULL DEFAULT 'pending',
  fleet VARCHAR(30) NULL,
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id),
  FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sppg_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  description VARCHAR(255) NULL,
  status VARCHAR(30) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id)
) ENGINE=InnoDB;

CREATE TABLE announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sppg_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id)
) ENGINE=InnoDB;

-- ---------- F. REVIEWS & AUDITS ----------
CREATE TABLE student_reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  menu_id INT NOT NULL,
  rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (menu_id) REFERENCES menus(id)
) ENGINE=InnoDB;

CREATE TABLE school_feedbacks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  sender_name VARCHAR(100) NOT NULL,
  position VARCHAR(100) NULL,
  score DECIMAL(2,1) NOT NULL CHECK (score BETWEEN 1 AND 5),
  content TEXT NOT NULL,
  container_temperature VARCHAR(40) NULL,
  waste_percentage DECIMAL(4,1) DEFAULT 0,
  status ENUM('very_satisfied', 'needs_evaluation') NOT NULL,
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  kitchen_response TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB;

CREATE TABLE kitchen_audits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sppg_id INT NOT NULL,
  audit_date DATE NOT NULL,
  audit_type ENUM('hygiene', 'nutrition') NOT NULL,
  result VARCHAR(100) NOT NULL,
  certificate_file VARCHAR(255) NULL,
  FOREIGN KEY (sppg_id) REFERENCES sppg_units(id)
) ENGINE=InnoDB;

-- ==========================================================================
-- SEED DATA (for testing queries and frontend parity)
-- ==========================================================================
INSERT INTO users (username, email, password_hash, full_name, role, institution, status) VALUES
 ('admin',    'admin@mbg.go.id',    '$2b$10$contohhashadmin', 'Atmind Administrator', 'admin',   'Dinas Pendidikan & Gizi Provinsi', 'active'),
 ('pgw.alya', 'sppg.dps01@mbg.go.id','$2b$10$contohhashsppg',  'Pgw Alya Sudarma',   'sppg',    'SPPG Denpasar Barat 01',           'active'),
 ('alya',     'alya@sekolah.id',    '$2b$10$contohhashalya',  'Alya MBG',           'student', 'SMA Negeri 1 Denpasar',            'active'),
 ('aditya',   'aditya@sekolah.id',  '$2b$10$contohhashadit',  'Komang Aditya',      'student', 'SMP Negeri 1 Denpasar',            'active'),
 ('gita',     'gita@sekolah.id',    '$2b$10$contohhashgita',  'Ni Putu Gita Dewi',  'student', 'SMP Negeri 2 Denpasar',            'active');

INSERT INTO sppg_units (code, name, region, address, capacity, contact_person, phone, status) VALUES
 ('SPPG-DPS-01', 'SPPG Denpasar Barat 01', 'Kota Denpasar', 'Jl. Gunung Agung No. 45, Pemecutan, Denpasar Barat', 3500, 'I Wayan Sudarma', '081234567891', 'active'),
 ('SPPG-BDG-02', 'SPPG Badung Selatan 02', 'Kab. Badung',   'Jl. Bypass Ngurah Rai No. 12, Tuban, Kuta',         2800, 'Ni Made Rai',      '081234567892', 'active'),
 ('SPPG-DPS-03', 'SPPG Denpasar Selatan 03', 'Kota Denpasar', 'Jl. Tukad Yeh Aya No. 88, Panjer, Denpasar Selatan', 3200, 'I Ketut Gede',    '081234567893', 'active'),
 ('SPPG-BDG-04', 'SPPG Badung Utara 04',    'Kab. Badung',   'Jl. Raya Mengwi No. 102, Mengwi, Badung',           2200, 'I Nyoman Suweta',  '081234567894', 'maintenance');

INSERT INTO schools (npsn, name, education_level, district, city, address, pic_name, sppg_id, status) VALUES
 ('50103120', 'SMP Negeri 1 Denpasar', 'SMP', 'Denpasar Barat',  'Kota Denpasar',    'Jl. Surapati No. 2, Dangin Puri',          'I Nyoman Sukra',     1, 'active'),
 ('50103121', 'SMP Negeri 2 Denpasar', 'SMP', 'Denpasar Timur',  'Kota Denpasar',    'Jl. Kamboja No. 4, Dangin Puri Kangin',    'Ni Ketut Suartini',  1, 'active'),
 ('50102044', 'SMP Negeri 1 Gianyar',  'SMP', 'Kota Gianyar',    'Kabupaten Gianyar','Jl. Raya Gianyar No. 10',                  'Wayan Sudarta',      1, 'active'),
 ('50101980', 'SMP Negeri 1 Klungkung','SMP', 'Semarapura',      'Kabupaten Klungkung','Jl. Untung Surapati No. 8',              'Luh Putu Aryani',    1, 'active'),
 ('50101567', 'SMP Negeri 1 Kuta',     'SMP', 'Kuta Selatan',    'Kabupaten Badung', 'Jl. Kediri No. 7, Tuban, Kuta',            'Gede Wira Pratama',  2, 'active'),
 ('50103441', 'SD Negeri 1 Renon',     'SD',  'Denpasar Selatan','Kota Denpasar',    'Jl. Tukad Balian No. 15, Renon',           'I Made Sujana',      3, 'pending');

INSERT INTO students (user_id, nisn, name, school_id, class_name, recipient_status) VALUES
 (3, '0081234567', 'Alya MBG',          1, 'XI IPA 1', 'active'),
 (4, '0081234568', 'Komang Aditya',     1, 'VIII C',   'active'),
 (5, '0081234569', 'Ni Putu Gita Dewi', 2, 'VII B',    'active');

INSERT INTO sppg_staff (user_id, name, position, sppg_id) VALUES
 (2, 'Pgw Alya Sudarma', 'Pegawai SPPG', 1);

INSERT INTO allergens (name) VALUES
 ('Kacang'), ('Seafood'), ('Telur'), ('Laktosa'), ('Gluten');

INSERT INTO student_allergies (student_id, allergen_id, medical_notes, verification_status, verified_by) VALUES
 (1, 1, 'Sensitif produk olahan kacang tanah', 'active', 1),
 (1, 2, 'Alergi udang laut', 'active', 1),
 (2, 3, 'Alergi telur ayam (gatal)', 'active', 1),
 (3, 4, 'Alergi susu sapi & olahan', 'medical_review', NULL);

INSERT INTO menus (name, description, category, calories, carbs, protein, fat, fiber, is_active) VALUES
 ('Paket Ayam Panggang Madu & Capcay Sayur', 'Nasi putih pulen, ayam panggang saus madu, capcay wortel brokoli jagung, semangka potong', 'regular', 620, 65, 32, 18, 8.0, 1),
 ('Paket Ikan Bakar Sambal Dabu & Cah Kangkung', 'Nasi merah organik, fillet ikan kembung bakar rempah, sambal dabu segar, pisang ambon', 'special', 590, 60, 34, 14, 7.0, 1),
 ('Paket Rolade Daging Sapi & Sup Sayur Bening', 'Nasi putih, rolade daging sapi saus gurih, sup bening wortel buncis, jeruk manis', 'regular', 640, 70, 28, 20, 6.0, 1),
 ('Paket Sate Ayam Lilit & Sayur Daun Kelor', 'Nasi jagung, sate ayam lilit, sup kelor, tempe orek, buah pepaya', 'special', 605, 70, 25, 15, 7.1, 1),
 ('Paket Siaga Cadangan: Ayam Opor Suwir', 'Nasi pulen, ayam opor suwir, tahu tempe bacem, kuah gurih bening', 'backup', 610, 76, 24, 15, 5.5, 1);

INSERT INTO menu_allergens (menu_id, allergen_id, type) VALUES
 (1, 1, 'free_from'),
 (1, 4, 'contains'),
 (2, 2, 'contains'),
 (2, 1, 'free_from'),
 (3, 2, 'free_from'),
 (3, 3, 'free_from'),
 (4, 5, 'free_from'),
 (5, 3, 'free_from');

INSERT INTO menu_schedules (menu_id, sppg_id, date, cycle, cutoff_time) VALUES
 (1, 1, CURDATE(), 'Oktober 2026 - Minggu 1', '20:00:00'),
 (3, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Oktober 2026 - Minggu 1', '20:00:00');

INSERT INTO menu_selections (student_id, schedule_id, menu_id, consumption_status) VALUES
 (1, 1, 1, 'active'),
 (2, 1, 2, 'active'),
 (3, 1, 2, 'medical_review');

INSERT INTO pickups (student_id, date, status, pickup_time) VALUES
 (1, CURDATE(), 'pending', NULL),
 (1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'picked_up', NOW()),
 (2, CURDATE(), 'picked_up', NOW());

INSERT INTO distributions (sppg_id, school_id, date, portion_count, eta_start, eta_end, status, fleet) VALUES
 (1, 1, CURDATE(), 542, '09:00:00', '09:30:00', 'delivered',  'B-01'),
 (1, 2, CURDATE(), 765, '09:15:00', '09:45:00', 'in_transit', 'B-02'),
 (1, 3, CURDATE(), 480, '09:30:00', '10:00:00', 'in_transit', 'B-04'),
 (1, 4, CURDATE(), 415, '10:00:00', '10:30:00', 'pending',    'B-05'),
 (2, 5, CURDATE(), 738, '09:00:00', '09:30:00', 'delivered',  'B-03');

INSERT INTO activity_logs (sppg_id, title, description, status) VALUES
 (1, 'Batch reception completed - SMP Negeri 1 Denpasar', '240 portions handed over to School Health Unit', 'Delivered'),
 (1, 'Courier fleet heading to destination - SMP Negeri 2 Gianyar', '180 portions in thermal distribution boxes', 'In Transit');

INSERT INTO announcements (sppg_id, title, content) VALUES
 (1, 'MBG Menu Distribution Schedule', 'October 2026 cycle is actively proceeding in compliance with National Nutrition Standards.');

INSERT INTO student_reviews (student_id, menu_id, rating, comment) VALUES
 (1, 1, 5, 'Delicious and well portioned!'),
 (1, 3, 4, 'Tender beef roll and fresh soup'),
 (2, 2, 5, 'Safe for my allergy and healthy'),
 (3, 2, 3, 'A bit mild in taste');

INSERT INTO school_feedbacks (school_id, sender_name, position, score, content, container_temperature, waste_percentage, status, is_verified) VALUES
 (1, 'I Nyoman Sukra', 'UKS & Nutrition Coordinator', 5.0, 'Students were very enthusiastic, packaging is hygienic and served warm.', 'Warm (58 C)', 0.8, 'very_satisfied', 1),
 (2, 'Ni Ketut Suartini', 'Distribution Supervisor', 4.8, 'Allergen tags are very clear, avoiding any distribution errors.', 'Warm (56 C)', 1.5, 'very_satisfied', 1),
 (4, 'Luh Putu Aryani', 'School Committee', 3.8, 'Distribution arrived close to break time, please arrive 15 minutes earlier.', 'Warm (54 C)', 6.2, 'needs_evaluation', 0);

INSERT INTO kitchen_audits (sppg_id, audit_date, audit_type, result, certificate_file) VALUES
 (1, CURDATE(), 'hygiene', 'Sanitary & Food Hygiene Certified', 'hygiene-lab-cert.pdf');
