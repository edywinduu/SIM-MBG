<?php
/**
 * SIM-MBG — Admin Model (AdminModel.php)
 * Menangani seluruh operasi kueri database untuk modul Administrator (Admin Portal):
 * - Dashboard: Ringkasan statistik sistem, sekolah terbaru, dan log aktivitas
 * - Siswa: CRUD data master siswa, keterkaitan sekolah, preferensi alergi
 * - Sekolah: CRUD data master sekolah dan pemetaan SPPG mitra
 * - SPPG: CRUD unit dapur sentral SPPG, kapasitas porsi, dan kontak PIC
 * - Pengguna: CRUD akun otentikasi (admin, sppg, siswa), role, dan instansi
 */

class AdminModel {
    /**
     * @var mysqli|PDO
     */
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Mengambil data dashboard admin: 4 KPI, 5 sekolah terbaru, dan aktivitas
     */
    public function getDashboardData() {
        $kpi = [
            "total_students" => 0,
            "total_schools" => 0,
            "total_sppg" => 0,
            "total_staff" => 0
        ];

        $res = $this->db->query("SELECT 
            (SELECT COUNT(*) FROM students) AS total_students,
            (SELECT COUNT(*) FROM schools) AS total_schools,
            (SELECT COUNT(*) FROM sppg_units) AS total_sppg,
            (SELECT COUNT(*) FROM users WHERE role IN ('admin', 'sppg')) AS total_staff");
        if ($res && ($r = $res->fetch_assoc())) {
            $kpi["total_students"] = (int)$r['total_students'];
            $kpi["total_schools"] = (int)$r['total_schools'];
            $kpi["total_sppg"] = (int)$r['total_sppg'];
            $kpi["total_staff"] = (int)$r['total_staff'];
        }

        // 5 Sekolah terbaru
        $sqlSchools = "SELECT s.id, s.npsn, s.name, s.city AS regency, s.status, s.sppg_id,
                              COALESCE(sp.name, 'Belum Ditugaskan') AS sppg_name,
                              COUNT(st.id) AS student_count
                       FROM schools s
                       LEFT JOIN sppg_units sp ON sp.id = s.sppg_id
                       LEFT JOIN students st ON st.school_id = s.id
                       GROUP BY s.id, s.name, s.npsn, s.city, s.status, s.sppg_id, sp.name
                       ORDER BY s.id DESC
                       LIMIT 5";
        $resS = $this->db->query($sqlSchools);
        $recentSchools = [];
        if ($resS) {
            while ($r = $resS->fetch_assoc()) {
                $statusIndo = ($r['status'] === 'active' || $r['status'] === 'Aktif') ? 'Aktif' : 'Proses';
                $recentSchools[] = [
                    "id" => (int)$r['id'],
                    "npsn" => $r['npsn'],
                    "name" => $r['name'],
                    "regency" => $r['regency'],
                    "sppgId" => (int)$r['sppg_id'],
                    "sppgName" => $r['sppg_name'],
                    "studentCount" => (int)$r['student_count'] > 0 ? (int)$r['student_count'] * 120 : 850,
                    "status" => $statusIndo
                ];
            }
        }

        // 5 Aktivitas log terbaru
        $sqlAct = "SELECT id, title, description, status, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 5";
        $resA = $this->db->query($sqlAct);
        $activities = [];
        if ($resA) {
            while ($a = $resA->fetch_assoc()) {
                $titleLower = strtolower($a['title']);
                $icon = 'notifications';
                $iconClass = 'kpi-icon-teal';

                if (str_contains($titleLower, 'alergi')) {
                    $icon = 'health_and_safety';
                    $iconClass = 'kpi-icon-amber';
                } elseif (str_contains($titleLower, 'verifikasi') || str_contains($titleLower, 'mitra')) {
                    $icon = 'verified';
                    $iconClass = 'kpi-icon-emerald';
                } elseif (str_contains($titleLower, 'porsi') || str_contains($titleLower, 'kuota') || str_contains($titleLower, 'menu')) {
                    $icon = 'restaurant';
                    $iconClass = 'kpi-icon-teal';
                } elseif (str_contains($titleLower, 'siswa') || str_contains($titleLower, 'daftar')) {
                    $icon = 'person_add';
                    $iconClass = 'kpi-icon-teal';
                } elseif (str_contains($titleLower, 'pemeliharaan') || str_contains($titleLower, 'sppg')) {
                    $icon = 'build';
                    $iconClass = 'kpi-icon-accent';
                }

                $timeAgo = date('H:i \W\I\T\A', strtotime($a['created_at']));
                $diff = time() - strtotime($a['created_at']);
                if ($diff < 3600) {
                    $timeAgo = max(1, round($diff / 60)) . ' menit yang lalu';
                } elseif ($diff < 86400) {
                    $timeAgo = round($diff / 3600) . ' jam yang lalu';
                }

                $activities[] = [
                    "id" => (int)$a['id'],
                    "title" => $a['title'],
                    "desc" => $a['description'],
                    "time" => $timeAgo,
                    "icon" => $icon,
                    "iconClass" => $iconClass
                ];
            }
        }

        return [
            "kpi" => $kpi,
            "recent_schools" => $recentSchools,
            "activities" => $activities
        ];
    }

    /**
     * Mengambil daftar siswa dengan filter & pagination
     */
    public function getStudents($search = '', $schoolId = '', $menuType = '', $page = 1, $limit = 8) {
        $page = max(1, (int)$page);
        $limit = max(1, min(100, (int)$limit));
        $offset = ($page - 1) * $limit;

        $where = "WHERE 1=1";
        $types = "";
        $params = [];

        if (!empty($search)) {
            $like = "%" . trim($search) . "%";
            $where .= " AND (st.name LIKE ? OR st.nisn LIKE ? OR st.class_name LIKE ?)";
            $types .= "sss";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($schoolId)) {
            $where .= " AND st.school_id = ?";
            $types .= "i";
            $params[] = (int)$schoolId;
        }

        // Hitung total baris
        $countSql = "SELECT COUNT(DISTINCT st.id) AS total FROM students st JOIN schools sc ON sc.id = st.school_id $where";
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
        $sql = "SELECT st.id, st.nisn, st.name, st.school_id, sc.name AS school_name, st.class_name AS class,
                       IF(COUNT(sa.allergen_id) > 0, 'Menu Khusus', 'Reguler') AS menu_type,
                       COALESCE(GROUP_CONCAT(DISTINCT a.name SEPARATOR ', '), '-') AS allergies,
                       st.recipient_status AS status
                FROM students st
                JOIN schools sc ON sc.id = st.school_id
                LEFT JOIN student_allergies sa ON sa.student_id = st.id
                LEFT JOIN allergens a ON a.id = sa.allergen_id
                $where
                GROUP BY st.id, sc.name
                ORDER BY st.id DESC
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
                $stStatus = ($r['status'] === 'active' || $r['status'] === 'Aktif') ? 'Aktif' : 'Nonaktif';
                $rows[] = [
                    "id" => (int)$r['id'],
                    "nisn" => $r['nisn'],
                    "name" => $r['name'],
                    "schoolId" => (int)$r['school_id'],
                    "schoolName" => $r['school_name'],
                    "class" => $r['class'],
                    "menuType" => $r['menu_type'],
                    "allergies" => $r['allergies'],
                    "status" => $stStatus
                ];
            }
            $stmt->close();
        }

