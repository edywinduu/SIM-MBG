-- ==========================================================================
-- SQL QUERIES - SIM-MBG (MySQL 8 / MariaDB)
-- Categorized by application portal and target pages.
-- Parameter values (e.g., 'alya', 1) are placeholders to be bound via backend parameters (?).
-- ==========================================================================
USE sim_mbg;

-- ##########################################################################
-- 1. AUTHENTICATION (login.html, register.html)
-- ##########################################################################

-- 1.1 Login: Fetch user credentials and role for backend password verification (e.g. bcrypt)
SELECT id, username, email, password_hash, full_name, role, status
FROM users
WHERE (username = 'alya' OR email = 'alya@sekolah.id')
  AND status = 'active'
LIMIT 1;

-- 1.2 Student Self-Registration: Create user account and linked student profile in a single transaction
START TRANSACTION;
INSERT INTO users (username, email, password_hash, full_name, role, institution, status)
VALUES ('budi_santoso', 'budi@sekolah.id', '$2b$10$hashbudi', 'Budi Santoso', 'student', 'SMP Negeri 1 Denpasar', 'active');

INSERT INTO students (user_id, nisn, name, school_id, class_name, recipient_status)
VALUES (LAST_INSERT_ID(), '0087612499', 'Budi Santoso', 1, 'VII A', 'active');
COMMIT;

-- 1.3 Check NISN duplicate before registration
SELECT COUNT(*) AS existing_count
FROM students
WHERE nisn = '0087612499';


-- ##########################################################################
-- 2. SPPG - DASHBOARD (dashboard.html)
-- ##########################################################################

-- 2.1 KPI: Total meal portions scheduled/distributed today
SELECT COALESCE(SUM(portion_count), 0) AS total_portions_today
FROM distributions
WHERE date = CURDATE();

-- 2.2 KPI: Number of active recipient schools today
SELECT COUNT(DISTINCT school_id) AS active_schools_today
FROM distributions
WHERE date = CURDATE();

-- 2.3 KPI: Verified active menus available
SELECT COUNT(*) AS total_active_menus
FROM menus
WHERE is_active = 1;

-- 2.4 KPI: Pending school evaluations requiring kitchen response
SELECT COUNT(*) AS pending_evaluations
FROM school_feedbacks
WHERE status = 'needs_evaluation'
  AND kitchen_response IS NULL;

-- 2.5 Latest SPPG distribution announcement
SELECT title, content, created_at
FROM announcements
ORDER BY created_at DESC
LIMIT 1;

-- 2.6 Recent distribution and kitchen activity logs (top 4)
SELECT title, description, status, created_at
FROM activity_logs
ORDER BY created_at DESC
LIMIT 4;

-- 2.7 Today's scheduled primary menu
SELECT m.name, m.description, m.calories, m.protein, m.fiber
FROM menu_schedules ms
JOIN menus m ON m.id = ms.menu_id
WHERE ms.date = CURDATE()
LIMIT 1;

-- 2.8 School distribution list with search, status filtering, and pagination
SELECT s.id AS school_id,
       s.name AS school_name,
       s.npsn,
       s.pic_name,
       CONCAT(s.city, ' / ', s.district) AS coverage_area,
       d.portion_count,
       CONCAT(TIME_FORMAT(d.eta_start, '%H:%i'), ' - ', TIME_FORMAT(d.eta_end, '%H:%i')) AS delivery_eta,
       d.status AS distribution_status
FROM distributions d
JOIN schools s ON s.id = d.school_id
WHERE d.date = CURDATE()
  AND (s.name LIKE '%denpasar%' OR s.district LIKE '%denpasar%' OR s.city LIKE '%denpasar%')
ORDER BY s.name ASC
LIMIT 5 OFFSET 0;

