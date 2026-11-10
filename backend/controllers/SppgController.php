<?php
/**
 * SIM-MBG — SPPG Controller (SppgController.php)
 * Mengelola permintaan HTTP untuk portal SPPG dan mendelegasikan kueri ke SppgModel (MVC).
 */

require_once __DIR__ . '/../models/SppgModel.php';

class SppgController {
    /**
     * @var mysqli|PDO
     */
    private $db;

    /**
     * @var SppgModel
     */
    private $sppgModel;

    public function __construct($db = null) {
        if ($db === null) {
            $connectionFile = __DIR__ . '/../config/connection.php';
            if (file_exists($connectionFile)) {
                require_once $connectionFile;
                $this->db = $conn ?? null;
            }
        } else {
            $this->db = $db;
        }

        $this->sppgModel = new SppgModel($this->db);
    }

    private function applyCorsHeaders() {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Content-Type: application/json; charset=UTF-8");
    }

    private function sendResponse($status, $message, $data = null, $httpCode = 200) {
        http_response_code($httpCode);
        $payload = [
            "status" => $status,
            "message" => $message
        ];
        if ($data !== null) {
            $payload["data"] = $data;
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private function parseInput() {
        $input = $_POST;
        $raw = file_get_contents("php://input");
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $input = array_merge($input, $json);
            }
        }
        return $input;
    }

    public function handleRequest() {
        $this->applyCorsHeaders();

        if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "OPTIONS") {
            http_response_code(200);
            exit;
        }

        $input = $this->parseInput();
        $action = trim($_GET['action'] ?? $input['action'] ?? 'get_dashboard');

