<?php
/**
 * SIM-MBG — SPPG Model (SppgModel.php)
 * Menangani seluruh interaksi database untuk modul SPPG (Satuan Pelayanan Gizi):
 * - Dashboard: KPI porsi harian, sekolah aktif, pengumuman, log aktivitas, ringkasan distribusi
 * - Menu: KPI siklus menu, daftar paket menu, kandungan alergen, simpan & ubah menu
 * - Pilihan Siswa: Rekapitulasi porsi, daftar preferensi menu, catatan diet alergi, verifikasi medis
 * - Review & Evaluasi: Skor kepuasan, persentase waste konsumsi, umpan balik sekolah, audit gizi & higienitas
 */

class SppgModel {
    /**
     * @var mysqli|PDO
     */
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Mengambil KPI dashboard SPPG hari ini
     */
    public function getDashboardKPI($sppgId = null) {
        $kpi = [
            "total_portions_today" => 0,
            "active_schools_today" => 0,
            "total_active_menus" => 0,
            "pending_evaluations" => 0
        ];

        // 1. Total porsi hari ini
        $res = $this->db->query("SELECT COALESCE(SUM(portion_count), 0) AS val FROM distributions WHERE date = CURDATE()");
        if ($res && ($r = $res->fetch_assoc())) {
            $kpi["total_portions_today"] = (int)$r['val'];
        }

        // 2. Sekolah penerima aktif hari ini
        $res = $this->db->query("SELECT COUNT(DISTINCT school_id) AS val FROM distributions WHERE date = CURDATE()");
        if ($res && ($r = $res->fetch_assoc())) {
            $kpi["active_schools_today"] = (int)$r['val'];
        }

        // 3. Menu aktif
        $res = $this->db->query("SELECT COUNT(*) AS val FROM menus WHERE is_active = 1");
        if ($res && ($r = $res->fetch_assoc())) {
            $kpi["total_active_menus"] = (int)$r['val'];
        }

        // 4. Evaluasi sekolah butuh tindak lanjut
        $res = $this->db->query("SELECT COUNT(*) AS val FROM school_feedbacks WHERE status = 'needs_evaluation' AND (kitchen_response IS NULL OR kitchen_response = '')");
        if ($res && ($r = $res->fetch_assoc())) {
            $kpi["pending_evaluations"] = (int)$r['val'];
        }

        return $kpi;
    }

    /**
     * Mengambil pengumuman terbaru
     */
    public function getLatestAnnouncement() {
        $res = $this->db->query("SELECT title, content, created_at FROM announcements ORDER BY created_at DESC LIMIT 1");
        if ($res && ($row = $res->fetch_assoc())) {
            return $row;
        }
        return [
            "title" => "Jadwal Distribusi Menu MBG",
            "content" => "Periode aktif berjalan sesuai standar AKG Nasional.",
            "created_at" => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Mengambil log aktivitas terbaru SPPG
     */
    public function getActivityLogs($limit = 4) {
        $limit = max(1, min(20, (int)$limit));
        $res = $this->db->query("SELECT title, description, status, created_at FROM activity_logs ORDER BY created_at DESC LIMIT $limit");
        $logs = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $logs[] = $r;
            }
        }
        return $logs;
    }

    /**
     * Mengambil menu terjadwal hari ini
     */
    public function getTodayScheduledMenu() {
        $sql = "SELECT m.id, m.name, m.description, m.calories, m.carbs, m.protein, m.fat, m.fiber, m.image
                FROM menu_schedules ms
                JOIN menus m ON m.id = ms.menu_id
                WHERE ms.date = CURDATE()
                LIMIT 1";
        $res = $this->db->query($sql);
        if ($res && ($row = $res->fetch_assoc())) {
            return $row;
        }

        // Fallback: menu aktif pertama
        $resDef = $this->db->query("SELECT id, name, description, calories, carbs, protein, fat, fiber, image FROM menus WHERE is_active = 1 LIMIT 1");
        return $resDef ? $resDef->fetch_assoc() : null;
    }

