<?php
/**
 * SIM-MBG — AuthController
 * Mengimplementasikan Controller untuk modul Autentikasi (MVC)
 * Berperan sebagai pengontrol alur autentikasi dan registrasi, dengan tetap
 * mengacu pada fungsionalitas dan kontrak data di backend/api/login.php & backend/api/register.php
 */

require_once __DIR__ . '/../models/User.php';

class AuthController {
    /**
     * @var mysqli|PDO|null
     */
    private $db;

    /**
     * @var User
     */
    private $userModel;

    /**
     * Inisialisasi AuthController dengan koneksi database
     * Jika parameter $db tidak diberikan, otomatis memuat koneksi dari config/connection.php
     *
     * @param mysqli|PDO|null $db
     */
    public function __construct($db = null) {
        if ($db === null) { 
            $connectionFile = __DIR__ . '/../config/connection.php';
            if (file_exists($connectionFile)) {
                require_once $connectionFile;
                // $conn didefinisikan di config/connection.php
                $this->db = $conn ?? null;
            }
        } else {
            $this->db = $db;
        }

        // Menghubungkan Controller dengan Model User
        $this->userModel = new User($this->db);
    }

    /**
     * Mengambil instance User Model yang digunakan
     *
     * @return User
     */
    public function getUserModel() {
        return $this->userModel;
    }

    /**
     * Memproses logika login pengguna
     * Mengacu pada alur validasi dan verifikasi di backend/api/login.php:
     * - Validasi kelengkapan input
     * - Pencarian user via Model (username, email, atau NISN)
     * - Verifikasi password (bcrypt hash maupun plain-text untuk seed/testing)
     * - Verifikasi status akun aktif
     * - Normalisasi role ('student' -> 'siswa') dan validasi role yang diizinkan
     * - Pembuatan session login
     *
     * @param string $username Nama pengguna, email, atau NIS/NISN
     * @param string $password Kata sandi pengguna
     * @return array Respon terstruktur status, pesan, role, dan data
     */
    public function processLogin($username, $password) {
        $username = trim((string)$username);
        $password = (string)$password;

        // 1. Validasi input wajib
        if ($username === '' || $password === '') {
            return [
                'status' => 'error',
                'code' => 400,
                'message' => 'Username dan password wajib diisi.'
            ];
        }

        // 2. Mencari pengguna melalui Model (username / email / NISN)
        $user = $this->userModel->findByUsername($username);

        // 3. Verifikasi kata sandi (mendukung bcrypt hash maupun plain-text untuk seed/testing)
        $passwordMatches = false;
        if ($user && !empty($user['password_hash'])) {
            if (password_verify($password, $user['password_hash'])) {
                $passwordMatches = true;
            } elseif ($password === $user['password_hash']) {
                $passwordMatches = true;
            }
        }

        if (!$user || !$passwordMatches) {
            return [
                'status' => 'error',
                'code' => 401,
                'message' => 'Username atau password salah.'
            ];
        }

        // 4. Periksa status akun (aktif / non-aktif)
        if (isset($user['status']) && $user['status'] === 'inactive') {
            return [
                'status' => 'error',
                'code' => 403,
                'message' => 'Akun tidak aktif.'
            ];
        }

        // 5. Memeriksa dan menormalisasi role pengguna
        $rawRole = strtolower(trim($user['role'] ?? ''));
        // Normalisasi role: jika bernilai 'student', dinormalisasi ke 'siswa'
        $role = ($rawRole === 'student') ? 'siswa' : $rawRole;

        $allowedRoles = ['siswa', 'sppg', 'admin'];
        if (!in_array($role, $allowedRoles, true)) {
            return [
                'status' => 'error',
                'code' => 403,
                'message' => 'Role akun tidak valid.'
            ];
        }

        // 6. Membuat session login
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_regenerate_id(true);
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
        $_SESSION['role'] = $role;
        if (!empty($user['student_id'])) {
            $_SESSION['student_id'] = (int)$user['student_id'];
        }

        // 7. Mengembalikan data terstruktur persis sesuai acuan backend/api/login.php
        return [
            'status' => 'success',
            'code' => 200,
            'message' => 'Login berhasil.',
            'role' => $role,
            'data' => [
                'id' => (int)$user['id'],
                'student_id' => !empty($user['student_id']) ? (int)$user['student_id'] : null,
                'username' => $user['username'],
                'full_name' => $user['full_name'] ?? $user['username'],
                'email' => $user['email'],
                'role' => $role,
                'nis' => $user['nisn'] ?? '',
                'nisn' => $user['nisn'] ?? '',
                'school' => $user['school_name'] ?? '',
                'school_id' => !empty($user['school_id']) ? (int)$user['school_id'] : null,
                'class' => $user['class_name'] ?? '',
                'profile_picture' => $user['profile_picture'] ?? ''
            ]
        ];
    }