-- 2.9 Total distribution row count (for pagination indicator "Showing 1-5 of N")
SELECT COUNT(*) AS total_rows
FROM distributions d
JOIN schools s ON s.id = d.school_id
WHERE d.date = CURDATE()
  AND (s.name LIKE '%denpasar%' OR s.district LIKE '%denpasar%' OR s.city LIKE '%denpasar%');

-- 2.10 Update delivery status (e.g., clicking "Sync" or "Mark Delivered")
UPDATE distributions
SET status = 'delivered'
WHERE school_id = 2 AND date = CURDATE();


-- ##########################################################################
-- 3. SPPG - MENU MANAGEMENT (menu.html)
-- ##########################################################################

-- 3.1 Menu overview KPIs
SELECT COUNT(*) AS total_active_menus,
       ROUND(AVG(calories)) AS avg_calories,
       SUM(category = 'special') AS special_diet_menus
FROM menus
WHERE is_active = 1;

-- 3.2 Category tab counters (All / Regular / Special / Backup)
SELECT category, COUNT(*) AS count
FROM menus
GROUP BY category;

-- 3.3 Menu cards with allergen tags, category filter, search, and pagination
SELECT m.id,
       m.name,
       m.description,
       m.category,
       m.calories,
       m.carbs,
       m.protein,
       m.fat,
       m.fiber,
       m.is_active,
       GROUP_CONCAT(
         CONCAT(IF(ma.type = 'free_from', 'Bebas ', 'Mengandung '), a.name)
         ORDER BY ma.type SEPARATOR ', '
       ) AS allergen_labels
FROM menus m
LEFT JOIN menu_allergens ma ON ma.menu_id = m.id
LEFT JOIN allergens a ON a.id = ma.allergen_id
WHERE (m.category = 'regular' OR 'regular' = 'all')
  AND (m.name LIKE '%ayam%' OR m.description LIKE '%ayam%')
GROUP BY m.id
ORDER BY m.id ASC
LIMIT 6 OFFSET 0;

-- 3.4 Create new menu package
INSERT INTO menus (name, description, category, calories, carbs, protein, fat, fiber, is_active)
VALUES ('Paket Ayam Bakar & Sayur Asem', 'Nasi putih, ayam bakar rempah, sayur asem, tempe goreng', 'regular', 620, 72, 26, 15, 6.5, 1);

-- 3.5 Update menu package
UPDATE menus
SET name = 'Paket Ayam Bakar & Sayur Asem Madu',
    category = 'regular',
    calories = 630
WHERE id = 1;

-- 3.6 Toggle menu active/inactive state
UPDATE menus
SET is_active = 0
WHERE id = 1;


-- ##########################################################################
-- 4. SPPG - STUDENT SELECTIONS & ALLERGIES (pilihan.html)
-- ##########################################################################

-- 4.1 Selection overview KPIs
SELECT (SELECT COUNT(*) FROM students WHERE recipient_status = 'active') AS total_active_students,
       (SELECT COUNT(*) FROM menu_selections ms JOIN menus m ON m.id = ms.menu_id WHERE m.category = 'regular') AS regular_package_orders,
       (SELECT COUNT(DISTINCT student_id) FROM student_allergies) AS special_diet_students,
       (SELECT COUNT(DISTINCT school_id) FROM students) AS connected_schools;

-- 4.2 Student selection table with school/class filter, search, and pagination
SELECT st.id AS student_id,
       st.nisn,
       st.name AS student_name,
       sc.name AS school_name,
       sc.city,
       st.class_name,
       m.name AS selected_menu_name,
       m.category AS menu_category,
       COALESCE(GROUP_CONCAT(sa.medical_notes SEPARATOR '; '), '-') AS allergy_notes,
       COALESCE(ms.consumption_status, 'active') AS consumption_status
