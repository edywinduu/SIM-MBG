<?php
/**
 * SIM-MBG — Admin Controller (AdminController.php)
 * Mengelola permintaan HTTP untuk portal Administrator dan mendelegasikan kueri ke AdminModel (MVC).
 */

require_once __DIR__ . '/../models/AdminModel.php';

class AdminController {
    /**
     * @var mysqli|PDO
     */
    private $db;

    /**
     * @var AdminModel
     */
    private $adminModel;

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

        $this->adminModel = new AdminModel($this->db);
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
                $this->getDashboard();
                break;

            case 'get_students':
                $this->getStudents($input);
                break;

            case 'save_student':
                $this->saveStudent($input);
                break;

            case 'delete_student':
                $this->deleteStudent($input);
                break;

            case 'get_schools':
                $this->getSchools($input);
                break;

            case 'save_school':
                $this->saveSchool($input);
                break;

            case 'delete_school':
                $this->deleteSchool($input);
                break;

            case 'get_sppg':
                $this->getSppg($input);
                break;

            case 'save_sppg':
                $this->saveSppg($input);
                break;

            case 'delete_sppg':
                $this->deleteSppg($input);
                break;

            case 'get_users':
                $this->getUsers($input);
                break;

            case 'save_user':
                $this->saveUser($input);
                break;

            case 'delete_user':
                $this->deleteUser($input);
                break;