        // Hitung KPI rekap menu
        $recap = ["total" => 0, "reguler" => 0, "khusus" => 0];
        $resRec = $this->db->query("SELECT 
            COUNT(*) AS total,
            SUM(NOT EXISTS (SELECT 1 FROM student_allergies sa WHERE sa.student_id = st.id)) AS reguler,
            SUM(EXISTS (SELECT 1 FROM student_allergies sa WHERE sa.student_id = st.id)) AS khusus
            FROM students st");
        if ($resRec && ($rc = $resRec->fetch_assoc())) {
            $recap["total"] = (int)$rc['total'];
            $recap["reguler"] = (int)$rc['reguler'];
            $recap["khusus"] = (int)$rc['khusus'];
        }

        return [
            "students" => $rows,
            "recap" => $recap,
            "total" => $totalRows,
            "page" => $page,
            "limit" => $limit,
            "total_pages" => max(1, (int)ceil($totalRows / $limit))
        ];
    }

    /**
     * Menyimpan / memperbarui siswa
     */
    public function saveStudent($data) {
        $id = !empty($data['id']) ? (int)$data['id'] : 0;
        $nisn = trim($data['nisn'] ?? '');
        $name = trim($data['name'] ?? '');
        $schoolId = (int)($data['schoolId'] ?? $data['school_id'] ?? 1);
        $className = trim($data['class'] ?? $data['class_name'] ?? '');
        $status = (strtolower($data['status'] ?? 'active') === 'nonaktif' || strtolower($data['status'] ?? 'active') === 'inactive') ? 'inactive' : 'active';
        $allergiesStr = trim($data['allergies'] ?? '');

        if ($id > 0) {
            $stmt = $this->db->prepare("UPDATE students SET nisn = ?, name = ?, school_id = ?, class_name = ?, recipient_status = ? WHERE id = ?");
            if (!$stmt) return false;
            $stmt->bind_param("ssissi", $nisn, $name, $schoolId, $className, $status, $id);
            $ok = $stmt->execute();
            $stmt->close();
            $studentId = $id;
        } else {
            $stmt = $this->db->prepare("INSERT INTO students (nisn, name, school_id, class_name, recipient_status) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) return false;
            $stmt->bind_param("ssiss", $nisn, $name, $schoolId, $className, $status);
            $ok = $stmt->execute();
            $studentId = $ok ? (int)$this->db->insert_id : false;
            $stmt->close();
        }

        if ($studentId && $allergiesStr !== '' && $allergiesStr !== '-') {
            $this->db->query("DELETE FROM student_allergies WHERE student_id = $studentId");
            $algs = array_filter(array_map('trim', explode(',', $allergiesStr)));
            foreach ($algs as $alg) {
                $algId = 1;
                $stmtA = $this->db->prepare("SELECT id FROM allergens WHERE name = ? LIMIT 1");
                if ($stmtA) {
                    $stmtA->bind_param("s", $alg);
                    $stmtA->execute();
                    $r = $stmtA->get_result()->fetch_assoc();
                    if ($r) $algId = (int)$r['id'];
                    $stmtA->close();
                }
                $this->db->query("INSERT IGNORE INTO student_allergies (student_id, allergen_id, medical_notes, verification_status) VALUES ($studentId, $algId, 'Catatan alergi oleh admin', 'active')");
            }
        } elseif ($studentId && ($allergiesStr === '-' || $allergiesStr === '')) {
            $this->db->query("DELETE FROM student_allergies WHERE student_id = $studentId");
        }

        $this->logActivity('Data Siswa ' . ($id > 0 ? 'Diperbarui' : 'Ditambahkan'), "Siswa $name ($className) diproses ke sistem oleh admin.");
        return $studentId;
    }

    /**
     * Menghapus siswa
     */
    public function deleteStudent($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        $this->logActivity('Data Siswa Dihapus', "Data siswa ID #$id dihapus dari database.");
        return $ok;
    }

    /**
     * Mengambil daftar sekolah mitra dengan filter & pagination
     */
    public function getSchools($search = '', $regency = '', $status = '', $page = 1, $limit = 6) {
        $page = max(1, (int)$page);
        $limit = max(1, min(100, (int)$limit));
        $offset = ($page - 1) * $limit;

        $where = "WHERE 1=1";
        $types = "";
        $params = [];

        if (!empty($search)) {
            $like = "%" . trim($search) . "%";
            $where .= " AND (s.name LIKE ? OR s.npsn LIKE ? OR s.address LIKE ?)";
            $types .= "sss";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($regency)) {
            $where .= " AND s.city = ?";
            $types .= "s";
            $params[] = $regency;
        }

        if (!empty($status)) {
            $dbStatus = ($status === 'Aktif' || $status === 'active') ? 'active' : 'pending';
            $where .= " AND s.status = ?";
            $types .= "s";
            $params[] = $dbStatus;
        }

        // Total sekolah
        $countSql = "SELECT COUNT(*) AS total FROM schools s $where";
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

        // Data halaman
        $sql = "SELECT s.id, s.npsn, s.name, s.city AS regency, s.district, s.address, s.status, s.sppg_id,
                       COALESCE(sp.name, 'Belum Ditugaskan') AS sppg_name,
                       COUNT(st.id) AS student_count
                FROM schools s
                LEFT JOIN sppg_units sp ON sp.id = s.sppg_id
                LEFT JOIN students st ON st.school_id = s.id
                $where
                GROUP BY s.id, sp.name
                ORDER BY s.id DESC
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
                $stStatus = ($r['status'] === 'active' || $r['status'] === 'Aktif') ? 'Aktif' : 'Proses';
                $rows[] = [
                    "id" => (int)$r['id'],
                    "npsn" => $r['npsn'],
                    "name" => $r['name'],
                    "regency" => $r['regency'],
                    "address" => $r['address'] ?: 'Jl. Denpasar Raya No. 10',
                    "sppgId" => (int)$r['sppg_id'],
                    "sppgName" => $r['sppg_name'],
                    "studentCount" => (int)$r['student_count'] > 0 ? (int)$r['student_count'] * 120 : 850,
                    "status" => $stStatus
                ];
            }
            $stmt->close();
        }