FROM students st
JOIN schools sc ON sc.id = st.school_id
LEFT JOIN menu_schedules sch ON sch.date = CURDATE()
LEFT JOIN menu_selections ms ON ms.student_id = st.id AND ms.schedule_id = sch.id
LEFT JOIN menus m ON m.id = ms.menu_id
LEFT JOIN student_allergies sa ON sa.student_id = st.id
WHERE (st.name LIKE '%a%' OR st.nisn LIKE '%a%')
  AND (sc.id = 1 OR 1 = 1)
GROUP BY st.id, sc.name, sc.city, st.class_name, m.name, m.category, ms.consumption_status
ORDER BY st.name ASC
LIMIT 10 OFFSET 0;

-- 4.3 Change student selected menu
UPDATE menu_selections
SET menu_id = 3
WHERE student_id = 1 AND schedule_id = 1;

-- 4.4 Verify medical diet review
UPDATE student_allergies
SET verification_status = 'active',
    verified_by = 1
WHERE student_id = 3 AND allergen_id = 4;

UPDATE menu_selections
SET consumption_status = 'active'
WHERE student_id = 3 AND schedule_id = 1;

-- 4.5 Insert new student dietary restriction / allergy
INSERT INTO student_allergies (student_id, allergen_id, medical_notes, verification_status)
VALUES (1, 1, 'Alergi kacang tanah derajat sedang', 'medical_review');


-- ##########################################################################
-- 5. SPPG - FEEDBACK & REVIEWS (review.html)
-- ##########################################################################

-- 5.1 School satisfaction and food waste KPIs
SELECT ROUND(AVG(score), 2) AS satisfaction_score,
       COUNT(*) AS total_feedbacks_received,
       COUNT(DISTINCT school_id) AS reporting_schools_count,
       ROUND(AVG(waste_percentage), 1) AS avg_waste_percentage,
       ROUND(100 - AVG(waste_percentage), 1) AS avg_consumption_rate
FROM school_feedbacks;

-- 5.2 Delivery punctuality percentage for today
SELECT ROUND(100 * SUM(status = 'delivered') / COUNT(*), 1) AS on_time_delivery_rate
FROM distributions
WHERE date = CURDATE();

-- 5.3 Filterable school feedback list (All / Very Satisfied / Needs Evaluation)
SELECT f.id,
       s.name AS school_name,
       f.sender_name,
       f.position,
       f.score,
       f.content,
       f.container_temperature,
       f.waste_percentage,
       f.status,
       f.is_verified,
       f.kitchen_response,
       f.created_at
FROM school_feedbacks f
JOIN schools s ON s.id = f.school_id
WHERE (f.status = 'needs_evaluation' OR 'all' = 'all')
ORDER BY f.created_at DESC;

-- 5.4 Submit kitchen response to school evaluation
UPDATE school_feedbacks
SET kitchen_response = 'Jadwal armada wilayah Denpasar Timur dimajukan 15 menit mulai besok.'
WHERE id = 3;

-- 5.5 Star rating distribution (1 to 5 stars) in percentage
SELECT rating,
       COUNT(*) AS count,
       ROUND(100 * COUNT(*) / SUM(COUNT(*)) OVER (), 0) AS percentage
FROM student_reviews
GROUP BY rating
ORDER BY rating DESC;

-- 5.6 Top favorite menus according to students (Rating 4-5)
SELECT m.name AS menu_name,
       m.category,
       ROUND(100 * SUM(r.rating >= 4) / COUNT(*), 1) AS approval_rate,
       COUNT(*) AS review_count
FROM student_reviews r
JOIN menus m ON m.id = r.menu_id
GROUP BY m.id
ORDER BY approval_rate DESC, review_count DESC
LIMIT 3;

-- 5.7 Latest kitchen hygiene audit status
SELECT audit_type, result, audit_date, certificate_file
FROM kitchen_audits
WHERE audit_type = 'hygiene'
ORDER BY audit_date DESC
LIMIT 1;


-- ##########################################################################
-- 6. STUDENT PORTAL (dashboard, menu, history, profile)
-- ##########################################################################