            default:
                $this->sendResponse("error", "Aksi '$action' tidak dikenal pada modul admin.", null, 400);
                break;
        }
    }

    private function getDashboard() {
        $data = $this->adminModel->getDashboardData();
        $this->sendResponse("success", "Data dashboard admin berhasil diambil.", $data);
    }

    private function getStudents($input) {
        $search = trim($_GET['search'] ?? $input['search'] ?? $_GET['q'] ?? $input['q'] ?? '');
        $schoolId = trim($_GET['school'] ?? $input['school'] ?? $_GET['school_id'] ?? $input['school_id'] ?? '');
        $menuType = trim($_GET['menu_type'] ?? $input['menu_type'] ?? '');
        $page = (int)($_GET['page'] ?? $input['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? $input['limit'] ?? 8);

        $res = $this->adminModel->getStudents($search, $schoolId, $menuType, $page, $limit);
        $this->sendResponse("success", "Data siswa berhasil diambil.", $res);
    }

    private function saveStudent($input) {
        $nisn = trim($input['nisn'] ?? '');
        $name = trim($input['name'] ?? '');

        if ($nisn === '' || $name === '') {
            $this->sendResponse("error", "NISN dan nama siswa wajib diisi.", null, 400);
        }

        $id = $this->adminModel->saveStudent($input);
        if (!$id) {
            $this->sendResponse("error", "Gagal menyimpan data siswa.", null, 500);
        }

        $this->sendResponse("success", "Data siswa berhasil disimpan!", ["id" => $id]);
    }

    private function deleteStudent($input) {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            $this->sendResponse("error", "ID siswa tidak valid.", null, 400);
        }

        $ok = $this->adminModel->deleteStudent($id);
        if (!$ok) {
            $this->sendResponse("error", "Gagal menghapus data siswa.", null, 500);
        }

        $this->sendResponse("success", "Data siswa berhasil dihapus!");
    }

    private function getSchools($input) {
        $search = trim($_GET['search'] ?? $input['search'] ?? $_GET['q'] ?? $input['q'] ?? '');
        $regency = trim($_GET['regency'] ?? $input['regency'] ?? '');
        $status = trim($_GET['status'] ?? $input['status'] ?? '');
        $page = (int)($_GET['page'] ?? $input['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? $input['limit'] ?? 6);

        $res = $this->adminModel->getSchools($search, $regency, $status, $page, $limit);
        $this->sendResponse("success", "Data sekolah berhasil diambil.", $res);
    }

    private function saveSchool($input) {
        $npsn = trim($input['npsn'] ?? '');
        $name = trim($input['name'] ?? '');

        if ($npsn === '' || $name === '') {
            $this->sendResponse("error", "NPSN dan nama sekolah wajib diisi.", null, 400);
        }

        $id = $this->adminModel->saveSchool($input);
        if (!$id) {
            $this->sendResponse("error", "Gagal menyimpan data sekolah.", null, 500);
        }

        $this->sendResponse("success", "Data sekolah berhasil disimpan!", ["id" => $id]);
    }

    private function deleteSchool($input) {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            $this->sendResponse("error", "ID sekolah tidak valid.", null, 400);
        }

        $ok = $this->adminModel->deleteSchool($id);
        if (!$ok) {
            $this->sendResponse("error", "Gagal menghapus data sekolah.", null, 500);
        }

        $this->sendResponse("success", "Data sekolah berhasil dihapus!");
    }

    private function getSppg($input) {
        $search = trim($_GET['search'] ?? $input['search'] ?? $_GET['q'] ?? $input['q'] ?? '');
        $regency = trim($_GET['regency'] ?? $input['regency'] ?? '');
        $status = trim($_GET['status'] ?? $input['status'] ?? '');
        $page = (int)($_GET['page'] ?? $input['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? $input['limit'] ?? 6);

        $res = $this->adminModel->getSppgUnits($search, $regency, $status, $page, $limit);
        $this->sendResponse("success", "Data unit SPPG berhasil diambil.", $res);
    }

    private function saveSppg($input) {
        $code = trim($input['code'] ?? '');
        $name = trim($input['name'] ?? '');

        if ($code === '' || $name === '') {
            $this->sendResponse("error", "Kode dan nama SPPG wajib diisi.", null, 400);
        }

        $id = $this->adminModel->saveSppgUnit($input);
        if (!$id) {
            $this->sendResponse("error", "Gagal menyimpan data unit SPPG.", null, 500);
        }

        $this->sendResponse("success", "Data unit SPPG berhasil disimpan!", ["id" => $id]);
    }

    private function deleteSppg($input) {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            $this->sendResponse("error", "ID SPPG tidak valid.", null, 400);
        }

        $ok = $this->adminModel->deleteSppgUnit($id);
        if (!$ok) {
            $this->sendResponse("error", "Gagal menghapus data unit SPPG.", null, 500);
        }

        $this->sendResponse("success", "Data unit SPPG berhasil dihapus!");
    }

    private function getUsers($input) {
        $search = trim($_GET['search'] ?? $input['search'] ?? $_GET['q'] ?? $input['q'] ?? '');
        $role = trim($_GET['role'] ?? $input['role'] ?? '');
        $status = trim($_GET['status'] ?? $input['status'] ?? '');
        $page = (int)($_GET['page'] ?? $input['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? $input['limit'] ?? 6);

        $res = $this->adminModel->getUsers($search, $role, $status, $page, $limit);
        $this->sendResponse("success", "Data pengguna berhasil diambil.", $res);
    }

    private function saveUser($input) {
        $name = trim($input['name'] ?? $input['full_name'] ?? '');
        $username = trim($input['username'] ?? '');
        $email = trim($input['email'] ?? '');

        if ($name === '' || $username === '' || $email === '') {
            $this->sendResponse("error", "Nama, username, dan email wajib diisi.", null, 400);
        }

        $id = $this->adminModel->saveUser($input);
        if (!$id) {
            $this->sendResponse("error", "Gagal menyimpan data akun pengguna.", null, 500);
        }

        $this->sendResponse("success", "Data pengguna berhasil disimpan!", ["id" => $id]);
    }

    private function deleteUser($input) {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            $this->sendResponse("error", "ID pengguna tidak valid.", null, 400);
        }

        $ok = $this->adminModel->deleteUser($id);
        if (!$ok) {
            $this->sendResponse("error", "Gagal menghapus pengguna.", null, 500);
        }

        $this->sendResponse("success", "Data pengguna berhasil dihapus!");
    }
}