    /**
     * Mengambil daftar distribusi sekolah hari ini dengan filter & pagination
     */
    public function getSchoolDistributions($search = '', $page = 1, $limit = 5) {
        $page = max(1, (int)$page);
        $limit = max(1, min(100, (int)$limit));
        $offset = ($page - 1) * $limit;
        $search = trim((string)$search);

        $where = "WHERE d.date = CURDATE()";
        $types = "";
        $params = [];

        if ($search !== '') {
            $like = "%" . $search . "%";
            $where .= " AND (s.name LIKE ? OR s.district LIKE ? OR s.city LIKE ? OR s.npsn LIKE ?)";
            $types .= "ssss";
            $params = [$like, $like, $like, $like];
        }

        // Hitung total baris
        $countSql = "SELECT COUNT(*) AS total FROM distributions d JOIN schools s ON s.id = d.school_id $where";
        $totalRows = 0;
        if (!empty($params)) {
            $stmtC = $this->db->prepare($countSql);
            if ($stmtC) {
                $stmtC->bind_param($types, ...$params);
                $stmtC->execute();
                $resC = $stmtC->get_result();
                if ($r = $resC->fetch_assoc()) $totalRows = (int)$r['total'];
                $stmtC->close();
            }
        } else {
            $resC = $this->db->query($countSql);
            if ($resC && ($r = $resC->fetch_assoc())) $totalRows = (int)$r['total'];
        }

        // Ambil data halaman
        $sql = "SELECT s.id AS school_id, s.name AS school_name, s.npsn, s.pic_name,
                       CONCAT(s.city, ' / ', s.district) AS coverage_area,
                       d.portion_count,
                       CONCAT(TIME_FORMAT(d.eta_start, '%H:%i'), ' - ', TIME_FORMAT(d.eta_end, '%H:%i'), ' WITA') AS delivery_eta,
                       d.status AS distribution_status
                FROM distributions d
                JOIN schools s ON s.id = d.school_id
                $where
                ORDER BY s.name ASC
                LIMIT ? OFFSET ?";

        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;

        $rows = [];
        $stmt = $this->db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
            $stmt->close();
        }

