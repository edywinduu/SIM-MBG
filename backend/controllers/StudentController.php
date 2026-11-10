<?php
/**
 * SIM-MBG — Student Controller (StudentController.php)
 * Mengelola permintaan HTTP untuk portal Siswa dan mendelegasikan kueri ke StudentModel (MVC).
 */

require_once __DIR__ . '/../models/StudentModel.php';

class StudentController {
    /**
     * @var mysqli|PDO
     */
    private $db;

    /**
     * @var StudentModel
     */
    private $studentModel;

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

        $this->studentModel = new StudentModel($this->db);
    }

    /**
     * Mengatur header CORS
     */
    private function applyCorsHeaders() {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Student-Id, X-User-Id");
        header("Content-Type: application/json; charset=UTF-8");
    }

    /**
     * Helper respon JSON standar
     */
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

    /**
     * Format tanggal Bahasa Indonesia
     */
    private function indonesianDate($dateStr = null) {
        $timestamp = $dateStr ? strtotime($dateStr) : time();
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $dayName = $days[date('w', $timestamp)];
        $dayNum = date('j', $timestamp);
        $monthName = $months[(int)date('n', $timestamp)];
        $year = date('Y', $timestamp);

        return "$dayName, $dayNum $monthName $year";
    }

    /**
     * Parse input dari POST, GET, dan JSON body
     */
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

    /**
     * Mengidentifikasi ID Siswa saat ini
     */
    private function resolveStudentId($input) {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        $studentId = null;
        if (!empty($_SESSION['student_id'])) {
            $studentId = (int)$_SESSION['student_id'];
        } elseif (!empty($_SESSION['user_id'])) {
            $studentId = $this->studentModel->findStudentId((int)$_SESSION['user_id']);
        }

        if (!$studentId) {
            $candidateStudentId = (int)($_GET['student_id'] ?? $input['student_id'] ?? 0);
            $candidateUserId = (int)($_GET['user_id'] ?? $input['user_id'] ?? 0);
            $candidateNis = trim($_GET['nis'] ?? $input['nis'] ?? $_GET['nisn'] ?? $input['nisn'] ?? '');
            $candidateUser = trim($_GET['username'] ?? $input['username'] ?? '');

            if ($candidateStudentId > 0) {
                $studentId = $candidateStudentId;
            } elseif ($candidateUserId > 0 || $candidateNis !== '' || $candidateUser !== '') {
                $studentId = $this->studentModel->findStudentId($candidateUserId, $candidateNis, $candidateUser);
            }
        }

        if (!$studentId) {
            $studentId = $this->studentModel->findStudentId();
        }

        return $studentId;
    }

    /**
     * Main request entry point / dispatcher
     */
    public function handleRequest() {
        $this->applyCorsHeaders();

        if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "OPTIONS") {
            http_response_code(200);
            exit;
        }

        $input = $this->parseInput();
        $action = trim($_GET['action'] ?? $input['action'] ?? 'get_dashboard');
        $studentId = $this->resolveStudentId($input);

        if (!$studentId) {
            $this->sendResponse("error", "Data siswa tidak ditemukan dalam sistem database.", null, 404);
        }

        $profile = $this->studentModel->getProfileById($studentId);
        if (!$profile) {
            $this->sendResponse("error", "Profil siswa tidak ditemukan.", null, 404);
        }

        switch ($action) {
            case 'get_dashboard':
                $this->getDashboard($studentId, $profile);
                break;

            case 'toggle_pickup':
                $this->togglePickup($studentId);
                break;

            case 'get_menus':
                $this->getMenus($studentId);
                break;

            case 'select_menu':
                $this->selectMenu($studentId, $input, $profile);
                break;

            case 'get_history':
                $this->getHistory($studentId);
                break;

            case 'submit_review':
            case 'update_review':
                $this->saveReview($studentId, $input, $action);
                break;

            case 'delete_review':
                $this->deleteReview($studentId, $input);
                break;

            case 'get_profile':
                $this->getProfile($studentId, $profile);
                break;

            case 'update_profile':
                $this->updateProfile($studentId, $profile, $input);
                break;

            default:
                $this->sendResponse("error", "Aksi '$action' tidak dikenal pada modul siswa.", null, 400);
                break;
        }
    }

    private function getDashboard($studentId, $profile) {
        $pickup = $this->studentModel->getTodayPickup($studentId);
        $pickupStatus = $pickup ? $pickup['status'] : 'pending';
        $pickupTime = $pickup ? $pickup['pickup_time'] : null;
        $isClaimed = ($pickupStatus === 'picked_up');

        if (!$pickup) {
            $this->studentModel->initTodayPickup($studentId);
        }

        $todayMenu = $this->studentModel->getTodayMenu($studentId);
        $schoolDistribution = !empty($profile['school_id'])
            ? $this->studentModel->getSchoolDistribution($profile['school_id'])
            : null;

        $this->sendResponse("success", "Data dashboard berhasil dimuat.", [
            "student" => [
                "id" => (int)$profile['student_id'],
                "user_id" => (int)$profile['user_id'],
                "name" => $profile['name'],
                "nis" => $profile['nisn'],
                "school" => $profile['school_name'] ?? 'SMA Negeri 1 Denpasar',
                "class" => $profile['class_name'],
                "profile_picture" => $profile['profile_picture']
            ],
            "date" => $this->indonesianDate(),
            "pickup" => [
                "status" => $pickupStatus,
                "is_claimed" => $isClaimed,
                "pickup_time" => $pickupTime ? date('H.i \W\I\T\A', strtotime($pickupTime)) : null,
                "time_slot" => "11:30 – 13:00 WITA",
                "location" => "Kantin Sekolah / Ruang Makan Kelas"
            ],
            "menu" => $todayMenu ? [
                "id" => (int)$todayMenu['id'],
                "code" => "Paket " . chr(64 + min(max(1, (int)$todayMenu['id']), 26)),
                "name" => $todayMenu['name'],
                "desc" => $todayMenu['description'],
                "calories" => $todayMenu['calories'] . " kkal",
                "protein" => $todayMenu['protein'] . "g",
                "carbs" => $todayMenu['carbs'] . "g",
                "fat" => $todayMenu['fat'] . "g",
                "fiber" => $todayMenu['fiber'] . "g",
                "image" => $todayMenu['image'] ?: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80"
            ] : null,
            "distribution" => $schoolDistribution
        ]);
    }

    private function togglePickup($studentId) {
        $ok = $this->studentModel->markPickupClaimed($studentId);
        if (!$ok) {
            $this->sendResponse("error", "Gagal memperbarui status pengambilan makan.", null, 500);
        }

        $this->sendResponse("success", "Status makan siang berhasil diperbarui menjadi Sudah Diambil!", [
            "status" => "picked_up",
            "is_claimed" => true,
            "pickup_time" => date('H.i \W\I\T\A')
        ]);
    }

    private function getMenus($studentId) {
        $userAllergies = $this->studentModel->getStudentAllergies($studentId);
        $selectedMenuId = $this->studentModel->getSelectedMenuId($studentId);
        $dbMenus = $this->studentModel->getAllActiveMenus();

        $menus = [];
        $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
        $index = 0;

        foreach ($dbMenus as $m) {
            $containsList = !empty($m['contains_allergens']) ? array_map('trim', explode(',', $m['contains_allergens'])) : [];
            $freeFromList = !empty($m['free_from_allergens']) ? array_map('trim', explode(',', $m['free_from_allergens'])) : [];
            $descItems = array_map('trim', explode(',', $m['description'] ?? ''));

            $hasConflict = false;
            $conflicts = [];
            foreach ($containsList as $c) {
                if (in_array($c, $userAllergies, true)) {
                    $hasConflict = true;
                    $conflicts[] = $c;
                }
            }

            $code = "Paket " . ($letters[$index] ?? chr(65 + $index));
            $index++;

            $menus[] = [
                "id" => (int)$m['id'],
                "code" => $code,
                "name" => $m['name'],
                "desc" => $m['description'],
                "calories" => $m['calories'] . " kkal",
                "protein" => $m['protein'] . "g",
                "carbs" => $m['carbs'] . "g",
                "fat" => $m['fat'] . "g",
                "fiber" => $m['fiber'] . "g",
                "items" => !empty($descItems[0]) ? $descItems : [$m['name']],
                "allergens" => $containsList,
                "free_from" => $freeFromList,
                "has_conflict" => $hasConflict,
                "conflicts" => $conflicts,
                "image" => $m['image'] ?: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80",
                "is_selected" => ($selectedMenuId === (int)$m['id'] || ($selectedMenuId === null && $index === 1))
            ];
        }

        if (!$selectedMenuId && !empty($menus)) {
            $selectedMenuId = $menus[0]['id'];
        }

        $this->sendResponse("success", "Daftar menu berhasil diambil.", [
            "user_allergies" => $userAllergies,
            "selected_menu_id" => $selectedMenuId,
            "cutoff_time" => "20:00:00",
            "menus" => $menus
        ]);
    }

    private function selectMenu($studentId, $input, $profile) {
        $menuId = (int)($input['menu_id'] ?? 0);
        if ($menuId <= 0) {
            $this->sendResponse("error", "Paket menu wajib dipilih.", null, 400);
        }

        $sppgId = $profile['sppg_id'] ?? 1;
        $res = $this->studentModel->saveMenuSelection($studentId, $menuId, $sppgId);

        if (!$res || !$res['success']) {
            $this->sendResponse("error", "Gagal menyimpan pilihan paket menu.", null, 500);
        }

        $this->sendResponse("success", "Pilihan paket berhasil disimpan ke database!", [
            "menu_id" => $res['menu_id'],
            "menu_name" => $res['menu_name'],
            "consumption_status" => $res['consumption_status'],
            "requires_medical_review" => $res['requires_medical_review']
        ]);
    }

    private function getHistory($studentId) {
        $rows = $this->studentModel->getHistoryAndReviews($studentId);
        $history = [];

        foreach ($rows as $row) {
            $mId = (int)($row['menu_id'] ?: 1);
            $code = "Paket " . chr(64 + min(max(1, $mId), 26));
            $history[] = [
                "id" => (int)($row['review_id'] ?: (($row['pickup_id'] ?? 0) + 100)),
                "review_id" => (int)($row['review_id'] ?? 0),
                "menu_id" => $mId,
                "date" => $this->indonesianDate($row['date']),
                "raw_date" => $row['date'],
                "pickupTime" => !empty($row['pickup_time']) ? date('H:i \W\I\T\A', strtotime($row['pickup_time'])) : '12:10 WITA',
                "status" => (($row['pickup_status'] ?? '') === 'picked_up') ? 'Sudah Diambil' : 'Belum Diambil',
                "menuCode" => $code,
                "menuName" => $row['menu_name'] ?: 'Paket Ayam Panggang Madu & Capcay Sayur',
                "rating" => (int)($row['rating'] ?: 0),
                "review" => $row['comment'] ?: 'Belum memberikan ulasan.',
                "macros" => ($row['calories'] ?: 620) . " kkal | " . ($row['protein'] ?: 30) . "g Protein | " . ($row['fiber'] ?: 7) . "g Serat",
                "image" => $row['image'] ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'
            ];
        }

        if (empty($history)) {
            $history = [
                [
                    "id" => 101,
                    "review_id" => 0,
                    "menu_id" => 1,
                    "date" => $this->indonesianDate(),
                    "pickupTime" => '12:10 WITA',
                    "status" => 'Sudah Diambil',
                    "menuCode" => 'Paket A',
                    "menuName" => 'Ayam Panggang Madu & Capcay Sayur',
                    "rating" => 5,
                    "review" => 'Porsi mengenyangkan dan bumbu sangat meresap pas.',
                    "macros" => '620 kkal | 32g Protein | 8g Serat',
                    "image" => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'
                ]
            ];
        }

        $this->sendResponse("success", "Riwayat makan dan ulasan berhasil diambil.", $history);
    }

    private function saveReview($studentId, $input, $action) {
        $reviewId = (int)($input['review_id'] ?? $input['id'] ?? 0);
        $menuId = (int)($input['menu_id'] ?? 1);
        $rating = max(1, min(5, (int)($input['rating'] ?? 5)));
        $comment = trim($input['review'] ?? $input['comment'] ?? 'Makanannya lezat dan bergizi.');

        $isUpdate = ($action === 'update_review' && $reviewId > 0);
        $ok = $this->studentModel->saveReview($studentId, $menuId, $rating, $comment, $isUpdate ? $reviewId : 0);

        if (!$ok) {
            $this->sendResponse("error", "Gagal menyimpan ulasan.", null, 500);
        }

        $this->sendResponse("success", $isUpdate ? "Ulasan berhasil diperbarui!" : "Ulasan berhasil disimpan!");
    }

    private function deleteReview($studentId, $input) {
        $reviewId = (int)($input['review_id'] ?? $input['id'] ?? 0);
        if ($reviewId > 0) {
            $this->studentModel->deleteReview($studentId, $reviewId);
        }
        $this->sendResponse("success", "Ulasan berhasil dihapus!");
    }

    private function getProfile($studentId, $profile) {
        $allergyData = $this->studentModel->getAllergiesWithNotes($studentId);
        $stats = $this->studentModel->getStudentKPI($studentId);

        $this->sendResponse("success", "Profil siswa berhasil diambil.", [
            "id" => (int)$profile['student_id'],
            "user_id" => (int)$profile['user_id'],
            "name" => $profile['name'],
            "username" => $profile['username'],
            "email" => $profile['email'],
            "nis" => $profile['nisn'],
            "school" => $profile['school_name'] ?? 'SMA Negeri 1 Denpasar',
            "class" => $profile['class_name'],
            "profile_picture" => $profile['profile_picture'],
            "allergies" => $allergyData['allergies'],
            "medical_notes" => $allergyData['medical_notes'],
            "stats" => $stats
        ]);
    }

    private function updateProfile($studentId, $profile, $input) {
        $newName = trim($input['name'] ?? $input['full_name'] ?? '');
        $newMedicalNotes = trim($input['medical_notes'] ?? $input['medicalNotes'] ?? '');
        $selectedAllergies = $input['allergies'] ?? [];
        if (is_string($selectedAllergies)) {
            $selectedAllergies = array_filter(array_map('trim', explode(',', $selectedAllergies)));
        }
        $newAvatar = $input['profile_picture'] ?? $input['avatarImage'] ?? null;

        $ok = $this->studentModel->updateProfileData(
            $studentId,
            $profile['user_id'],
            $newName,
            $newAvatar,
            $selectedAllergies,
            $newMedicalNotes
        );

        if (!$ok) {
            $this->sendResponse("error", "Gagal memperbarui data profil.", null, 500);
        }

        if ($newName !== '') {
            $_SESSION['full_name'] = $newName;
        }

        $this->sendResponse("success", "Data profil dan preferensi medis berhasil disimpan ke database!", [
            "name" => $newName ?: $profile['name'],
            "allergies" => $selectedAllergies,
            "medical_notes" => $newMedicalNotes
        ]);
    }
}