-- 6.1 Student Dashboard: Daily pickup status
SELECT status AS pickup_status, pickup_time
FROM pickups
WHERE student_id = 1 AND date = CURDATE();

-- 6.2 Mark meal as picked up
UPDATE pickups
SET status = 'picked_up',
    pickup_time = NOW()
WHERE student_id = 1 AND date = CURDATE();

-- 6.3 Student KPI stats: Attendance rate, total reviews submitted, average rating
SELECT (SELECT ROUND(100 * SUM(status = 'picked_up') / COUNT(*)) FROM pickups WHERE student_id = 1) AS attendance_rate_percent,
       (SELECT COUNT(*) FROM student_reviews WHERE student_id = 1) AS total_reviews,
       (SELECT ROUND(AVG(rating), 1) FROM student_reviews WHERE student_id = 1) AS avg_rating;

-- 6.4 Menu Selection Page: Available menus excluding student-sensitive allergens
SELECT m.id, m.name, m.calories, m.carbs, m.protein, m.fat, m.fiber, m.description
FROM menus m
WHERE m.is_active = 1
  AND NOT EXISTS (
    SELECT 1
    FROM student_allergies sa
    JOIN menu_allergens ma ON ma.allergen_id = sa.allergen_id
    WHERE sa.student_id = 1
      AND ma.menu_id = m.id
      AND ma.type = 'contains'
  );

-- 6.5 Save / Update menu choice before daily cutoff (e.g. 20:00 previous day)
INSERT INTO menu_selections (student_id, schedule_id, menu_id)
SELECT 1, ms.id, 2
FROM menu_schedules ms
WHERE ms.id = 1
  AND NOW() < TIMESTAMP(DATE_SUB(ms.date, INTERVAL 1 DAY), ms.cutoff_time)
ON DUPLICATE KEY UPDATE menu_id = VALUES(menu_id);

-- 6.6 History & Reviews: Meal history with existing reviews
SELECT p.date,
       p.status AS pickup_status,
       p.pickup_time,
       m.id AS menu_id,
       m.name AS menu_name,
       r.id AS review_id,
       r.rating,
       r.comment,
       r.created_at AS reviewed_at
FROM pickups p
JOIN menu_selections ms ON ms.student_id = p.student_id
JOIN menu_schedules sch ON sch.id = ms.schedule_id AND sch.date = p.date
JOIN menus m ON m.id = ms.menu_id
LEFT JOIN student_reviews r ON r.student_id = p.student_id AND r.menu_id = m.id
WHERE p.student_id = 1
ORDER BY p.date DESC;

-- 6.7 Create / Update / Delete student meal review
INSERT INTO student_reviews (student_id, menu_id, rating, comment)
VALUES (1, 2, 5, 'Rasa gurih pas dan porsi sangat mengenyangkan.');

UPDATE student_reviews
SET rating = 4, comment = 'Cukup enak dan porsi cukup.'
WHERE id = 1 AND student_id = 1;

DELETE FROM student_reviews
WHERE id = 1 AND student_id = 1;

-- 6.8 Profile & Nutrition: Student medical allergies and notes
SELECT a.id AS allergen_id, a.name AS allergen_name, sa.medical_notes, sa.verification_status
FROM student_allergies sa
JOIN allergens a ON a.id = sa.allergen_id
WHERE sa.student_id = 1;

-- Save updated student allergies (batch replace)
DELETE FROM student_allergies WHERE student_id = 1;
INSERT INTO student_allergies (student_id, allergen_id, medical_notes, verification_status)
VALUES (1, 1, 'Sensitif produk olahan kacang tanah', 'medical_review'),
       (1, 2, 'Alergi udang laut', 'medical_review');


-- ##########################################################################
-- 7. ADMIN PORTAL (dashboard, students, schools, sppg, users)
-- ##########################################################################