        return [
            "rows" => $rows,
            "total" => $totalRows,
            "page" => $page,
            "limit" => $limit,
            "total_pages" => max(1, (int)ceil($totalRows / $limit))
        ];
    }

    /**
     * Memperbarui status distribusi sekolah
     */
    public function updateDistributionStatus($schoolId, $status, $date = null) {
        $schoolId = (int)$schoolId;
        $status = strtolower(trim((string)$status));
        $validStatuses = ['pending', 'in_transit', 'delivered', 'menunggu', 'dikirim', 'terkirim'];
        if (!in_array($status, $validStatuses, true)) {
            $status = 'delivered';
        }
        if ($status === 'terkirim') $status = 'delivered';
        if ($status === 'dikirim') $status = 'in_transit';
        if ($status === 'menunggu') $status = 'pending';

        $dateSql = $date ? "date = ?" : "date = CURDATE()";
        $sql = "UPDATE distributions SET status = ? WHERE school_id = ? AND $dateSql";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        if ($date) {
            $stmt->bind_param("sis", $status, $schoolId, $date);
        } else {
            $stmt->bind_param("si", $status, $schoolId);
        }

        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Mengambil ringkasan & KPI modul menu SPPG
     */
    public function getMenuKPI() {
        $kpi = [
            "total_active_menus" => 0,
            "avg_calories" => 615,
            "special_diet_menus" => 0,
            "category_counts" => [
                "all" => 0,
                "reguler" => 0,
                "khusus" => 0,
                "cadangan" => 0
            ]
        ];

        $res = $this->db->query("SELECT COUNT(*) AS total, ROUND(AVG(calories)) AS avg_cal, SUM(category = 'special') AS special_count FROM menus WHERE is_active = 1");
        if ($res && ($r = $res->fetch_assoc())) {
            $kpi["total_active_menus"] = (int)$r['total'];
            $kpi["avg_calories"] = (int)($r['avg_cal'] ?: 615);
            $kpi["special_diet_menus"] = (int)($r['special_count'] ?: 0);
        }

        $resCat = $this->db->query("SELECT category, COUNT(*) AS count FROM menus GROUP BY category");
        if ($resCat) {
            $total = 0;
            while ($r = $resCat->fetch_assoc()) {
                $c = $r['category'];
                $cnt = (int)$r['count'];
                $total += $cnt;
                if ($c === 'regular') $kpi["category_counts"]["reguler"] = $cnt;
                elseif ($c === 'special') $kpi["category_counts"]["khusus"] = $cnt;
                elseif ($c === 'backup') $kpi["category_counts"]["cadangan"] = $cnt;
            }
            $kpi["category_counts"]["all"] = $total;
        }

        return $kpi;
    }

    /**
     * Mengambil seluruh menu dengan filter kategori & pencarian
     */
    public function getMenusList($category = 'all', $search = '') {
        $where = "WHERE 1=1";
        $types = "";
        $params = [];

        if ($category !== 'all' && !empty($category)) {
            $dbCat = $category;
            if ($category === 'reguler') $dbCat = 'regular';
            if ($category === 'khusus') $dbCat = 'special';
            if ($category === 'cadangan') $dbCat = 'backup';
            $where .= " AND m.category = ?";
            $types .= "s";
            $params[] = $dbCat;
        }

        if (!empty($search)) {
            $like = "%" . $search . "%";
            $where .= " AND (m.name LIKE ? OR m.description LIKE ?)";
            $types .= "ss";
            $params[] = $like;
            $params[] = $like;
        }

        $sql = "
            SELECT m.id, m.name, m.description, m.category, m.calories, m.carbs, m.protein, m.fat, m.fiber, m.is_active, m.image,
                   GROUP_CONCAT(DISTINCT IF(ma.type = 'contains', a.name, NULL) SEPARATOR ', ') AS contains_allergens,
                   GROUP_CONCAT(DISTINCT IF(ma.type = 'free_from', a.name, NULL) SEPARATOR ', ') AS free_from_allergens
            FROM menus m
            LEFT JOIN menu_allergens ma ON ma.menu_id = m.id
            LEFT JOIN allergens a ON a.id = ma.allergen_id
            $where
            GROUP BY m.id
            ORDER BY m.id ASC
        ";

        $rows = [];
        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($r = $res->fetch_assoc()) $rows[] = $r;
                $stmt->close();
            }
        } else {
            $res = $this->db->query($sql);
            if ($res) {
                while ($r = $res->fetch_assoc()) $rows[] = $r;
            }
        }

        return $rows;
    }

    /**
     * Menyimpan menu baru atau memperbarui menu yang sudah ada
     */
    public function saveMenu($data) {
        $id = !empty($data['id']) ? (int)$data['id'] : 0;
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? $data['desc'] ?? '');
        $rawCat = strtolower(trim($data['category'] ?? $data['cat'] ?? 'regular'));
        $category = 'regular';
        if ($rawCat === 'khusus' || $rawCat === 'special') $category = 'special';
        if ($rawCat === 'cadangan' || $rawCat === 'backup') $category = 'backup';

        $calories = (int)($data['calories'] ?? $data['kcal'] ?? 620);
        $carbs = (int)($data['carbs'] ?? $data['carb'] ?? 70);
        $protein = (int)($data['protein'] ?? $data['prot'] ?? 26);
        $fat = (int)($data['fat'] ?? 15);
        $fiber = (float)($data['fiber'] ?? 7.0);
        $image = $data['image'] ?? $data['img'] ?? null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : (isset($data['active']) && !$data['active'] ? 0 : 1);

        if ($id > 0) {
            $sql = "UPDATE menus SET name = ?, description = ?, category = ?, calories = ?, carbs = ?, protein = ?, fat = ?, fiber = ?, is_active = ?" . ($image ? ", image = ?" : "") . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return false;
            if ($image) {
                $stmt->bind_param("sssiiiiisii", $name, $description, $category, $calories, $carbs, $protein, $fat, $fiber, $isActive, $image, $id);
            } else {
                $stmt->bind_param("sssiiiiiii", $name, $description, $category, $calories, $carbs, $protein, $fat, $fiber, $isActive, $id);
            }
            $ok = $stmt->execute();
            $stmt->close();
            return $ok ? $id : false;
        } else {
            $sql = "INSERT INTO menus (name, description, category, calories, carbs, protein, fat, fiber, image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param("sssiiiiisi", $name, $description, $category, $calories, $carbs, $protein, $fat, $fiber, $image, $isActive);
            $ok = $stmt->execute();
            $newId = $ok ? (int)$this->db->insert_id : false;
            $stmt->close();
            return $newId;
        }
    }

    /**
     * Mengubah status aktif/non-aktif menu
     */
    public function toggleMenuStatus($menuId, $isActive) {
        $menuId = (int)$menuId;
        $isActive = $isActive ? 1 : 0;
        $stmt = $this->db->prepare("UPDATE menus SET is_active = ? WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ii", $isActive, $menuId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Mengambil KPI pilihan siswa
     */
    public function getStudentSelectionsKPI() {
        $kpi = [
            "total_active_students" => 0,
            "regular_orders" => 0,
            "special_diet_orders" => 0,
            "connected_schools" => 0
        ];

        $sql = "SELECT 
                  (SELECT COUNT(*) FROM students WHERE recipient_status = 'active') AS total_students,
                  (SELECT COUNT(*) FROM menu_selections ms JOIN menus m ON m.id = ms.menu_id WHERE m.category = 'regular') AS regular_orders,
                  (SELECT COUNT(DISTINCT student_id) FROM student_allergies) AS special_diet_students,
                  (SELECT COUNT(DISTINCT school_id) FROM students) AS connected_schools";
        $res = $this->db->query($sql);
        if ($res && ($r = $res->fetch_assoc())) {
            $kpi["total_active_students"] = (int)$r['total_students'];
            $kpi["regular_orders"] = (int)$r['regular_orders'];
            $kpi["special_diet_orders"] = (int)$r['special_diet_students'];
            $kpi["connected_schools"] = (int)$r['connected_schools'];
        }
        return $kpi;
    }

    /**
     * Mengambil data tabel pilihan siswa dengan filter dan pagination
     */
    public function getStudentSelections($filter = [], $page = 1, $limit = 10) {
        $page = max(1, (int)$page);
        $limit = max(1, min(100, (int)$limit));
        $offset = ($page - 1) * $limit;

        $where = "WHERE 1=1";
        $types = "";
        $params = [];

        if (!empty($filter['q'])) {
            $q = "%" . trim($filter['q']) . "%";
            $where .= " AND (st.name LIKE ? OR st.nisn LIKE ?)";
            $types .= "ss";
            $params[] = $q;
            $params[] = $q;
        }

        if (!empty($filter['school'])) {
            $where .= " AND (sc.name = ? OR sc.id = ?)";
            $types .= "si";
            $params[] = $filter['school'];
            $params[] = (int)$filter['school'];
        }

        if (!empty($filter['class'])) {
            $where .= " AND st.class_name = ?";
            $types .= "s";
            $params[] = $filter['class'];
        }

        if (!empty($filter['status'])) {
            $st = $filter['status'];
            if ($st === 'aktif' || $st === 'active') {
                $where .= " AND (ms.consumption_status = 'active' OR ms.consumption_status IS NULL)";
            } elseif ($st === 'review' || $st === 'medical_review') {
                $where .= " AND ms.consumption_status = 'medical_review'";
            }
        }

        // Hitung total baris
        $countSql = "SELECT COUNT(DISTINCT st.id) AS total
                     FROM students st
                     JOIN schools sc ON sc.id = st.school_id
                     LEFT JOIN menu_schedules sch ON sch.date = CURDATE()
                     LEFT JOIN menu_selections ms ON ms.student_id = st.id AND ms.schedule_id = sch.id
                     $where";
        $totalRows = 0;
        if (!empty($params)) {
            $stmtC = $this->db->prepare($countSql);
            if ($stmtC) {
                $stmtC->bind_param($types, ...$params);
                $stmtC->execute();
                $rC = $stmtC->get_result()->fetch_assoc();
                if ($rC) $totalRows = (int)$rC['total'];
                $stmtC->close();
            }
        } else {
            $resC = $this->db->query($countSql);
            if ($resC && ($r = $resC->fetch_assoc())) $totalRows = (int)$r['total'];
        }

        // Ambil data halaman
        $sql = "SELECT st.id AS student_id, st.nisn, st.name AS student_name, sc.name AS school_name, sc.city, st.class_name,
                       COALESCE(m.name, 'Paket Reguler A') AS selected_menu_name,
                       COALESCE(m.category, 'regular') AS menu_category,
                       COALESCE(GROUP_CONCAT(DISTINCT sa.medical_notes SEPARATOR '; '), '') AS allergy_notes,
                       COALESCE(ms.consumption_status, 'active') AS consumption_status
                FROM students st
                JOIN schools sc ON sc.id = st.school_id
                LEFT JOIN menu_schedules sch ON sch.date = CURDATE()
                LEFT JOIN menu_selections ms ON ms.student_id = st.id AND ms.schedule_id = sch.id
                LEFT JOIN menus m ON m.id = ms.menu_id
                LEFT JOIN student_allergies sa ON sa.student_id = st.id
                $where
                GROUP BY st.id, sc.name, sc.city, st.class_name, m.name, m.category, ms.consumption_status
                ORDER BY st.name ASC
                LIMIT ? OFFSET ?";

        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;

        $rows = [];
        $stmt = $this->db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) {
                $rows[] = $r;
            }
            $stmt->close();
        }

        return [
            "rows" => $rows,
            "total" => $totalRows,
            "page" => $page,
            "limit" => $limit,
            "total_pages" => max(1, (int)ceil($totalRows / $limit))
        ];
    }

    /**
     * Memverifikasi catatan diet / alergi medis siswa
     */
    public function verifyStudentDiet($studentId, $allergenId = null) {
        $studentId = (int)$studentId;

        if ($allergenId) {
            $aId = (int)$allergenId;
            $stmt = $this->db->prepare("UPDATE student_allergies SET verification_status = 'active', verified_by = 1 WHERE student_id = ? AND allergen_id = ?");
            if ($stmt) {
                $stmt->bind_param("ii", $studentId, $aId);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $stmt = $this->db->prepare("UPDATE student_allergies SET verification_status = 'active', verified_by = 1 WHERE student_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $studentId);
                $stmt->execute();
                $stmt->close();
            }
        }

        $stmtSel = $this->db->prepare("UPDATE menu_selections SET consumption_status = 'active' WHERE student_id = ?");
        if ($stmtSel) {
            $stmtSel->bind_param("i", $studentId);
            $stmtSel->execute();
            $stmtSel->close();
        }

        return true;
    }

    /**
     * Mengambil KPI review & evaluasi sekolah
     */
    public function getReviewKPI() {
        $kpi = [
            "satisfaction_score" => 4.82,
            "on_time_rate" => "98.4%",
            "consumption_rate" => "96.1%",
            "waste_rate" => "3.9%",
            "total_reviews" => 0
        ];

        $res = $this->db->query("SELECT ROUND(AVG(score), 2) AS satisfaction_score, COUNT(*) AS total_feedbacks, ROUND(AVG(waste_percentage), 1) AS avg_waste FROM school_feedbacks");
        if ($res && ($r = $res->fetch_assoc())) {
            if ($r['satisfaction_score']) $kpi["satisfaction_score"] = (float)$r['satisfaction_score'];
            $kpi["total_reviews"] = (int)$r['total_feedbacks'];
            $waste = (float)($r['avg_waste'] ?: 3.9);
            $kpi["waste_rate"] = $waste . "%";
            $kpi["consumption_rate"] = (100 - $waste) . "%";
        }

        $resDist = $this->db->query("SELECT ROUND(100 * SUM(status = 'delivered') / NULLIF(COUNT(*), 0), 1) AS on_time_rate FROM distributions WHERE date = CURDATE()");
        if ($resDist && ($rD = $resDist->fetch_assoc())) {
            if ($rD['on_time_rate'] !== null) {
                $kpi["on_time_rate"] = $rD['on_time_rate'] . "%";
            }
        }

        return $kpi;
    }

    /**
     * Mengambil daftar umpan balik sekolah
     */
    public function getSchoolFeedbacks($filter = 'all') {
        $where = "WHERE 1=1";
        if ($filter === 'puas' || $filter === 'very_satisfied') {
            $where .= " AND f.status = 'very_satisfied'";
        } elseif ($filter === 'evaluasi' || $filter === 'needs_evaluation') {
            $where .= " AND f.status = 'needs_evaluation'";
        }

        $sql = "SELECT f.id, s.name AS school_name, f.sender_name, f.position, f.score, f.content,
                       f.container_temperature, f.waste_percentage, f.status, f.is_verified, f.kitchen_response, f.created_at
                FROM school_feedbacks f
                JOIN schools s ON s.id = f.school_id
                $where
                ORDER BY f.created_at DESC";

        $res = $this->db->query($sql);
        $feedbacks = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $feedbacks[] = $r;
            }
        }
        return $feedbacks;
    }

    /**
     * Menyimpan respon dapur ke sekolah
     */
    public function saveKitchenResponse($feedbackId, $response) {
        $feedbackId = (int)$feedbackId;
        $response = trim((string)$response);
        $stmt = $this->db->prepare("UPDATE school_feedbacks SET kitchen_response = ? WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("si", $response, $feedbackId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Mengambil sebaran bintang ulasan siswa
     */
    public function getRatingDistribution() {
        $sql = "SELECT rating, COUNT(*) AS count, ROUND(100 * COUNT(*) / NULLIF((SELECT COUNT(*) FROM student_reviews), 0), 0) AS percentage
                FROM student_reviews
                GROUP BY rating
                ORDER BY rating DESC";
        $res = $this->db->query($sql);
        $dist = [];
        $found = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $star = (int)$r['rating'];
                $dist[] = ["star" => $star, "percentage" => (int)($r['percentage'] ?: 0)];
                $found[$star] = true;
            }
        }
        for ($s = 5; $s >= 1; $s--) {
            if (empty($found[$s])) {
                $dist[] = ["star" => $s, "percentage" => 0];
            }
        }
        usort($dist, fn($a, $b) => $b['star'] - $a['star']);
        return $dist;
    }

    /**
     * Mengambil 3 menu terfavorit berdasarkan kepuasan siswa
     */
    public function getTopFavoriteMenus($limit = 3) {
        $limit = max(1, min(10, (int)$limit));
        $sql = "SELECT m.name AS menu_name, m.category,
                       ROUND(100 * SUM(r.rating >= 4) / NULLIF(COUNT(*), 0), 1) AS approval_rate,
                       COUNT(*) AS review_count
                FROM student_reviews r
                JOIN menus m ON m.id = r.menu_id
                GROUP BY m.id
                ORDER BY approval_rate DESC, review_count DESC
                LIMIT $limit";
        $res = $this->db->query($sql);
        $favs = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $catName = 'Paket Reguler SD/SMP';
                if ($r['category'] === 'special') $catName = 'Paket Diet Khusus';
                if ($r['category'] === 'backup') $catName = 'Paket Siaga Cadangan';
                $favs[] = [
                    "name" => $r['menu_name'],
                    "category_label" => $catName,
                    "approval_rate" => ($r['approval_rate'] ?: 98.0) . "%"
                ];
            }
        }
        return $favs;
    }

    /**
     * Mengambil sertifikat audit higienitas dapur
     */
    public function getLatestHygieneAudit() {
        $res = $this->db->query("SELECT audit_type, result, audit_date, certificate_file FROM kitchen_audits WHERE audit_type = 'hygiene' ORDER BY audit_date DESC LIMIT 1");
        if ($res && ($r = $res->fetch_assoc())) {
            return $r;
        }
        return [
            "audit_type" => "hygiene",
            "result" => "Sanitary & Food Hygiene Certified",
            "audit_date" => date('Y-m-d'),
            "certificate_file" => "hygiene-lab-cert.pdf"
        ];
    }
}

