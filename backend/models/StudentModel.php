<?php
/**
 * SIM-MBG — Student Model (StudentModel.php)
 * Menangani seluruh interaksi database untuk modul Siswa:
 * - Profil siswa, asal sekolah, dan catatan alergi medis
 * - Pengambilan makan siang harian (pickups)
 * - Jadwal & daftar menu aktif
 * - Pemilihan paket makanan siswa (menu_selections)
 * - Riwayat makan dan ulasan siswa (student_reviews)
 */

class StudentModel {
    /**
     * @var mysqli|PDO
     */
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Mengambil profil dasar siswa berdasarkan student_id
     */
    public function getProfileById($studentId) {
        if (!$this->db) return null;
        $studentId = (int)$studentId;

        $sql = "SELECT s.id AS student_id, s.user_id, s.nisn, s.name, s.class_name, s.recipient_status, s.profile_picture,
                       u.username, u.email, u.full_name,
                       sc.id AS school_id, sc.name AS school_name, sc.district, sc.city, sc.sppg_id
                FROM students s
                JOIN users u ON u.id = s.user_id
                LEFT JOIN schools sc ON sc.id = s.school_id
                WHERE s.id = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $res = $stmt->get_result();
        $profile = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $profile;
    }

    /**
     * Mencari student_id berdasarkan user_id, nisn, atau username
     */
    public function findStudentId($userId = null, $nisn = null, $username = null) {
        if (!$this->db) return null;

        if (!empty($userId)) {
            $stmt = $this->db->prepare("SELECT id FROM students WHERE user_id = ? LIMIT 1");
            if ($stmt) {
                $uid = (int)$userId;
                $stmt->bind_param("i", $uid);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $stmt->close();
                    return (int)$row['id'];
                }
                $stmt->close();
            }
        }