    /**
     * Memproses logika pendaftaran (registrasi) siswa baru
     * Mengacu pada alur validasi dan transaksi di backend/api/register.php:
     * - Validasi field wajib Langkah 1 & 2
     * - Validasi format email & panjang password (minimal 8 karakter)
     * - Validasi konfirmasi password
     * - Pengecekan duplikasi username, email, dan NISN
     * - Pencarian / fallback data sekolah
     * - Transaksi penyimpanan akun pengguna dan profil siswa
     * - Pembuatan session login langsung
     *
     * @param array $input Data formulir pendaftaran
     * @return array Respon status, pesan, role, dan data
     */
    public function processRegister($input) {
        if (!is_array($input)) {
            return [
                'status' => 'error',
                'code' => 400,
                'message' => 'Data pendaftaran tidak valid.'
            ];
        }

        // Data Langkah 1: Sekolah, NIS/NISN, Kelas
        $schoolInput = trim($input['school'] ?? $input['school_name'] ?? $input['school_id'] ?? '');
        $nis = trim($input['nis'] ?? $input['nisn'] ?? '');
        $className = trim($input['class'] ?? $input['class_name'] ?? '');

        // Data Langkah 2: Username, Email, Password, Konfirmasi Password, Nama Lengkap
        $username = trim($input['username'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = (string)($input['password'] ?? '');
        $confirmPassword = (string)($input['confirmPassword'] ?? $input['confirm_password'] ?? '');
        $fullName = trim($input['full_name'] ?? $input['name'] ?? '');
        if ($fullName === '') {
            $fullName = $username;
        }

        // 1. Validasi field wajib diisi
        if ($schoolInput === '') {
            return ['status' => 'error', 'code' => 400, 'message' => 'Asal sekolah wajib dipilih.'];
        }
        if ($nis === '') {
            return ['status' => 'error', 'code' => 400, 'message' => 'Nomor Induk Siswa (NIS/NISN) wajib diisi.'];
        }
        if ($className === '') {
            return ['status' => 'error', 'code' => 400, 'message' => 'Kelas wajib dipilih.'];
        }
        if ($username === '') {
            return ['status' => 'error', 'code' => 400, 'message' => 'Nama pengguna (username) wajib diisi.'];
        }
        if ($email === '') {
            return ['status' => 'error', 'code' => 400, 'message' => 'Alamat email wajib diisi.'];
        }
        if ($password === '') {
            return ['status' => 'error', 'code' => 400, 'message' => 'Kata sandi wajib diisi.'];
        }

        // 2. Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'code' => 400, 'message' => 'Format alamat email tidak valid.'];
        }

        // 3. Validasi panjang kata sandi
        if (strlen($password) < 8) {
            return ['status' => 'error', 'code' => 400, 'message' => 'Kata sandi minimal 8 karakter.'];
        }

        // 4. Validasi kecocokan konfirmasi kata sandi
        if ($confirmPassword !== '' && $password !== $confirmPassword) {
            return ['status' => 'error', 'code' => 400, 'message' => 'Konfirmasi kata sandi tidak cocok. Harap periksa kembali.'];
        }

        // 5. Pengecekan duplikasi pada database via Model
        if ($this->userModel->usernameExists($username)) {
            return ['status' => 'error', 'code' => 409, 'message' => 'Nama pengguna (username) sudah digunakan. Silakan pilih username lain.'];
        }
        if ($this->userModel->emailExists($email)) {
            return ['status' => 'error', 'code' => 409, 'message' => 'Alamat email sudah terdaftar. Silakan gunakan email lain atau langsung masuk.'];
        }
        if ($this->userModel->nisnExists($nis)) {
            return ['status' => 'error', 'code' => 409, 'message' => 'Nomor Induk Siswa (NIS/NISN) sudah terdaftar dalam sistem.'];
        }

        // 6. Mencocokkan data sekolah
        $schoolInfo = $this->userModel->findSchool($schoolInput);
        $schoolId = $schoolInfo['id'];
        $schoolName = $schoolInfo['name'];

        // 7. Menyimpan akun pengguna dan profil siswa dalam transaksi
        try {
            $created = $this->userModel->registerStudent(
                [
                    'username' => $username,
                    'email' => $email,
                    'password_hash' => $password, // Sesuai acuan backend/api/register.php
                    'full_name' => $fullName,
                    'role' => 'student',
                    'institution' => $schoolName,
                    'status' => 'active'
                ],
                [
                    'nisn' => $nis,
                    'name' => $fullName,
                    'school_id' => $schoolId,
                    'class_name' => $className,
                    'recipient_status' => 'active'
                ]
            );

            $newUserId = $created['user_id'];
            $newStudentId = $created['student_id'];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'code' => 500,
                'message' => 'Terjadi kesalahan saat pendaftaran: ' . $e->getMessage()
            ];
        }

        // 8. Membuat session login langsung setelah pendaftaran berhasil
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_regenerate_id(true);
        }