-- 7.1 Dashboard KPIs
SELECT 
  (SELECT COUNT(*) FROM students) AS total_students,
  (SELECT COUNT(*) FROM schools) AS total_registered_schools,
  (SELECT COUNT(*) FROM sppg_units) AS total_sppg_units,
  (SELECT COUNT(*) FROM users WHERE role IN ('admin', 'sppg')) AS total_admin_and_staff;

-- 7.2 Dashboard: Recent registered schools table
SELECT s.id, s.npsn, s.name AS school_name, s.city, s.education_level, s.status,
       COUNT(st.id) AS total_enrolled_students
FROM schools s
LEFT JOIN students st ON st.school_id = s.id
GROUP BY s.id, s.name, s.city, s.status
ORDER BY s.id DESC
LIMIT 5;

-- 7.3 Dashboard: SPPG operational summary table
SELECT sp.id, sp.code, sp.name AS sppg_name, sp.region, sp.capacity, sp.status,
       COUNT(s.id) AS assigned_schools_count
FROM sppg_units sp
LEFT JOIN schools s ON s.sppg_id = sp.id
GROUP BY sp.id, sp.code, sp.name, sp.capacity, sp.status
ORDER BY sp.capacity DESC;

-- 7.4 Student Management (students.html): List with school, allergens, and search filters
SELECT st.id,
       st.nisn,
       st.name AS student_name,
       sc.name AS school_name,
       st.class_name,
       IF(COUNT(sa.allergen_id) > 0, 'Menu Khusus', 'Reguler') AS menu_preference,
       COALESCE(GROUP_CONCAT(a.name SEPARATOR ', '), '-') AS allergen_list,
       st.recipient_status AS status
FROM students st
JOIN schools sc ON sc.id = st.school_id
LEFT JOIN student_allergies sa ON sa.student_id = st.id
LEFT JOIN allergens a ON a.id = sa.allergen_id
WHERE (st.name LIKE '%alya%' OR st.nisn LIKE '%alya%')
  AND (sc.id = 1 OR 1 = 1)
  AND (st.recipient_status = 'active' OR 'all' = 'all')
GROUP BY st.id, sc.name
ORDER BY st.id DESC
LIMIT 10 OFFSET 0;

-- 7.5 Student Management: Add new student
INSERT INTO students (nisn, name, school_id, class_name, recipient_status)
VALUES ('0081234580', 'Dewi Lestari', 1, 'X MIPA 1', 'active');

-- 7.6 Student Management: Update student profile
UPDATE students
SET name = 'Dewi Lestari Putri',
    school_id = 1,
    class_name = 'X MIPA 2',
    recipient_status = 'active'
WHERE id = 1;

-- 7.7 Student Management: Delete student
DELETE FROM students WHERE id = 13;

-- 7.8 School Management (schools.html): List with estimated students and partner SPPG
SELECT s.id,
       s.npsn,
       s.name AS school_name,
       s.city,
       s.district,
       s.address,
       s.status,
       sp.name AS partner_sppg_name,
       COUNT(st.id) AS estimated_students
FROM schools s
JOIN sppg_units sp ON sp.id = s.sppg_id
LEFT JOIN students st ON st.school_id = s.id
WHERE (s.name LIKE '%denpasar%' OR s.npsn LIKE '%denpasar%')
  AND (s.city = 'Kota Denpasar' OR 'all' = 'all')
  AND (s.status = 'active' OR 'all' = 'all')
GROUP BY s.id, sp.name
ORDER BY s.id DESC
LIMIT 10 OFFSET 0;

-- 7.9 School Management: Add new school
INSERT INTO schools (npsn, name, education_level, district, city, address, pic_name, sppg_id, status)
VALUES ('50109999', 'SMA Negeri 9 Denpasar', 'SMA', 'Denpasar Selatan', 'Kota Denpasar', 'Jl. Tukad Pakerisan No. 50', 'I Wayan Oka', 1, 'active');