        if (!empty($nisn)) {
            $stmt = $this->db->prepare("SELECT id FROM students WHERE nisn = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $nisn);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $stmt->close();
                    return (int)$row['id'];
                }
                $stmt->close();
            }
        }

        if (!empty($username)) {
            $stmt = $this->db->prepare("SELECT s.id FROM students s JOIN users u ON u.id = s.user_id WHERE u.username = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $stmt->close();
                    return (int)$row['id'];
                }
                $stmt->close();
            }
        }

        // Fallback: siswa pertama di sistem
        $res = $this->db->query("SELECT id FROM students ORDER BY id ASC LIMIT 1");
        if ($res && ($row = $res->fetch_assoc())) {
            return (int)$row['id'];
        }

        return null;
    }

    /**
     * Mengambil status pickup hari ini
     */
    public function getTodayPickup($studentId) {
        $studentId = (int)$studentId;
        $stmt = $this->db->prepare("SELECT status, pickup_time FROM pickups WHERE student_id = ? AND date = CURDATE() LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row;
    }

    /**
     * Memastikan baris pickup harian terinisialisasi
     */
    public function initTodayPickup($studentId) {
        $studentId = (int)$studentId;
        $stmt = $this->db->prepare("INSERT IGNORE INTO pickups (student_id, date, status) VALUES (?, CURDATE(), 'pending')");
        if ($stmt) {
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $stmt->close();
        }
    }

    /**
     * Menandai pengambilan makan hari ini
     */
    public function markPickupClaimed($studentId) {
        $studentId = (int)$studentId;
        $stmt = $this->db->prepare(
            "INSERT INTO pickups (student_id, date, status, pickup_time)
             VALUES (?, CURDATE(), 'picked_up', NOW())
             ON DUPLICATE KEY UPDATE status = 'picked_up', pickup_time = NOW()"
        );
        if (!$stmt) return false;
        $stmt->bind_param("i", $studentId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Mengambil menu hari ini untuk siswa (dari menu_selections atau menu_schedules)
     */
    public function getTodayMenu($studentId) {
        $studentId = (int)$studentId;

        // Pilihan spesifik siswa hari ini
        $stmt = $this->db->prepare(
            "SELECT m.id, m.name, m.description, m.category, m.calories, m.protein, m.carbs, m.fat, m.fiber, m.image
             FROM menu_selections ms
             JOIN menu_schedules sch ON sch.id = ms.schedule_id AND sch.date = CURDATE()
             JOIN menus m ON m.id = ms.menu_id
             WHERE ms.student_id = ?
             LIMIT 1"
        );
        if ($stmt) {
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $stmt->close();
                return $row;
            }
            $stmt->close();
        }

        // Pilihan jadwal umum hari ini
        $stmtSched = $this->db->prepare(
            "SELECT m.id, m.name, m.description, m.category, m.calories, m.protein, m.carbs, m.fat, m.fiber, m.image
             FROM menu_schedules sch
             JOIN menus m ON m.id = sch.menu_id
             WHERE sch.date = CURDATE()
             LIMIT 1"
        );
        if ($stmtSched) {
            $stmtSched->execute();
            $resSched = $stmtSched->get_result();
            if ($row = $resSched->fetch_assoc()) {
                $stmtSched->close();
                return $row;
            }
            $stmtSched->close();
        }

        // Fallback: menu aktif pertama
        $resDef = $this->db->query("SELECT id, name, description, category, calories, protein, carbs, fat, fiber, image FROM menus WHERE is_active = 1 LIMIT 1");
        return $resDef ? $resDef->fetch_assoc() : null;
    }

    /**
     * Mengambil status distribusi sekolah hari ini
     */
    public function getSchoolDistribution($schoolId) {
        if (!$schoolId) return null;
        $schoolId = (int)$schoolId;
        $stmt = $this->db->prepare(
            "SELECT status, eta_start, eta_end, fleet, portion_count
             FROM distributions
             WHERE school_id = ? AND date = CURDATE()
             LIMIT 1"
        );
        if (!$stmt) return null;
        $stmt->bind_param("i", $schoolId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row;
    }

    /**
     * Mengambil daftar alergi siswa
     */
    public function getStudentAllergies($studentId) {
        $studentId = (int)$studentId;
        $allergies = [];
        $stmt = $this->db->prepare(
            "SELECT a.name
             FROM student_allergies sa
             JOIN allergens a ON a.id = sa.allergen_id
             WHERE sa.student_id = ?"
        );
        if ($stmt) {
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $allergies[] = $row['name'];
            }
            $stmt->close();
        }
        return $allergies;
    }

    /**
     * Mengambil menu yang sedang dipilih siswa
     */
    public function getSelectedMenuId($studentId) {
        $studentId = (int)$studentId;
        $stmt = $this->db->prepare(
            "SELECT ms.menu_id
             FROM menu_selections ms
             JOIN menu_schedules sch ON sch.id = ms.schedule_id AND (sch.date = CURDATE() OR sch.date >= CURDATE())
             WHERE ms.student_id = ?
             ORDER BY sch.date ASC
             LIMIT 1"
        );
        if ($stmt) {
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $stmt->close();
                return (int)$row['menu_id'];
            }
            $stmt->close();
        }
        return null;
    }

    /**
     * Mengambil seluruh menu aktif beserta kandungan alergen
     */
    public function getAllActiveMenus() {
        $sql = "
            SELECT m.id, m.name, m.description, m.category, m.calories, m.carbs, m.protein, m.fat, m.fiber, m.image,
                   GROUP_CONCAT(DISTINCT IF(ma.type = 'contains', a.name, NULL) SEPARATOR ', ') AS contains_allergens,
                   GROUP_CONCAT(DISTINCT IF(ma.type = 'free_from', a.name, NULL) SEPARATOR ', ') AS free_from_allergens
            FROM menus m
            LEFT JOIN menu_allergens ma ON ma.menu_id = m.id
            LEFT JOIN allergens a ON a.id = ma.allergen_id
            WHERE m.is_active = 1
            GROUP BY m.id
            ORDER BY m.id ASC
        ";
        $res = $this->db->query($sql);
        $menus = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $menus[] = $row;
            }
        }
        return $menus;
    }

    /**
     * Menyimpan pilihan paket menu siswa
     */
    public function saveMenuSelection($studentId, $menuId, $sppgId = 1) {
        $studentId = (int)$studentId;
        $menuId = (int)$menuId;

        // 1. Cari atau buat jadwal menu hari ini
        $scheduleId = null;
        $stmtFind = $this->db->prepare("SELECT id FROM menu_schedules WHERE date = CURDATE() LIMIT 1");
        if ($stmtFind) {
            $stmtFind->execute();
            $res = $stmtFind->get_result();
            if ($row = $res->fetch_assoc()) {
                $scheduleId = (int)$row['id'];
            }
            $stmtFind->close();
        }

        if (!$scheduleId) {
            $stmtAdd = $this->db->prepare("INSERT INTO menu_schedules (menu_id, sppg_id, date, cycle, cutoff_time) VALUES (?, ?, CURDATE(), 'Harian', '20:00:00')");
            if ($stmtAdd) {
                $stmtAdd->bind_param("ii", $menuId, $sppgId);
                $stmtAdd->execute();
                $scheduleId = (int)$this->db->insert_id;
                $stmtAdd->close();
            }
        }

        // 2. Cek konflik alergen
        $consumptionStatus = 'active';
        $stmtCheck = $this->db->prepare(
            "SELECT COUNT(*) AS conflict_count
             FROM student_allergies sa
             JOIN menu_allergens ma ON ma.allergen_id = sa.allergen_id
             WHERE sa.student_id = ? AND ma.menu_id = ? AND ma.type = 'contains'"
        );
        if ($stmtCheck) {
            $stmtCheck->bind_param("ii", $studentId, $menuId);
            $stmtCheck->execute();
            $confRes = $stmtCheck->get_result();
            if ($cRow = $confRes->fetch_assoc()) {
                if ((int)$cRow['conflict_count'] > 0) {
                    $consumptionStatus = 'medical_review';
                }
            }
            $stmtCheck->close();
        }

        // 3. Simpan / update pilihan menu
        $stmtSave = $this->db->prepare(
            "INSERT INTO menu_selections (student_id, schedule_id, menu_id, consumption_status)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE menu_id = VALUES(menu_id), consumption_status = VALUES(consumption_status)"
        );
        if (!$stmtSave) return false;
        $stmtSave->bind_param("iiis", $studentId, $scheduleId, $menuId, $consumptionStatus);
        $ok = $stmtSave->execute();
        $stmtSave->close();

        // 4. Ambil nama menu
        $menuName = "Menu Paket";
        $stmtName = $this->db->prepare("SELECT name FROM menus WHERE id = ? LIMIT 1");
        if ($stmtName) {
            $stmtName->bind_param("i", $menuId);
            $stmtName->execute();
            $nRes = $stmtName->get_result();
            if ($nRow = $nRes->fetch_assoc()) {
                $menuName = $nRow['name'];
            }
            $stmtName->close();
        }

        return [
            "success" => $ok,
            "menu_id" => $menuId,
            "menu_name" => $menuName,
            "consumption_status" => $consumptionStatus,
            "requires_medical_review" => ($consumptionStatus === 'medical_review')
        ];
    }

    /**
     * Mengambil riwayat makan dan testimoni ulasan siswa
     */
    public function getHistoryAndReviews($studentId) {
        $studentId = (int)$studentId;
        $history = [];

        $sql = "
            SELECT p.id AS pickup_id, p.date, p.status AS pickup_status, p.pickup_time,
                   m.id AS menu_id, m.name AS menu_name, m.calories, m.protein, m.fiber, m.image,
                   r.id AS review_id, r.rating, r.comment, r.created_at AS reviewed_at
            FROM pickups p
            LEFT JOIN menu_selections ms ON ms.student_id = p.student_id
            LEFT JOIN menu_schedules sch ON sch.id = ms.schedule_id AND sch.date = p.date
            LEFT JOIN menus m ON m.id = ms.menu_id
            LEFT JOIN student_reviews r ON r.student_id = p.student_id AND r.menu_id = m.id
            WHERE p.student_id = ?
            ORDER BY p.date DESC
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $history[] = $row;
            }
            $stmt->close();
        }

        if (empty($history)) {
            $stmtRev = $this->db->prepare(
                "SELECT r.id AS review_id, r.menu_id, r.rating, r.comment, r.created_at,
                        m.name AS menu_name, m.calories, m.protein, m.fiber, m.image
                 FROM student_reviews r
                 JOIN menus m ON m.id = r.menu_id
                 WHERE r.student_id = ?
                 ORDER BY r.created_at DESC"
            );
            if ($stmtRev) {
                $stmtRev->bind_param("i", $studentId);
                $stmtRev->execute();
                $resRev = $stmtRev->get_result();
                while ($r = $resRev->fetch_assoc()) {
                    $history[] = [
                        "pickup_id" => 0,
                        "date" => $r['created_at'],
                        "pickup_status" => "picked_up",
                        "pickup_time" => null,
                        "menu_id" => $r['menu_id'],
                        "menu_name" => $r['menu_name'],
                        "calories" => $r['calories'],
                        "protein" => $r['protein'],
                        "fiber" => $r['fiber'],
                        "image" => $r['image'],
                        "review_id" => $r['review_id'],
                        "rating" => $r['rating'],
                        "comment" => $r['comment'],
                        "reviewed_at" => $r['created_at']
                    ];
                }
                $stmtRev->close();
            }
        }

        return $history;
    }

    /**
     * Menyimpan atau memperbarui ulasan makanan
     */
    public function saveReview($studentId, $menuId, $rating, $comment, $reviewId = 0) {
        $studentId = (int)$studentId;
        $menuId = (int)$menuId;
        $rating = max(1, min(5, (int)$rating));
        $comment = trim((string)$comment);
        $reviewId = (int)$reviewId;

        if ($reviewId > 0) {
            $stmt = $this->db->prepare("UPDATE student_reviews SET rating = ?, comment = ? WHERE id = ? AND student_id = ?");
            if (!$stmt) return false;
            $stmt->bind_param("isii", $rating, $comment, $reviewId, $studentId);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO student_reviews (student_id, menu_id, rating, comment)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)"
            );
            if (!$stmt) return false;
            $stmt->bind_param("iiis", $studentId, $menuId, $rating, $comment);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
    }

    /**
     * Menghapus ulasan
     */
    public function deleteReview($studentId, $reviewId) {
        $studentId = (int)$studentId;
        $reviewId = (int)$reviewId;
        $stmt = $this->db->prepare("DELETE FROM student_reviews WHERE id = ? AND student_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ii", $reviewId, $studentId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Mengambil data alergi & catatan medis lengkap
     */
    public function getAllergiesWithNotes($studentId) {
        $studentId = (int)$studentId;
        $allergies = [];
        $medicalNotes = '';

        $stmt = $this->db->prepare(
            "SELECT a.id, a.name, sa.medical_notes, sa.verification_status
             FROM student_allergies sa
             JOIN allergens a ON a.id = sa.allergen_id
             WHERE sa.student_id = ?"
        );
        if ($stmt) {
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $allergies[] = $row['name'];
                if (!empty($row['medical_notes'])) {
                    $medicalNotes = $row['medical_notes'];
                }
            }
            $stmt->close();
        }

        return ['allergies' => $allergies, 'medical_notes' => $medicalNotes];
    }

    /**
     * Mengambil statistik KPI kehadiran & ulasan siswa
     */
    public function getStudentKPI($studentId) {
        $studentId = (int)$studentId;
        $stats = [
            "attendance_rate" => "100%",
            "total_reviews" => 0,
            "avg_rating" => 5.0
        ];

        $stmt = $this->db->prepare("
            SELECT
              (SELECT ROUND(100 * SUM(status = 'picked_up') / NULLIF(COUNT(*), 0)) FROM pickups WHERE student_id = ?) AS attendance_pct,
              (SELECT COUNT(*) FROM student_reviews WHERE student_id = ?) AS total_revs,
              (SELECT ROUND(AVG(rating), 1) FROM student_reviews WHERE student_id = ?) AS average_rate
        ");
        if ($stmt) {
            $stmt->bind_param("iii", $studentId, $studentId, $studentId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $stats["attendance_rate"] = ($row['attendance_pct'] !== null) ? $row['attendance_pct'] . "%" : "100%";
                $stats["total_reviews"] = (int)($row['total_revs'] ?? 0);
                $stats["avg_rating"] = (float)($row['average_rate'] ?: 5.0);
            }
            $stmt->close();
        }

        return $stats;
    }

    /**
     * Memperbarui profil siswa & preferensi alergi
     */
    public function updateProfileData($studentId, $userId, $name, $avatar, $allergies, $medicalNotes) {
        $studentId = (int)$studentId;
        $userId = (int)$userId;

        if (!empty($name)) {
            $stmtU = $this->db->prepare("UPDATE users SET full_name = ? WHERE id = ?");
            if ($stmtU) {
                $stmtU->bind_param("si", $name, $userId);
                $stmtU->execute();
                $stmtU->close();
            }
            $stmtS = $this->db->prepare("UPDATE students SET name = ? WHERE id = ?");
            if ($stmtS) {
                $stmtS->bind_param("si", $name, $studentId);
                $stmtS->execute();
                $stmtS->close();
            }
        }

        if (!empty($avatar) && strlen($avatar) < 255) {
            $stmtPic = $this->db->prepare("UPDATE students SET profile_picture = ? WHERE id = ?");
            if ($stmtPic) {
                $stmtPic->bind_param("si", $avatar, $studentId);
                $stmtPic->execute();
                $stmtPic->close();
            }
        }

        // Transaksi pembaruan alergi
        $this->db->begin_transaction();
        try {
            $stmtDel = $this->db->prepare("DELETE FROM student_allergies WHERE student_id = ?");
            if ($stmtDel) {
                $stmtDel->bind_param("i", $studentId);
                $stmtDel->execute();
                $stmtDel->close();
            }

            if (!empty($allergies) && is_array($allergies)) {
                $stmtIns = $this->db->prepare(
                    "INSERT INTO student_allergies (student_id, allergen_id, medical_notes, verification_status)
                     VALUES (?, ?, ?, 'medical_review')"
                );
                foreach ($allergies as $algName) {
                    $algName = trim($algName);
                    if ($algName === '') continue;

                    $algId = null;
                    $stmtF = $this->db->prepare("SELECT id FROM allergens WHERE name = ? LIMIT 1");
                    if ($stmtF) {
                        $stmtF->bind_param("s", $algName);
                        $stmtF->execute();
                        $r = $stmtF->get_result();
                        if ($row = $r->fetch_assoc()) $algId = (int)$row['id'];
                        $stmtF->close();
                    }

                    if (!$algId) {
                        $stmtN = $this->db->prepare("INSERT INTO allergens (name) VALUES (?)");
                        if ($stmtN) {
                            $stmtN->bind_param("s", $algName);
                            $stmtN->execute();
                            $algId = (int)$this->db->insert_id;
                            $stmtN->close();
                        }
                    }

                    if ($algId && $stmtIns) {
                        $stmtIns->bind_param("iis", $studentId, $algId, $medicalNotes);
                        $stmtIns->execute();
                    }
                }
                if ($stmtIns) $stmtIns->close();
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
}