        $_SESSION['user_id'] = $newUserId;
        $_SESSION['student_id'] = $newStudentId;
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $fullName;
        $_SESSION['role'] = 'siswa';

        // 9. Memberikan respon sukses sesuai kontrak acuan backend/api/register.php
        return [
            'status' => 'success',
            'code' => 200,
            'message' => 'Pendaftaran berhasil.',
            'role' => 'siswa',
            'data' => [
                'id' => $newUserId,
                'student_id' => $newStudentId,
                'username' => $username,
                'full_name' => $fullName,
                'email' => $email,
                'nis' => $nis,
                'school' => $schoolName,
                'class' => $className,
                'role' => 'siswa'
            ]
        ];
    }

    /**
     * Memproses logout pengguna dengan menghapus data session
     *
     * @return array
     */
    public function processLogout() {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        $_SESSION = [];

        if (!headers_sent() && ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }

        return [
            'status' => 'success',
            'code' => 200,
            'message' => 'Logout berhasil.'
        ];
    }

    /**
     * Mendapatkan sesi pengguna yang sedang aktif
     *
     * @return array|null
     */
    public function getCurrentUser() {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        if (empty($_SESSION['user_id'])) {
            return null;
        }

        return [
            'user_id' => (int)$_SESSION['user_id'],
            'student_id' => !empty($_SESSION['student_id']) ? (int)$_SESSION['student_id'] : null,
            'username' => $_SESSION['username'] ?? '',
            'full_name' => $_SESSION['full_name'] ?? '',
            'role' => $_SESSION['role'] ?? ''
        ];
    }

    /**
     * Entrypoint HTTP request untuk proses Login (MVC Action)
     */
    public function login() {
        $this->applyCorsHeaders();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->sendHttpResponse(405, [
                'status' => 'error',
                'message' => 'Metode request tidak diizinkan.'
            ]);
            return;
        }

        $input = $this->parseRequestInput();
        $username = trim($input['username'] ?? $input['email'] ?? $input['nis'] ?? $input['nisn'] ?? $input['identifier'] ?? '');
        $password = (string)($input['password'] ?? '');

        $result = $this->processLogin($username, $password);
        $code = $result['code'] ?? ($result['status'] === 'success' ? 200 : 400);

        $this->sendHttpResponse($code, $result);
    }

    /**
     * Entrypoint HTTP request untuk proses Register (MVC Action)
     */
    public function register() {
        $this->applyCorsHeaders();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->sendHttpResponse(405, [
                'status' => 'error',
                'message' => 'Metode request tidak diizinkan.'
            ]);
            return;
        }

        $input = $this->parseRequestInput();
        $result = $this->processRegister($input);
        $code = $result['code'] ?? ($result['status'] === 'success' ? 200 : 400);

        $this->sendHttpResponse($code, $result);
    }

    /**
     * Entrypoint HTTP request untuk proses Logout (MVC Action)
     */
    public function logout() {
        $this->applyCorsHeaders();
        $result = $this->processLogout();
        $this->sendHttpResponse(200, $result);
    }

    /**
     * Entrypoint HTTP request untuk memeriksa status sesi pengguna yang sedang aktif (MVC Action)
     */
    public function me() {
        $this->applyCorsHeaders();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        if (empty($_SESSION['user_id'])) {
            $this->sendHttpResponse(401, [
                'status' => 'error',
                'success' => false,
                'message' => 'Pengguna belum login.'
            ]);
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $user = $this->userModel->findById($userId);

        if (!$user) {
            $this->sendHttpResponse(404, [
                'status' => 'error',
                'success' => false,
                'message' => 'Data pengguna tidak ditemukan.'
            ]);
            return;
        }

        $rawRole = strtolower(trim($user['role'] ?? ''));
        $role = ($rawRole === 'student') ? 'siswa' : $rawRole;

        $this->sendHttpResponse(200, [
            'status' => 'success',
            'success' => true,
            'data' => [
                'id' => (int)$user['id'],
                'nama' => $user['full_name'] ?? $user['username'],
                'full_name' => $user['full_name'] ?? $user['username'],
                'username' => $user['username'],
                'role' => $role
            ]
        ]);
    }

    /**
     * Mengatur header CORS
     */
    private function applyCorsHeaders() {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    }

    /**
     * Membaca dan menggabungkan data request dari $_POST dan payload JSON
     *
     * @return array
     */
    private function parseRequestInput() {
        $input = $_POST;
        $rawBody = file_get_contents("php://input");
        if (!empty($rawBody)) {
            $jsonData = json_decode($rawBody, true);
            if (is_array($jsonData)) {
                $input = array_merge($input, $jsonData);
            }
        }
        return $input;
    }

    /**
     * Mengirim respon HTTP dalam format JSON atau teks biasa
     *
     * @param int $code HTTP Status Code
     * @param array $payload Data respon
     */
    private function sendHttpResponse($code, $payload) {
        http_response_code($code);

        $isJsonRequest = (
            (isset($_SERVER["HTTP_ACCEPT"]) && str_contains($_SERVER["HTTP_ACCEPT"], "application/json")) ||
            (isset($_GET["format"]) && $_GET["format"] === "json")
        );

        if ($isJsonRequest) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            if ($payload['status'] === 'success') {
                $role = $payload['role'] ?? 'siswa';
                echo ($payload['message'] ?? 'Berhasil.') . " Role: " . htmlspecialchars($role, ENT_QUOTES, "UTF-8");
            } else {
                echo htmlspecialchars($payload['message'] ?? 'Terjadi kesalahan.', ENT_QUOTES, "UTF-8");
            }
        }
        exit;
    }
}

// Dukungan eksekusi langsung jika file Controller diakses langsung via HTTP request
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    $action = $_GET['action'] ?? $_POST['action'] ?? 'login';
    $controller = new AuthController();

    if ($action === 'register') {
        $controller->register();
    } elseif ($action === 'logout') {
        $controller->logout();
    } elseif ($action === 'me' || $action === 'profile') {
        $controller->me();
    } else {
        $controller->login();
    }
}
?>