-- 7.10 School Management: Update school
UPDATE schools
SET name = 'SMA Negeri 9 Denpasar',
    education_level = 'SMA',
    district = 'Denpasar Selatan',
    city = 'Kota Denpasar',
    address = 'Jl. Tukad Pakerisan No. 50',
    pic_name = 'I Wayan Oka',
    sppg_id = 1,
    status = 'active'
WHERE id = 7;

-- 7.11 School Management: Delete school
DELETE FROM schools WHERE id = 7;

-- 7.12 SPPG Management (sppg.html): List with full capacity, contact person, and school count
SELECT sp.id,
       sp.code,
       sp.name AS sppg_name,
       sp.region,
       sp.address,
       sp.capacity,
       sp.contact_person,
       sp.phone,
       sp.status,
       COUNT(s.id) AS partner_schools_count
FROM sppg_units sp
LEFT JOIN schools s ON s.sppg_id = sp.id
WHERE (sp.name LIKE '%denpasar%' OR sp.code LIKE '%denpasar%')
  AND (sp.status = 'active' OR 'all' = 'all')
GROUP BY sp.id
ORDER BY sp.id DESC
LIMIT 10 OFFSET 0;

-- 7.13 SPPG Management: Add new SPPG unit
INSERT INTO sppg_units (code, name, region, address, capacity, contact_person, phone, status)
VALUES ('SPPG-DPS-05', 'SPPG Denpasar Timur 05', 'Kota Denpasar', 'Jl. Gatot Subroto Timur No. 88', 3000, 'I Putu Gede Astawa', '081234567895', 'active');

-- 7.14 SPPG Management: Update SPPG unit
UPDATE sppg_units
SET name = 'SPPG Denpasar Timur 05',
    region = 'Kota Denpasar',
    address = 'Jl. Gatot Subroto Timur No. 88',
    capacity = 3200,
    contact_person = 'I Putu Gede Astawa',
    phone = '081234567895',
    status = 'active'
WHERE id = 5;

-- 7.15 SPPG Management: Delete SPPG unit
DELETE FROM sppg_units WHERE id = 5;

-- 7.16 User Management (users.html): List users with roles and status
SELECT u.id,
       u.full_name,
       u.email,
       u.username,
       u.role,
       u.institution,
       u.status,
       u.created_at
FROM users u
WHERE (u.full_name LIKE '%admin%' OR u.username LIKE '%admin%' OR u.email LIKE '%admin%')
  AND (u.role = 'admin' OR 'all' = 'all')
  AND (u.status = 'active' OR 'all' = 'all')
ORDER BY u.id DESC
LIMIT 10 OFFSET 0;

-- 7.17 User Management: Add new user account
INSERT INTO users (username, email, password_hash, full_name, role, institution, status)
VALUES ('petugas_sppg2', 'sppg.bdg02@mbg.go.id', '$2b$10$hashpetugas2', 'Ni Made Rai', 'sppg', 'SPPG Badung Selatan 02', 'active');

-- 7.18 User Management: Update user account
UPDATE users
SET full_name = 'Ni Made Rai Rahayu',
    role = 'sppg',
    institution = 'SPPG Badung Selatan 02',
    status = 'active'
WHERE id = 2;

-- 7.19 User Management: Delete user account
DELETE FROM users WHERE id = 6;


-- ##########################################################################
-- 8. PUBLIC & LANDING PAGE (index.html)
-- ##########################################################################

-- 8.1 Public Impact Metrics (Landing page hero statistics)
SELECT 
  (SELECT COUNT(*) FROM students WHERE recipient_status = 'active') AS total_beneficiary_students,
  (SELECT COALESCE(SUM(portion_count), 0) FROM distributions WHERE status = 'delivered') AS total_meals_delivered,
  (SELECT COUNT(*) FROM schools WHERE status = 'active') AS partner_schools_count,
  (SELECT COUNT(*) FROM sppg_units WHERE status = 'active') AS active_sppg_units;