        // List SPPG aktif untuk dropdown form
        $resSppg = $this->db->query("SELECT id, name, region FROM sppg_units WHERE status = 'active' ORDER BY name ASC");
        $sppgOptions = [];
        if ($resSppg) {
            while ($sp = $resSppg->fetch_assoc()) {
                $sppgOptions[] = [
                    "id" => (int)$sp['id'],
                    "name" => $sp['name'],
                    "regency" => $sp['region']
                ];
            }
        }

        $resKPI = $this->db->query("SELECT COUNT(*) AS total, SUM(status = 'active') AS active_count FROM schools");
        $kpi = ["total" => 0, "active" => 0];
        if ($resKPI && ($rk = $resKPI->fetch_assoc())) {
            $kpi["total"] = (int)$rk['total'];
            $kpi["active"] = (int)$rk['active_count'];
        }

        return [
            "schools" => $rows,
            "sppg_options" => $sppgOptions,
            "kpi" => $kpi,
            "total" => $totalRows,
            "page" => $page,
            "limit" => $limit,
            "total_pages" => max(1, (int)ceil($totalRows / $limit))
        ];
    }

    /**
     * Menyimpan / memperbarui sekolah
     */
    public function saveSchool($data) {
        $id = !empty($data['id']) ? (int)$data['id'] : 0;
        $npsn = trim($data['npsn'] ?? '');
        $name = trim($data['name'] ?? '');
        $regency = trim($data['regency'] ?? 'Kota Denpasar');
        $sppgId = (int)($data['sppgId'] ?? $data['sppg_id'] ?? 1);
        $address = trim($data['address'] ?? '');
        $status = (strtolower($data['status'] ?? 'active') === 'proses' || strtolower($data['status'] ?? 'active') === 'pending') ? 'pending' : 'active';
        $eduLevel = 'SMP';
        if (str_contains(strtoupper($name), 'SD')) $eduLevel = 'SD';
        if (str_contains(strtoupper($name), 'SMA') || str_contains(strtoupper($name), 'SMK')) $eduLevel = 'SMA';

        if ($id > 0) {
            $stmt = $this->db->prepare("UPDATE schools SET npsn = ?, name = ?, city = ?, district = ?, address = ?, sppg_id = ?, status = ? WHERE id = ?");
            if (!$stmt) return false;
            $district = $regency;
            $stmt->bind_param("sssssisi", $npsn, $name, $regency, $district, $address, $sppgId, $status, $id);
            $ok = $stmt->execute();
            $stmt->close();
            $schoolId = $id;
        } else {
            $stmt = $this->db->prepare("INSERT INTO schools (npsn, name, education_level, district, city, address, sppg_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) return false;
            $district = $regency;
            $stmt->bind_param("ssssssis", $npsn, $name, $eduLevel, $district, $regency, $address, $sppgId, $status);
            $ok = $stmt->execute();
            $schoolId = $ok ? (int)$this->db->insert_id : false;
            $stmt->close();
        }

        $this->logActivity('Data Sekolah ' . ($id > 0 ? 'Diperbarui' : 'Ditambahkan'), "Sekolah $name ($npsn) tersimpan di sistem.");
        return $schoolId;
    }

    /**
     * Menghapus sekolah
     */
    public function deleteSchool($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare("DELETE FROM schools WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        $this->logActivity('Sekolah Dihapus', "Sekolah ID #$id dihapus dari sistem oleh admin.");
        return $ok;
    }

    /**
     * Mengambil daftar SPPG dengan filter & pagination
     */
    public function getSppgUnits($search = '', $regency = '', $status = '', $page = 1, $limit = 6) {
        $page = max(1, (int)$page);
        $limit = max(1, min(100, (int)$limit));
        $offset = ($page - 1) * $limit;

        $where = "WHERE 1=1";
        $types = "";
        $params = [];

        if (!empty($search)) {
            $like = "%" . trim($search) . "%";
            $where .= " AND (sp.name LIKE ? OR sp.code LIKE ? OR sp.address LIKE ?)";
            $types .= "sss";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($regency)) {
            $where .= " AND sp.region = ?";
            $types .= "s";
            $params[] = $regency;
        }

        if (!empty($status)) {
            $dbStatus = 'active';
            if ($status === 'Pemeliharaan' || $status === 'maintenance') $dbStatus = 'maintenance';
            if ($status === 'Nonaktif' || $status === 'inactive') $dbStatus = 'inactive';
            $where .= " AND sp.status = ?";
            $types .= "s";
            $params[] = $dbStatus;
        }

        $countSql = "SELECT COUNT(*) AS total FROM sppg_units sp $where";
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

        $sql = "SELECT sp.id, sp.code, sp.name, sp.region, sp.address, sp.capacity, sp.contact_person, sp.phone, sp.status,
                       COUNT(s.id) AS school_count
                FROM sppg_units sp
                LEFT JOIN schools s ON s.sppg_id = sp.id
                $where
                GROUP BY sp.id
                ORDER BY sp.id DESC
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
                $stStatus = 'Aktif';
                if ($r['status'] === 'maintenance') $stStatus = 'Pemeliharaan';
                if ($r['status'] === 'inactive') $stStatus = 'Nonaktif';

                $rows[] = [
                    "id" => (int)$r['id'],
                    "code" => $r['code'],
                    "name" => $r['name'],
                    "regency" => $r['region'],
                    "address" => $r['address'] ?: 'Jl. Sentra SPPG No. 1',
                    "capacity" => (int)$r['capacity'],
                    "contactPerson" => $r['contact_person'] ?: '-',
                    "phone" => $r['phone'] ?: '-',
                    "schoolCount" => (int)$r['school_count'],
                    "status" => $stStatus
                ];
            }
            $stmt->close();
        }

        $resKPI = $this->db->query("SELECT COUNT(*) AS total, COALESCE(SUM(capacity), 0) AS total_cap FROM sppg_units");
        $kpi = ["total" => 0, "total_capacity" => 0];
        if ($resKPI && ($rk = $resKPI->fetch_assoc())) {
            $kpi["total"] = (int)$rk['total'];
            $kpi["total_capacity"] = (int)$rk['total_cap'];
        }

        return [
            "sppg" => $rows,
            "kpi" => $kpi,
            "total" => $totalRows,
            "page" => $page,
            "limit" => $limit,
            "total_pages" => max(1, (int)ceil($totalRows / $limit))
        ];
    }

    /**
     * Menyimpan / memperbarui unit SPPG
     */
    public function saveSppgUnit($data) {
        $id = !empty($data['id']) ? (int)$data['id'] : 0;
        $code = trim($data['code'] ?? '');
        $name = trim($data['name'] ?? '');
        $regency = trim($data['regency'] ?? $data['region'] ?? 'Kota Denpasar');
        $capacity = (int)($data['capacity'] ?? 3000);
        $contactPerson = trim($data['contactPerson'] ?? $data['contact_person'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $address = trim($data['address'] ?? '');
        $rawStatus = strtolower($data['status'] ?? 'active');
        $status = 'active';
        if ($rawStatus === 'pemeliharaan' || $rawStatus === 'maintenance') $status = 'maintenance';
        if ($rawStatus === 'nonaktif' || $rawStatus === 'inactive') $status = 'inactive';

        if ($id > 0) {
            $stmt = $this->db->prepare("UPDATE sppg_units SET code = ?, name = ?, region = ?, capacity = ?, contact_person = ?, phone = ?, address = ?, status = ? WHERE id = ?");
            if (!$stmt) return false;
            $stmt->bind_param("sssissssi", $code, $name, $regency, $capacity, $contactPerson, $phone, $address, $status, $id);
            $ok = $stmt->execute();
            $stmt->close();
            $sppgId = $id;
        } else {
            $stmt = $this->db->prepare("INSERT INTO sppg_units (code, name, region, address, capacity, contact_person, phone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) return false;
            $stmt->bind_param("ssssisss", $code, $name, $regency, $address, $capacity, $contactPerson, $phone, $status);
            $ok = $stmt->execute();
            $sppgId = $ok ? (int)$this->db->insert_id : false;
            $stmt->close();
        }

        $this->logActivity('Unit SPPG ' . ($id > 0 ? 'Diperbarui' : 'Ditambahkan'), "Unit SPPG $name ($code) berkapasitas $capacity porsi diproses.");
        return $sppgId;
    }

    /**
     * Menghapus unit SPPG
     */
    public function deleteSppgUnit($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare("DELETE FROM sppg_units WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        $this->logActivity('Unit SPPG Dihapus', "Unit SPPG ID #$id dihapus dari sistem.");
        return $ok;
    }

    /**
     * Mengambil daftar pengguna sistem dengan filter & pagination
     */
    public function getUsers($search = '', $role = '', $status = '', $page = 1, $limit = 6) {
        $page = max(1, (int)$page);
        $limit = max(1, min(100, (int)$limit));
        $offset = ($page - 1) * $limit;

        $where = "WHERE 1=1";
        $types = "";
        $params = [];

        if (!empty($search)) {
            $like = "%" . trim($search) . "%";
            $where .= " AND (u.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR u.institution LIKE ?)";
            $types .= "ssss";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($role)) {
            $dbRole = strtolower($role);
            if ($role === 'Petugas SPPG') $dbRole = 'sppg';
            if ($role === 'Siswa') $dbRole = 'student';
            if ($role === 'Admin') $dbRole = 'admin';
            $where .= " AND u.role = ?";
            $types .= "s";
            $params[] = $dbRole;
        }

        if (!empty($status)) {
            $dbStatus = ($status === 'Aktif' || $status === 'active') ? 'active' : 'inactive';
            $where .= " AND u.status = ?";
            $types .= "s";
            $params[] = $dbStatus;
        }

        $countSql = "SELECT COUNT(*) AS total FROM users u $where";
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

        $sql = "SELECT u.id, u.full_name, u.username, u.email, u.role, u.institution, u.status, u.created_at
                FROM users u
                $where
                ORDER BY u.id DESC
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
                $roleDisplay = 'Admin';
                if ($r['role'] === 'sppg') $roleDisplay = 'Petugas SPPG';
                if ($r['role'] === 'student') $roleDisplay = 'Siswa';

                $rows[] = [
                    "id" => (int)$r['id'],
                    "name" => $r['full_name'],
                    "username" => $r['username'],
                    "email" => $r['email'],
                    "role" => $roleDisplay,
                    "institution" => $r['institution'] ?: 'Lembaga Pendidikan',
                    "status" => ($r['status'] === 'active' || $r['status'] === 'Aktif') ? 'Aktif' : 'Nonaktif'
                ];
            }
            $stmt->close();
        }

        $resKPI = $this->db->query("SELECT 
            COUNT(*) AS total,
            SUM(role = 'admin') AS admin_count,
            SUM(role = 'sppg') AS sppg_count,
            SUM(role = 'student') AS student_count
            FROM users");
        $kpi = ["total" => 0, "admin" => 0, "sppg" => 0, "siswa" => 0];
        if ($resKPI && ($rk = $resKPI->fetch_assoc())) {
            $kpi["total"] = (int)$rk['total'];
            $kpi["admin"] = (int)$rk['admin_count'];
            $kpi["sppg"] = (int)$rk['sppg_count'];
            $kpi["siswa"] = (int)$rk['student_count'];
        }

        return [
            "users" => $rows,
            "kpi" => $kpi,
            "total" => $totalRows,
            "page" => $page,
            "limit" => $limit,
            "total_pages" => max(1, (int)ceil($totalRows / $limit))
        ];
    }

    /**
     * Menyimpan / memperbarui pengguna
     */
    public function saveUser($data) {
        $id = !empty($data['id']) ? (int)$data['id'] : 0;
        $name = trim($data['name'] ?? $data['full_name'] ?? '');
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = (string)($data['password'] ?? '');
        $rawRole = strtolower(trim($data['role'] ?? 'admin'));
        $role = 'admin';
        if ($rawRole === 'petugas sppg' || $rawRole === 'sppg') $role = 'sppg';
        if ($rawRole === 'siswa' || $rawRole === 'student') $role = 'student';

        $institution = trim($data['institution'] ?? '');
        $status = (strtolower($data['status'] ?? 'active') === 'nonaktif' || strtolower($data['status'] ?? 'active') === 'inactive') ? 'inactive' : 'active';

        if ($id > 0) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->db->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, password_hash = ?, role = ?, institution = ?, status = ? WHERE id = ?");
                if (!$stmt) return false;
                $stmt->bind_param("sssssssi", $name, $username, $email, $hash, $role, $institution, $status, $id);
            } else {
                $stmt = $this->db->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, role = ?, institution = ?, status = ? WHERE id = ?");
                if (!$stmt) return false;
                $stmt->bind_param("ssssssi", $name, $username, $email, $role, $institution, $status, $id);
            }
            $ok = $stmt->execute();
            $stmt->close();
            $userId = $id;
        } else {
            $hash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : '$2b$10$defaultseedhash';
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash, full_name, role, institution, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) return false;
            $stmt->bind_param("sssssss", $username, $email, $hash, $name, $role, $institution, $status);
            $ok = $stmt->execute();
            $userId = $ok ? (int)$this->db->insert_id : false;
            $stmt->close();
        }

        $this->logActivity('Akun Pengguna ' . ($id > 0 ? 'Diperbarui' : 'Ditambahkan'), "Akun @$username ($role) disimpan oleh admin.");
        return $userId;
    }

    /**
     * Menghapus pengguna
     */
    public function deleteUser($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        $this->logActivity('Pengguna Dihapus', "Akun pengguna ID #$id dihapus oleh admin.");
        return $ok;
    }

    /**
     * Menyimpan log aktivitas ke database
     */
    public function logActivity($title, $desc, $status = 'System') {
        $stmt = $this->db->prepare("INSERT INTO activity_logs (sppg_id, title, description, status) VALUES (1, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $title, $desc, $status);
            $stmt->execute();
            $stmt->close();
        }
    }
}