        switch ($action) {
            case 'get_dashboard':
                $this->getDashboard($input);
                break;

            case 'update_distribution_status':
                $this->updateDistributionStatus($input);
                break;

            case 'get_menus':
                $this->getMenus($input);
                break;

            case 'save_menu':
                $this->saveMenu($input);
                break;

            case 'toggle_menu_status':
                $this->toggleMenuStatus($input);
                break;

            case 'get_student_selections':
                $this->getStudentSelections($input);
                break;

            case 'verify_student_diet':
                $this->verifyStudentDiet($input);
                break;

            case 'get_reviews':
                $this->getReviews($input);
                break;

            case 'respond_feedback':
                $this->respondFeedback($input);
                break;

            default:
                $this->sendResponse("error", "Aksi '$action' tidak dikenal pada modul SPPG.", null, 400);
                break;
        }
    }

    private function getDashboard($input) {
        $search = trim($_GET['search'] ?? $input['search'] ?? $_GET['q'] ?? $input['q'] ?? '');
        $page = (int)($_GET['page'] ?? $input['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? $input['limit'] ?? 5);

        $kpi = $this->sppgModel->getDashboardKPI();
        $announcement = $this->sppgModel->getLatestAnnouncement();
        $activities = $this->sppgModel->getActivityLogs(4);
        $todayMenu = $this->sppgModel->getTodayScheduledMenu();
        $distData = $this->sppgModel->getSchoolDistributions($search, $page, $limit);

        $mappedActivities = array_map(function($act) {
            $statusLower = strtolower($act['status'] ?? '');
            $icon = 'task_alt';
            $tone = 'ico-green';
            $pill = ['pill-green', 'Terkirim'];

            if (str_contains($statusLower, 'transit') || str_contains($statusLower, 'proses') || str_contains($statusLower, 'armada')) {
                $icon = 'directions_run';
                $tone = 'ico-blue';
                $pill = ['pill-blue', 'Diproses'];
            } elseif (str_contains($statusLower, 'evaluasi') || str_contains($statusLower, 'catatan')) {
                $icon = 'rate_review';
                $tone = 'ico-orange';
                $pill = ['pill-orange', 'Evaluasi Diterima'];
            } elseif (str_contains($statusLower, 'inspeksi') || str_contains($statusLower, 'higienitas')) {
                $icon = 'shield';
                $tone = 'ico-green';
                $pill = ['pill-green', 'Terverifikasi'];
            }

            return [
                "icon" => $icon,
                "tone" => $tone,
                "title" => $act['title'],
                "desc" => $act['description'],
                "time" => $act['created_at'],
                "pill" => $pill
            ];
        }, $activities);

        $composition = [];
        if ($todayMenu && !empty($todayMenu['description'])) {
            $parts = array_map('trim', explode(',', $todayMenu['description']));
            $cats = ['Karbohidrat', 'Lauk Utama', 'Sayuran', 'Buah & Pelengkap'];
            foreach ($parts as $idx => $p) {
                $catName = $cats[$idx] ?? 'Pelengkap';
                $composition[] = ["category" => $catName, "desc" => $p];
            }
        }

        $this->sendResponse("success", "Data dashboard SPPG berhasil dimuat.", [
            "kpi" => $kpi,
            "announcement" => [
                "title" => $announcement['title'],
                "content" => $announcement['content'],
                "time" => date('H:i \W\I\T\A', strtotime($announcement['created_at']))
            ],
            "activities" => $mappedActivities,
            "today_menu" => $todayMenu ? [
                "id" => (int)$todayMenu['id'],
                "name" => $todayMenu['name'],
                "calories" => (int)$todayMenu['calories'],
                "protein" => (float)$todayMenu['protein'],
                "carbs" => (int)$todayMenu['carbs'],
                "fat" => (int)$todayMenu['fat'],
                "fiber" => (float)$todayMenu['fiber'],
                "image" => $todayMenu['image'] ?: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80",
                "composition" => $composition
            ] : null,
            "distributions" => $distData['rows'],
            "pagination" => [
                "page" => $distData['page'],
                "limit" => $distData['limit'],
                "total_rows" => $distData['total'],
                "total_pages" => $distData['total_pages']
            ]
        ]);
    }

    private function updateDistributionStatus($input) {
        $schoolId = (int)($input['school_id'] ?? 0);
        $status = trim($input['status'] ?? 'delivered');
        $date = trim($input['date'] ?? '');

        if ($schoolId <= 0) {
            $this->sendResponse("error", "ID Sekolah wajib disertakan.", null, 400);
        }

        $ok = $this->sppgModel->updateDistributionStatus($schoolId, $status, $date ?: null);
        if (!$ok) {
            $this->sendResponse("error", "Gagal memperbarui status pengiriman.", null, 500);
        }

        $this->sendResponse("success", "Status pengiriman sekolah berhasil diperbarui!");
    }

    private function getMenus($input) {
        $category = trim($_GET['category'] ?? $input['category'] ?? $_GET['filter'] ?? $input['filter'] ?? 'all');
        $search = trim($_GET['search'] ?? $input['search'] ?? $_GET['q'] ?? $input['q'] ?? '');

        $kpi = $this->sppgModel->getMenuKPI();
        $dbMenus = $this->sppgModel->getMenusList($category, $search);

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $mapped = [];
        $letters = ['A', 'B', 'C', 'D', 'E', 'F'];

        foreach ($dbMenus as $idx => $m) {
            $cat = $m['category'];
            $frontendCat = 'reguler';
            $catLabel = 'Reguler SD/SMP';
            if ($cat === 'special') {
                $frontendCat = 'khusus';
                $catLabel = 'Diet Alergi Khusus';
            } elseif ($cat === 'backup') {
                $frontendCat = 'cadangan';
                $catLabel = 'Ready-to-Deploy';
            }

            $tags = [];
            if (!empty($m['free_from_allergens'])) {
                $free = array_map('trim', explode(',', $m['free_from_allergens']));
                foreach ($free as $f) {
                    $tags[] = ['ok', 'Bebas ' . $f];
                }
            }
            if (!empty($m['contains_allergens'])) {
                $cont = array_map('trim', explode(',', $m['contains_allergens']));
                foreach ($cont as $c) {
                    $tags[] = ['warn', 'Mengandung ' . $c];
                }
            }
            if (empty($tags)) {
                $tags[] = ['ok', 'Bebas Kacang'];
            }

            $mapped[] = [
                "id" => (int)$m['id'],
                "code" => "Paket " . ($letters[$idx % count($letters)]),
                "day" => ($days[$idx % 5]) . " • Minggu ke-1",
                "cat" => $frontendCat,
                "catLabel" => $catLabel,
                "name" => $m['name'],
                "desc" => $m['description'],
                "kcal" => (int)$m['calories'],
                "calories" => $m['calories'] . " kkal",
                "carb" => (int)$m['carbs'],
                "carbs" => $m['carbs'] . "g",
                "prot" => (int)$m['protein'],
                "protein" => $m['protein'] . "g",
                "fat" => (int)$m['fat'],
                "fiber" => ((float)$m['fiber']) . "g",
                "tags" => $tags,
                "active" => (bool)$m['is_active'],
                "img" => $m['image'] ?: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80"
            ];
        }

        $this->sendResponse("success", "Daftar menu berhasil diambil.", [
            "kpi" => $kpi,
            "menus" => $mapped
        ]);
    }

    private function saveMenu($input) {
        $name = trim($input['name'] ?? '');
        $kcal = (int)($input['kcal'] ?? $input['calories'] ?? 0);

        if ($name === '' || $kcal <= 0) {
            $this->sendResponse("error", "Nama menu dan kalori wajib diisi.", null, 400);
        }

        $menuId = $this->sppgModel->saveMenu($input);
        if (!$menuId) {
            $this->sendResponse("error", "Gagal menyimpan data menu ke database.", null, 500);
        }

        $this->sendResponse("success", "Menu berhasil disimpan ke database!", [
            "menu_id" => $menuId
        ]);
    }

    private function toggleMenuStatus($input) {
        $menuId = (int)($input['menu_id'] ?? $input['id'] ?? 0);
        $isActive = isset($input['is_active']) ? (bool)$input['is_active'] : (isset($input['active']) ? (bool)$input['active'] : true);

        if ($menuId <= 0) {
            $this->sendResponse("error", "ID Menu wajib disertakan.", null, 400);
        }

        $ok = $this->sppgModel->toggleMenuStatus($menuId, $isActive);
        if (!$ok) {
            $this->sendResponse("error", "Gagal memperbarui status menu.", null, 500);
        }

        $this->sendResponse("success", "Status menu berhasil diperbarui!");
    }

    private function getStudentSelections($input) {
        $filter = [
            'q' => trim($_GET['q'] ?? $input['q'] ?? $_GET['search'] ?? $input['search'] ?? ''),
            'school' => trim($_GET['school'] ?? $input['school'] ?? ''),
            'class' => trim($_GET['class'] ?? $input['class'] ?? ''),
            'diet' => trim($_GET['diet'] ?? $input['diet'] ?? ''),
            'status' => trim($_GET['status'] ?? $input['status'] ?? '')
        ];
        $page = (int)($_GET['page'] ?? $input['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? $input['limit'] ?? 10);

        $kpi = $this->sppgModel->getStudentSelectionsKPI();
        $listData = $this->sppgModel->getStudentSelections($filter, $page, $limit);

        $mappedRows = array_map(function($r) {
            $isReview = ($r['consumption_status'] === 'medical_review');
            $type = 'reguler';
            if ($r['menu_category'] === 'special') $type = 'khusus';
            if ($isReview) $type = 'evaluasi';

            return [
                "student_id" => (int)$r['student_id'],
                "nama" => $r['student_name'],
                "nisn" => $r['nisn'],
                "sekolah" => $r['school_name'],
                "kelas" => $r['class_name'],
                "type" => $type,
                "menuLabel" => $r['selected_menu_name'],
                "note" => $r['allergy_notes'] !== '-' ? $r['allergy_notes'] : '',
                "status" => $isReview ? 'review' : 'aktif'
            ];
        }, $listData['rows']);

        $this->sendResponse("success", "Data pilihan menu siswa berhasil diambil.", [
            "kpi" => $kpi,
            "students" => $mappedRows,
            "pagination" => [
                "page" => $listData['page'],
                "limit" => $listData['limit'],
                "total_rows" => $listData['total'],
                "total_pages" => $listData['total_pages']
            ]
        ]);
    }

    private function verifyStudentDiet($input) {
        $studentId = (int)($input['student_id'] ?? $input['id'] ?? 0);
        $allergenId = !empty($input['allergen_id']) ? (int)$input['allergen_id'] : null;

        if ($studentId <= 0) {
            $this->sendResponse("error", "ID Siswa wajib disertakan.", null, 400);
        }

        $ok = $this->sppgModel->verifyStudentDiet($studentId, $allergenId);
        if (!$ok) {
            $this->sendResponse("error", "Gagal memverifikasi catatan diet siswa.", null, 500);
        }

        $this->sendResponse("success", "Preferensi diet siswa berhasil diverifikasi!");
    }

    private function getReviews($input) {
        $filter = trim($_GET['filter'] ?? $input['filter'] ?? 'all');

        $kpi = $this->sppgModel->getReviewKPI();
        $feedbacks = $this->sppgModel->getSchoolFeedbacks($filter);
        $ratingDistribution = $this->sppgModel->getRatingDistribution();
        $favorites = $this->sppgModel->getTopFavoriteMenus(3);
        $audit = $this->sppgModel->getLatestHygieneAudit();

        $mappedFeedbacks = array_map(function($f) {
            $kind = ($f['status'] === 'very_satisfied') ? 'puas' : 'evaluasi';
            $isWarn = ($kind === 'evaluasi');

            $tags = [];
            if (!empty($f['container_temperature'])) {
                $tags[] = ["Suhu: " . $f['container_temperature'], 'green'];
            }
            if (isset($f['waste_percentage'])) {
                $tags[] = ["Sisa: " . $f['waste_percentage'] . "%", 'green'];
            }

            return [
                "id" => (int)$f['id'],
                "code" => strtoupper(substr($f['school_name'], 0, 2)),
                "school" => $f['school_name'],
                "who" => $f['sender_name'] . ($f['position'] ? " • " . $f['position'] : "") . " • " . date('d M Y, H:i \W\I\T\A', strtotime($f['created_at'])),
                "score" => (float)$f['score'],
                "text" => '"' . $f['content'] . '"',
                "tags" => $tags,
                "kind" => $kind,
                "warn" => $isWarn,
                "responded" => !empty($f['kitchen_response'])
            ];
        }, $feedbacks);

        $this->sendResponse("success", "Data review dan evaluasi berhasil diambil.", [
            "kpi" => $kpi,
            "feedbacks" => $mappedFeedbacks,
            "rating_distribution" => $ratingDistribution,
            "favorite_menus" => $favorites,
            "audit" => $audit
        ]);
    }

    private function respondFeedback($input) {
        $feedbackId = (int)($input['feedback_id'] ?? $input['id'] ?? 0);
        $response = trim($input['response'] ?? $input['kitchen_response'] ?? 'Tanggapan telah dicatat oleh Tim Dapur SPPG.');

        if ($feedbackId <= 0) {
            $this->sendResponse("error", "ID Umpan Balik wajib disertakan.", null, 400);
        }

        $ok = $this->sppgModel->saveKitchenResponse($feedbackId, $response);
        if (!$ok) {
            $this->sendResponse("error", "Gagal menyimpan tanggapan dapur.", null, 500);
        }

        $this->sendResponse("success", "Tanggapan dapur berhasil dikirim!");
    }
}

